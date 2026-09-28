<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";

$pageTitle = "Laporan Stok Sparepart";
$filterMerk = $_GET['merk'] ?? '';

$sql = "SELECT * FROM sparepart";
if ($filterMerk && isValidMerk($filterMerk)) {
    $filterMerkEsc = mysqli_real_escape_string($conn, $filterMerk);
    $sql .= " WHERE merk_kendaraan = '$filterMerkEsc'";
}
$sql .= " ORDER BY merk_kendaraan, nama";
$data = mysqli_query($conn, $sql);

include "../includes/header.php";
?>

<div class="filter-bar">
    <form method="GET">
        <div class="form-group">
            <label>Filter Merk</label>
            <select name="merk" onchange="this.form.submit()">
                <?php renderMerkOptions($filterMerk, true); ?>
            </select>
        </div>
        <button type="button" onclick="window.print()" class="btn btn-primary">Cetak</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Informasi Stok Sparepart<?= $filterMerk ? " - $filterMerk" : '' ?></h3>
    <table>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Merk Kendaraan</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Status</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['kode']) ?></td>
            <td><?= htmlspecialchars($d['nama']) ?></td>
            <td><span class="badge badge-merk"><?= htmlspecialchars($d['merk_kendaraan']) ?></span></td>
            <td><?= formatRupiah($d['harga']) ?></td>
            <td><?= $d['stok'] ?></td>
            <td>
                <?php if ($d['stok'] == 0): ?>
                    <span class="badge badge-low">Habis</span>
                <?php elseif ($d['stok'] <= 5): ?>
                    <span class="badge badge-low">Stok Rendah</span>
                <?php else: ?>
                    Tersedia
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
