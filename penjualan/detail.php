<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/fifo_helper.php";

$pageTitle = "Detail Penjualan";
$id = (int)$_GET['id'];

$header = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT pj.*, p.nama AS nama_pelanggan, u.username
     FROM penjualan pj
     LEFT JOIN pelanggan p ON pj.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON pj.id_user = u.id
     WHERE pj.id_penjualan = $id"
));

$detail = mysqli_query($conn,
    "SELECT d.*, s.kode, s.nama, s.merk_kendaraan
     FROM penjualan_detail d
     JOIN sparepart s ON d.id_sparepart = s.id_sparepart
     WHERE d.id_penjualan = $id"
);

$totalHppTransaksi = getHppPenjualan($conn, $id);
$labaKotor = $header['total'] - $totalHppTransaksi;

include "../includes/header.php";
?>

<div class="card">
    <table>
        <tr><th width="200">Tanggal</th><td><?= date('d/m/Y', strtotime($header['tanggal'])) ?></td></tr>
        <tr><th>Pelanggan</th><td><?= htmlspecialchars($header['nama_pelanggan'] ?? 'Umum') ?></td></tr>
        <tr><th>Total Penjualan (Omset)</th><td><strong><?= formatRupiah($header['total']) ?></strong></td></tr>
        <tr><th>Total HPP (Modal FIFO)</th><td><strong style="color:var(--danger);"><?= formatRupiah($totalHppTransaksi) ?></strong></td></tr>
        <tr><th>Laba Kotor</th><td><strong style="color:var(--success);"><?= formatRupiah($labaKotor) ?></strong></td></tr>
        <tr><th>Input Oleh</th><td><?= htmlspecialchars($header['username'] ?? '-') ?></td></tr>
    </table>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;">Detail Item & Konsumsi Batch FIFO</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Sparepart</th>
                <th>Merk</th>
                <th>Qty</th>
                <th>Harga Jual</th>
                <th>Subtotal Omset</th>
                <th>Rincian Batch FIFO (HPP)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($d = mysqli_fetch_assoc($detail)): 
                $id_detail = (int)$d['id_detail'];
                $fifoLogs = mysqli_query($conn, 
                    "SELECT fd.*, b.tanggal_masuk, b.id_batch 
                     FROM penjualan_fifo_detail fd 
                     JOIN sparepart_stok_fifo b ON fd.id_batch = b.id_batch 
                     WHERE fd.id_detail = $id_detail 
                     ORDER BY fd.id_fifo_detail ASC"
                );
            ?>
            <tr>
                <td><?= htmlspecialchars($d['kode']) ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><span class="badge badge-merk"><?= htmlspecialchars($d['merk_kendaraan']) ?></span></td>
                <td><?= $d['qty'] ?></td>
                <td><?= formatRupiah($d['harga']) ?></td>
                <td><?= formatRupiah($d['subtotal']) ?></td>
                <td>
                    <ul style="margin:0;padding-left:16px;font-size:0.88rem;">
                        <?php while ($f = mysqli_fetch_assoc($fifoLogs)): ?>
                        <li>
                            Batch #<?= $f['id_batch'] ?> (Tgl Masuk: <?= date('d/m/Y', strtotime($f['tanggal_masuk'])) ?>): 
                            <strong><?= $f['qty'] ?> pcs</strong> @ <?= formatRupiah($f['harga_beli']) ?> 
                            (HPP: <?= formatRupiah($f['subtotal_hpp']) ?>)
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<a href="index.php" class="btn btn-warning">Kembali</a>

<?php include "../includes/footer.php"; ?>
