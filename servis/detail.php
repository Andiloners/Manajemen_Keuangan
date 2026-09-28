<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Detail Servis";
$id = (int)$_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT s.*, k.no_polisi, k.merk, k.jenis AS jenis_kendaraan, p.nama AS nama_pelanggan, p.telepon, u.username
     FROM servis s
     JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
     JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON s.id_user = u.id
     WHERE s.id_servis = $id"
));

include "../includes/header.php";
?>

<div class="card">
    <table>
        <tr><th width="200">Tanggal</th><td><?= date('d/m/Y', strtotime($data['tanggal'])) ?></td></tr>
        <tr><th>Pelanggan</th><td><?= htmlspecialchars($data['nama_pelanggan']) ?> (<?= htmlspecialchars($data['telepon']) ?>)</td></tr>
        <tr><th>Kendaraan</th><td><?= htmlspecialchars($data['no_polisi']) ?> - <?= htmlspecialchars($data['merk']) ?> (<?= htmlspecialchars($data['jenis_kendaraan']) ?>)</td></tr>
        <tr><th>Jenis Servis</th><td><?= htmlspecialchars($data['jenis_servis']) ?></td></tr>
        <tr><th>Biaya Jasa</th><td><?= formatRupiah($data['biaya_jasa']) ?></td></tr>
        <tr><th>Keterangan</th><td><?= htmlspecialchars($data['keterangan'] ?: '-') ?></td></tr>
        <tr><th>Nama Pengguna</th><td><?= htmlspecialchars($data['username'] ?? '-') ?></td></tr>
    </table>
</div>

<a href="index.php" class="btn btn-warning">Kembali</a>

<?php include "../includes/footer.php"; ?>
