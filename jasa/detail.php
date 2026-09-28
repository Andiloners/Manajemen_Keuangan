<?php
include "../config/database.php";
include "../includes/auth.php"; // pastikan login

// Pastikan hanya admin atau kasir yang dapat mengakses
if (!in_array($_SESSION['level'], ['admin', 'kasir'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = "Detail Pendapatan Mekanik";

$id_user = (int)($_GET['id'] ?? 0);
if ($id_user <= 0) {
    // ID tidak valid, kembali ke indeks
    header('Location: index.php');
    exit;
}

// Nama mekanik
$mekanikRes = mysqli_query($conn, "SELECT username FROM users WHERE id = $id_user");
$mekanikRow = mysqli_fetch_assoc($mekanikRes);
$mekanikName = $mekanikRow ? $mekanikRow['username'] : 'Tidak Diketahui';

// Ambil semua servis yang dilakukan mekanik tersebut
$sql = "
    SELECT s.id_servis, s.tanggal, k.no_polisi, p.nama AS pelanggan, s.jenis_servis, s.biaya_jasa, s.keterangan
    FROM servis s
    JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
    JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
    WHERE s.id_user = $id_user
    ORDER BY s.tanggal DESC
";
$detailRes = mysqli_query($conn, $sql);

// Hitung total
$total = 0.0;
while ($row = mysqli_fetch_assoc($detailRes)) {
    $total += $row['biaya_jasa'];
}
// Reset pointer untuk iterasi ulang
mysqli_data_seek($detailRes, 0);
?>
<?php include "../includes/header.php"; ?>
<div class="card">
    <h2>Detail Pendapatan Mekanik: <?= htmlspecialchars($mekanikName) ?></h2>
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Polisi</th>
            <th>Pelanggan</th>
            <th>Jenis Servis</th>
            <th>Biaya Jasa (Rp)</th>
            <th>Keterangan</th>
        </tr>
        <?php $no = 1; while($row = mysqli_fetch_assoc($detailRes)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['no_polisi']) ?></td>
            <td><?= htmlspecialchars($row['pelanggan']) ?></td>
            <td><?= htmlspecialchars($row['jenis_servis']) ?></td>
            <td><?= number_format($row['biaya_jasa'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="5" style="text-align:right;font-weight:bold;">Total Pendapatan</td>
            <td colspan="2" style="font-weight:bold;">Rp <?= number_format($total, 0, ',', '.') ?></td>
        </tr>
    </table>
    <a href="index.php" class="btn btn-warning" style="margin-top:12px;">Kembali</a>
</div>
<?php include "../includes/footer.php"; ?>
