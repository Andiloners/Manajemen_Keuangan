<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Data Piutang Pelanggan";
$tampilkanSemua = isset($_GET['status']) && $_GET['status'] === 'semua';
$filterTerbuka = $tampilkanSemua ? "" : "HAVING (h.jumlah - COALESCE(SUM(pb.jumlah), 0)) > 0";
try {
    $data = mysqli_query($conn,
        "SELECT h.id_piutang, h.tanggal, h.jenis, h.keterangan, h.jumlah,
                p.id_pelanggan, p.nama, p.telepon,
                COALESCE(SUM(pb.jumlah), 0) AS terbayar
         FROM piutang h
         JOIN pelanggan p ON p.id_pelanggan = h.id_pelanggan
         LEFT JOIN pembayaran_piutang pb ON pb.id_piutang = h.id_piutang
         GROUP BY h.id_piutang, h.tanggal, h.jenis, h.keterangan, h.jumlah,
                  p.id_pelanggan, p.nama, p.telepon
         $filterTerbuka
         ORDER BY h.tanggal DESC, h.id_piutang DESC"
    );
    $ringkasan = mysqli_query($conn,
        "SELECT COUNT(DISTINCT h.id_pelanggan) AS jumlah_orang,
                COALESCE(SUM(h.jumlah - COALESCE(pb.total_bayar, 0)), 0) AS total_piutang
         FROM piutang h
         LEFT JOIN (
             SELECT id_piutang, SUM(jumlah) AS total_bayar
             FROM pembayaran_piutang
             GROUP BY id_piutang
         ) pb ON pb.id_piutang = h.id_piutang
         WHERE h.jumlah > COALESCE(pb.total_bayar, 0)"
    );
} catch (mysqli_sql_exception $e) {
    $data = false;
    $ringkasan = false;
}

include "../includes/header.php";
?>

<div class="actions">
    <a href="../piutang/tambah.php" class="btn btn-primary">+ Catat Hutang</a>
    <a href="../piutang/riwayat.php" class="btn btn-success">Riwayat Pembayaran / Cetak</a>
    <a href="master.php" class="btn btn-warning">Kelola Pelanggan</a>
    <a href="<?= $tampilkanSemua ? 'index.php' : 'index.php?status=semua' ?>" class="btn btn-secondary">
        <?= $tampilkanSemua ? 'Tampilkan Hutang Aktif' : 'Riwayat Semua Hutang' ?>
    </a>
</div>

<?php if (!$data || !$ringkasan): ?>
    <div class="alert alert-warning">
        Tabel pencatatan hutang/keuangan belum tersedia. Jalankan <strong>database/migration_piutang.sql</strong>, lalu <strong>database/migration_piutang_keuangan.sql</strong> pada database bengkel_db.
    </div>
<?php else: ?>
    <?php $summary = mysqli_fetch_assoc($ringkasan); ?>
    <div class="card" style="margin-bottom: 20px;">
        <strong><?= number_format((int)$summary['jumlah_orang']) ?> pelanggan masih berhutang</strong>
        <div style="font-size: 1.5rem; font-weight: 800; color: #dc2626; margin-top: 4px;">
            <?= formatRupiah($summary['total_piutang']) ?>
        </div>
        <small>Jumlah sisa hutang yang belum dibayar.</small>
    </div>

    <div class="alert alert-info">
        Catatan servis atau penjualan lama tidak otomatis dianggap hutang. Masukkan nilai yang benar-benar belum dibayar melalui tombol “Catat Hutang”.
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pelanggan</th>
                        <th>Hutang karena</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Terbayar</th>
                        <th>Sisa Hutang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($data) === 0): ?>
                    <tr><td colspan="8" style="text-align: center;">Tidak ada data hutang<?= $tampilkanSemua ? '.' : ' yang masih terbuka.' ?></td></tr>
                    <?php else: ?>
                        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
                        <?php $sisa = max(0, (float)$d['jumlah'] - (float)$d['terbayar']); ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($d['nama']) ?></strong><br>
                                <small><?= htmlspecialchars($d['telepon'] ?: '-') ?></small>
                            </td>
                            <td>
                                <?php
                                $labelJenis = [
                                    'servis' => 'Servis',
                                    'sparepart' => 'Pengambilan sparepart',
                                    'lainnya' => 'Lainnya'
                                ][$d['jenis']] ?? 'Lainnya';
                                ?>
                                <strong><?= htmlspecialchars($labelJenis) ?></strong><br>
                                <?= nl2br(htmlspecialchars($d['keterangan'])) ?>
                            </td>
                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($d['tanggal']))) ?></td>
                            <td><?= formatRupiah($d['jumlah']) ?></td>
                            <td><?= formatRupiah($d['terbayar']) ?></td>
                            <td><strong style="color: <?= $sisa > 0 ? '#dc2626' : '#059669' ?>;"><?= formatRupiah($sisa) ?></strong></td>
                            <td>
                                <?php if ($sisa > 0): ?>
                                <a href="../piutang/bayar.php?id=<?= (int)$d['id_piutang'] ?>" class="btn btn-success btn-sm">Bayar / Riwayat</a>
                                <?php else: ?>
                                <a href="../piutang/bayar.php?id=<?= (int)$d['id_piutang'] ?>" class="btn btn-primary btn-sm">Lunas / Riwayat</a>
                                <?php endif; ?>
                                <a href="../piutang/riwayat.php?id_pelanggan=<?= (int)$d['id_pelanggan'] ?>" class="btn btn-secondary btn-sm">Cetak Pelanggan</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>
