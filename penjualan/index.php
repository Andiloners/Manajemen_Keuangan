<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Penjualan Sparepart";
$data = mysqli_query($conn,
    "SELECT pj.*, p.nama AS nama_pelanggan, u.username
     FROM penjualan pj
     LEFT JOIN pelanggan p ON pj.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON pj.id_user = u.id
     ORDER BY pj.tanggal DESC, pj.id_penjualan DESC"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Transaksi Penjualan Baru</a>
</div>

<?php if (isset($_GET['piutang_terkait'])): ?>
<div class="alert alert-warning">Penjualan tidak dihapus karena memiliki riwayat piutang pelanggan.</div>
<?php elseif (isset($_GET['gagal_hapus'])): ?>
<div class="alert alert-warning">Penjualan tidak dapat dihapus. Stok dan riwayat keuangan tidak berubah.</div>
<?php endif; ?>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Nama Pengguna</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= htmlspecialchars($d['nama_pelanggan'] ?? 'Umum') ?></td>
            <td><?= formatRupiah($d['total']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td>
                <a href="detail.php?id=<?= $d['id_penjualan'] ?>" class="btn btn-primary btn-sm">Detail</a>
                <a href="hapus.php?id=<?= $d['id_penjualan'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus transaksi ini? Stok akan dikembalikan.')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
