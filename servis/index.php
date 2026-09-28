<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Transaksi Servis";
$data = mysqli_query($conn,
    "SELECT s.*, k.no_polisi, k.merk, p.nama AS nama_pelanggan, u.username
     FROM servis s
     JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
     JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON s.id_user = u.id
     ORDER BY s.tanggal DESC, s.id_servis DESC"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Input Servis Baru</a>
</div>

<?php if (isset($_GET['piutang_terkait'])): ?>
<div class="alert alert-warning">Servis tidak dihapus karena masih terhubung dengan riwayat piutang. Lunasi atau periksa piutang tersebut terlebih dahulu.</div>
<?php elseif (isset($_GET['gagal_hapus'])): ?>
<div class="alert alert-warning">Servis tidak dapat dihapus. Tidak ada riwayat keuangan yang diubah.</div>
<?php endif; ?>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Polisi</th>
            <th>Pelanggan</th>
            <th>Jenis Servis</th>
            <th>Biaya Jasa</th>
            <th>Nama Pengguna</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= htmlspecialchars($d['no_polisi']) ?> (<?= htmlspecialchars($d['merk']) ?>)</td>
            <td><?= htmlspecialchars($d['nama_pelanggan']) ?></td>
            <td><?= htmlspecialchars($d['jenis_servis']) ?></td>
            <td><?= formatRupiah($d['biaya_jasa']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td>
                <a href="detail.php?id=<?= $d['id_servis'] ?>" class="btn btn-primary btn-sm">Detail</a>
                <a href="hapus.php?id=<?= $d['id_servis'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
