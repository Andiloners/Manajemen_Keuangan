<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Data Pengeluaran";
$data = mysqli_query($conn, 
    "SELECT p.*, u.username FROM pengeluaran p 
     LEFT JOIN users u ON p.id_user = u.id 
     ORDER BY p.tanggal DESC, p.id_pengeluaran DESC"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Tambah Pengeluaran</a>
</div>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th>Jumlah</th>
            <th>Upload oleh</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $d['jenis'])) ?></td>
            <td><?= htmlspecialchars($d['keterangan']) ?></td>
            <td><?= formatRupiah($d['jumlah']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td>
                <a href="edit.php?id=<?= $d['id_pengeluaran'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $d['id_pengeluaran'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
