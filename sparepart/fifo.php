<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";
include "../includes/fifo_helper.php";

$pageTitle = "Kartu Stok FIFO & Lot Batch Sparepart";

$id_sparepart_filter = isset($_GET['id_sparepart']) ? (int)$_GET['id_sparepart'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'aktif';

$whereClause = "WHERE 1=1";
if ($id_sparepart_filter > 0) {
    $whereClause .= " AND b.id_sparepart = $id_sparepart_filter";
}

if ($filter_status === 'aktif') {
    $whereClause .= " AND b.stok_sisa > 0";
} elseif ($filter_status === 'habis') {
    $whereClause .= " AND b.stok_sisa = 0";
}

$queryBatches = "SELECT b.*, s.kode, s.nama AS nama_sparepart, s.merk_kendaraan, s.harga AS harga_jual, u.username
                 FROM sparepart_stok_fifo b
                 JOIN sparepart s ON b.id_sparepart = s.id_sparepart
                 LEFT JOIN users u ON b.id_user = u.id
                 $whereClause
                 ORDER BY b.tanggal_masuk DESC, b.id_batch DESC";

$resultBatches = mysqli_query($conn, $queryBatches);

// List sparepart untuk dropdown filter
$listSparepart = mysqli_query($conn, "SELECT id_sparepart, kode, nama, merk_kendaraan FROM sparepart ORDER BY merk_kendaraan, nama");

// Total Statistik Aset FIFO
$statAset = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(stok_sisa * harga_beli),0) AS total_aset_modal,
            COALESCE(SUM(stok_sisa),0) AS total_unit_sisa,
            COUNT(CASE WHEN stok_sisa > 0 THEN 1 END) AS total_batch_aktif
     FROM sparepart_stok_fifo"
));

include "../includes/header.php";
?>

<div class="card-grid" style="margin-bottom: 20px;">
    <div class="stat-card-img blue">
        <img src="../assets/img/icons/sparepart.svg" alt="Batch" class="stat-icon">
        <div>
            <h3>Batch Stok Aktif</h3>
            <div class="value"><?= number_format($statAset['total_batch_aktif']) ?> Batch</div>
        </div>
    </div>
    <div class="stat-card-img green">
        <img src="../assets/img/icons/saldo.svg" alt="Aset" class="stat-icon">
        <div>
            ### Total Nilai Aset Gudang (Modal HPP)
            <div class="value"><?= formatRupiah($statAset['total_aset_modal']) ?></div>
        </div>
    </div>
    <div class="stat-card-img orange">
        <img src="../assets/img/icons/kendaraan.svg" alt="Unit" class="stat-icon">
        <div>
            ### Total Unit Stok Fisik FIFO
            <div class="value"><?= number_format($statAset['total_unit_sisa']) ?> Pcs</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
            <label>Filter Sparepart</label>
            <select name="id_sparepart" style="width:100%;">
                <option value="0">-- Semua Sparepart --</option>
                <?php while ($sp = mysqli_fetch_assoc($listSparepart)): ?>
                <option value="<?= $sp['id_sparepart'] ?>" <?= $id_sparepart_filter == $sp['id_sparepart'] ? 'selected' : '' ?>>
                    [<?= htmlspecialchars($sp['merk_kendaraan']) ?>] <?= htmlspecialchars($sp['kode']) ?> - <?= htmlspecialchars($sp['nama']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;width:160px;">
            <label>Status Stok Batch</label>
            <select name="status" style="width:100%;">
                <option value="aktif" <?= $filter_status === 'aktif' ? 'selected' : '' ?>>Aktif (Tersedia)</option>
                <option value="habis" <?= $filter_status === 'habis' ? 'selected' : '' ?>>Habis (0 Pcs)</option>
                <option value="semua" <?= $filter_status === 'semua' ? 'selected' : '' ?>>Semua Batch</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter Data</button>
        <a href="fifo.php" class="btn btn-warning">Reset</a>
    </form>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3>Daftar Lot / Batch Stok FIFO</h3>
        <a href="index.php" class="btn btn-primary btn-sm">Master Sparepart</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Batch</th>
                <th>Tgl Masuk</th>
                <th>Sparepart</th>
                <th>Merk</th>
                <th>Harga Beli (HPP)</th>
                <th>Harga Jual</th>
                <th>Stok Masuk</th>
                <th>Stok Sisa</th>
                <th>Nilai Modal Sisa</th>
                <th>Status</th>
                <th>Input Oleh</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($resultBatches) > 0): ?>
                <?php while ($b = mysqli_fetch_assoc($resultBatches)): 
                    $nilaiSisa = $b['stok_sisa'] * $b['harga_beli'];
                ?>
                <tr>
                    <td><strong>#Batch-<?= $b['id_batch'] ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($b['tanggal_masuk'])) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($b['nama_sparepart']) ?></strong><br>
                        <small style="color:#666;"><?= htmlspecialchars($b['kode']) ?></small>
                    </td>
                    <td><span class="badge badge-merk"><?= htmlspecialchars($b['merk_kendaraan']) ?></span></td>
                    <td><strong><?= formatRupiah($b['harga_beli']) ?></strong></td>
                    <td><?= formatRupiah($b['harga_jual']) ?></td>
                    <td><?= number_format($b['stok_masuk']) ?> pcs</td>
                    <td>
                        <strong style="font-size:1.05rem;color:<?= $b['stok_sisa'] > 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                            <?= number_format($b['stok_sisa']) ?> pcs
                        </strong>
                    </td>
                    <td><?= formatRupiah($nilaiSisa) ?></td>
                    <td>
                        <?php if ($b['stok_sisa'] > 0): ?>
                        <span class="badge badge-admin">Tersedia</span>
                        <?php else: ?>
                        <span class="badge" style="background:#e0e0e0;color:#666;">Habis</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($b['username'] ?? '-') ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" style="text-align:center;padding:24px;color:#888;">
                        Tidak ada data batch stok FIFO yang sesuai dengan filter.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
