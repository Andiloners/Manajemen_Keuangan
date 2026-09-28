<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Master Pelanggan";
$data = mysqli_query($conn,
    "SELECT p.*, u.username FROM pelanggan p
     LEFT JOIN users u ON p.id_user = u.id
     ORDER BY p.nama"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Tambah Pelanggan</a>
    <a href="index.php" class="btn btn-warning">Kembali ke Data Piutang</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Nama Pengguna</th>
                <th>Aksi</th>
            </tr>
            <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['telepon'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['alamat'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
                <td>
                    <a href="edit.php?id=<?= $d['id_pelanggan'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="hapus.php?id=<?= $d['id_pelanggan'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
