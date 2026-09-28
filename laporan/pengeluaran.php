<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Laporan Pengeluaran";

$dari = getSafeDate($conn, $_GET['dari'] ?? null, date('Y-m-01'));
$sampai = getSafeDate($conn, $_GET['sampai'] ?? null, date('Y-m-d'));

$data = mysqli_query($conn,
    "SELECT p.*, u.username FROM pengeluaran p 
     LEFT JOIN users u ON p.id_user = u.id
     WHERE p.tanggal BETWEEN '$dari' AND '$sampai' ORDER BY p.tanggal DESC"
);

$total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS jml FROM pengeluaran WHERE tanggal BETWEEN '$dari' AND '$sampai'"
))['jml'];

include "../includes/header.php";
?>

<div class="filter-bar">
    <form method="GET">
        <div class="form-group">
            <label>Dari</label>
            <input type="date" name="dari" value="<?= $dari ?>">
        </div>
        <div class="form-group">
            <label>Sampai</label>
            <input type="date" name="sampai" value="<?= $sampai ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <button type="button" onclick="window.print()" class="btn btn-warning">Cetak</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-bottom:16px;">Laporan Pengeluaran</h3>
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th>Jumlah</th>
            <th>Oleh</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $d['jenis'])) ?></td>
            <td><?= htmlspecialchars($d['keterangan']) ?></td>
            <td><?= formatRupiah($d['jumlah']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
        </tr>
        <?php endwhile; ?>
        <tr style="font-weight:bold;background:#ffebee;">
            <td colspan="4" style="text-align:right;">Total Pengeluaran</td>
            <td><?= formatRupiah($total) ?></td>
            <td></td>
        </tr>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
