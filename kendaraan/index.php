<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Data Kendaraan";
$data = mysqli_query($conn,
    "SELECT k.*, p.nama AS nama_pelanggan, u.username
     FROM kendaraan k
     JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON k.id_user = u.id
     ORDER BY k.no_polisi"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Tambah Kendaraan</a>
</div>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>No. Polisi</th>
            <th>Merk</th>
            <th>Jenis</th>
            <th>Pemilik</th>
            <th>Nama Pengguna</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['no_polisi']) ?></td>
            <td><?= htmlspecialchars($d['merk']) ?></td>
            <td><?= htmlspecialchars($d['jenis']) ?></td>
            <td><?= htmlspecialchars($d['nama_pelanggan']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td>
                <a href="edit.php?id=<?= $d['id_kendaraan'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $d['id_kendaraan'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
