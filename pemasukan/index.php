<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Data Pemasukan";
$data = mysqli_query($conn, 
    "SELECT p.*, u.username FROM pemasukan p 
     LEFT JOIN users u ON p.id_user = u.id 
     ORDER BY p.tanggal DESC, p.id_pemasukan DESC"
);

include "../includes/header.php";
?>

<div class="actions">
    <a href="tambah.php" class="btn btn-primary">+ Tambah Pemasukan Manual</a>
    <a href="../laporan/keuangan.php" class="btn btn-warning">Lihat Arus Kas</a>
</div>

<div class="alert alert-info">
    Penerimaan pembayaran hutang dicatat otomatis dari menu piutang dan ditandai sebagai <strong>Pembayaran Piutang</strong>. Jangan masukkan pembayaran yang sama sebagai pemasukan manual.
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
                <?php if ($d['jenis'] === 'lainnya'): ?>
                <a href="hapus.php?id=<?= $d['id_pemasukan'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</a>
                <?php elseif ($d['jenis'] === 'pembayaran_piutang'): ?>
                <a href="../pelanggan/index.php?status=semua" class="btn btn-primary btn-sm">Lihat Piutang</a>
                <?php else: ?>
                <span style="color:#999;font-size:0.8rem;">Otomatis</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
