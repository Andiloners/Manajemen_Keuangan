<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";

$pageTitle = "Data Sparepart";
$filterMerk = $_GET['merk'] ?? '';

$sql = "SELECT s.*, u.username FROM sparepart s LEFT JOIN users u ON s.id_user = u.id";
if ($filterMerk && isValidMerk($filterMerk)) {
    $filterMerkEsc = mysqli_real_escape_string($conn, $filterMerk);
    $sql .= " WHERE s.merk_kendaraan = '$filterMerkEsc'";
}
$sql .= " ORDER BY s.merk_kendaraan, s.nama";
$data = mysqli_query($conn, $sql);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Tambah Sparepart</a>
</div>

<div class="filter-bar">
    <form method="GET">
        <div class="form-group">
            <label>Filter Merk Kendaraan</label>
            <select name="merk" onchange="this.form.submit()">
                <?php renderMerkOptions($filterMerk, true); ?>
            </select>
        </div>
        <?php if ($filterMerk): ?>
        <a href="index.php" class="btn btn-warning">Reset Filter</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Merk Kendaraan</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Nama Pengguna</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['kode']) ?></td>
            <td><?= htmlspecialchars($d['nama']) ?></td>
            <td><span class="badge badge-merk"><?= htmlspecialchars($d['merk_kendaraan']) ?></span></td>
            <td><?= formatRupiah($d['harga']) ?></td>
            <td>
                <?= $d['stok'] ?>
                <?php if ($d['stok'] <= 5): ?>
                    <span class="badge badge-low">Stok Rendah</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td>
                <a href="edit.php?id=<?= $d['id_sparepart'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $d['id_sparepart'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
