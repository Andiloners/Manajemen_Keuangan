<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Laporan Informasi Kendaraan";
$data = mysqli_query($conn,
    "SELECT k.*, p.nama AS nama_pelanggan, p.telepon
     FROM kendaraan k JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     ORDER BY k.no_polisi"
);

include "../includes/header.php";
?>

<div class="actions">
    <button onclick="window.print()" class="btn btn-primary">Cetak</button>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Daftar Seluruh Kendaraan</h3>
    <table>
        <tr>
            <th>No</th>
            <th>No. Polisi</th>
            <th>Merk</th>
            <th>Jenis</th>
            <th>Pemilik</th>
            <th>Telepon</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['no_polisi']) ?></td>
            <td><?= htmlspecialchars($d['merk']) ?></td>
            <td><?= htmlspecialchars($d['jenis']) ?></td>
            <td><?= htmlspecialchars($d['nama_pelanggan']) ?></td>
            <td><?= htmlspecialchars($d['telepon']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
