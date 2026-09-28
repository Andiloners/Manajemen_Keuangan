<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Laporan Informasi Pelanggan";
$data = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");

include "../includes/header.php";
?>

<div class="actions">
    <button onclick="window.print()" class="btn btn-primary">Cetak</button>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Daftar Seluruh Pelanggan</h3>
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Telepon</th>
            <th>Alamat</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['nama']) ?></td>
            <td><?= htmlspecialchars($d['telepon']) ?></td>
            <td><?= htmlspecialchars($d['alamat']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
