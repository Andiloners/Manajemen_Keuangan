<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Riwayat Pembayaran Piutang";
$idPelanggan = isset($_GET['id_pelanggan']) ? (int)$_GET['id_pelanggan'] : 0;
$dariInput = trim($_GET['dari'] ?? '');
$sampaiInput = trim($_GET['sampai'] ?? '');
$error = '';
$riwayat = false;
$pelangganTerpilih = null;
$totalPembayaran = 0;
$totalNominalHutang = 0;
$totalSisaHutang = 0;
$jumlahTransaksi = 0;
$kondisi = [];
$periodeCetak = 'Seluruh riwayat pembayaran';

if (isset($_GET['id_pelanggan'])) {
    if ($idPelanggan <= 0) {
        $error = 'Pelanggan tidak valid.';
    } else {
        $stmtPelanggan = mysqli_prepare($conn, "SELECT nama, telepon FROM pelanggan WHERE id_pelanggan = ?");
        mysqli_stmt_bind_param($stmtPelanggan, 'i', $idPelanggan);
        mysqli_stmt_execute($stmtPelanggan);
        $pelangganTerpilih = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPelanggan));
        if (!$pelangganTerpilih) {
            $error = 'Data pelanggan tidak ditemukan.';
        } else {
            $kondisi[] = "h.id_pelanggan = $idPelanggan";
            $pageTitle = 'Riwayat Pembayaran - ' . $pelangganTerpilih['nama'];
        }
    }
}

$validasiTanggal = function ($tanggal) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        return false;
    }
    return checkdate((int)substr($tanggal, 5, 2), (int)substr($tanggal, 8, 2), (int)substr($tanggal, 0, 4));
};

if ($dariInput !== '' || $sampaiInput !== '') {
    if (!$validasiTanggal($dariInput) || !$validasiTanggal($sampaiInput)) {
        $error = 'Isi tanggal Dari dan Sampai dengan tanggal yang valid. Kosongkan keduanya untuk mencetak seluruh riwayat.';
    } elseif ($dariInput > $sampaiInput) {
        $error = 'Tanggal Dari tidak boleh melewati tanggal Sampai.';
    } else {
        $dari = mysqli_real_escape_string($conn, $dariInput);
        $sampai = mysqli_real_escape_string($conn, $sampaiInput);
        $kondisi[] = "pb.tanggal BETWEEN '$dari' AND '$sampai'";
        $periodeCetak = date('d/m/Y', strtotime($dariInput)) . ' s/d ' . date('d/m/Y', strtotime($sampaiInput));
    }
}
    $filterRiwayat = $kondisi ? 'WHERE ' . implode(' AND ', $kondisi) : '';

if ($error === '') {
    $riwayat = mysqli_query($conn,
        "SELECT pb.id_pembayaran, pb.tanggal, pb.jumlah, pb.keterangan AS catatan_bayar,
                h.id_piutang, h.jenis, h.keterangan AS alasan_hutang, h.jumlah AS nominal_hutang,
                GREATEST(h.jumlah - COALESCE(total_pb.total_bayar, 0), 0) AS sisa_hutang,
                p.nama AS nama_pelanggan, p.telepon,
                u.username,
                pm.id_pemasukan
         FROM pembayaran_piutang pb
         JOIN piutang h ON h.id_piutang = pb.id_piutang
         LEFT JOIN (
             SELECT id_piutang, SUM(jumlah) AS total_bayar
             FROM pembayaran_piutang
             GROUP BY id_piutang
         ) total_pb ON total_pb.id_piutang = h.id_piutang
         JOIN pelanggan p ON p.id_pelanggan = h.id_pelanggan
         LEFT JOIN users u ON u.id = pb.id_user
         LEFT JOIN pemasukan pm ON pm.id_pembayaran_piutang = pb.id_pembayaran
         $filterRiwayat
         ORDER BY pb.tanggal DESC, pb.id_pembayaran DESC"
    );

    if ($riwayat) {
        $total = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) AS jumlah, COALESCE(SUM(pb.jumlah), 0) AS total
             FROM pembayaran_piutang pb
             JOIN piutang h ON h.id_piutang = pb.id_piutang
             $filterRiwayat"
        ));
        $jumlahTransaksi = (int)$total['jumlah'];
        $totalPembayaran = (float)$total['total'];

        $filterHutang = $idPelanggan > 0 ? "WHERE h.id_pelanggan = $idPelanggan" : '';
        $ringkasanHutang = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COALESCE(SUM(h.jumlah), 0) AS nominal,
                    COALESCE(SUM(GREATEST(h.jumlah - COALESCE(pb.total_bayar, 0), 0)), 0) AS sisa
             FROM piutang h
             LEFT JOIN (
                 SELECT id_piutang, SUM(jumlah) AS total_bayar
                 FROM pembayaran_piutang
                 GROUP BY id_piutang
             ) pb ON pb.id_piutang = h.id_piutang
             $filterHutang"
        ));
        $totalNominalHutang = (float)$ringkasanHutang['nominal'];
        $totalSisaHutang = (float)$ringkasanHutang['sisa'];
    } else {
        $error = 'Riwayat pembayaran tidak dapat dimuat. Pastikan migrasi piutang keuangan sudah dijalankan.';
    }
}

include "../includes/header.php";
?>

<style>
@media print {
    @page { size: landscape; margin: 12mm; }
}
</style>

<?php if ($error): ?>
<div class="alert alert-warning no-print"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="actions no-print">
    <a href="../pelanggan/index.php" class="btn btn-warning">Kembali ke Piutang</a>
    <?php if ($idPelanggan > 0): ?>
    <a href="riwayat.php" class="btn btn-secondary">Riwayat Semua Pelanggan</a>
    <?php endif; ?>
    <?php if ($riwayat): ?>
    <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Riwayat</button>
    <?php endif; ?>
</div>

<div class="card no-print">
    <h3 style="margin-bottom:12px;">Pilih Periode Riwayat</h3>
    <form method="GET" class="filter-bar">
        <?php if ($idPelanggan > 0): ?>
        <input type="hidden" name="id_pelanggan" value="<?= $idPelanggan ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>Dari tanggal</label>
            <input type="date" name="dari" value="<?= htmlspecialchars($dariInput) ?>">
        </div>
        <div class="form-group">
            <label>Sampai tanggal</label>
            <input type="date" name="sampai" value="<?= htmlspecialchars($sampaiInput) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan Periode</button>
        <a href="riwayat.php<?= $idPelanggan > 0 ? '?id_pelanggan=' . $idPelanggan : '' ?>" class="btn btn-secondary">Tampilkan Semua</a>
    </form>
    <small><?= $pelangganTerpilih ? 'Riwayat dibatasi untuk pelanggan ini. ' : '' ?>Kosongkan tanggal untuk menampilkan dan mencetak seluruh riwayat<?= $pelangganTerpilih ? ' pelanggan ini' : '' ?>.</small>
</div>

<?php if ($riwayat): ?>
<div class="debt-report-heading">
    <div>
        <span class="debt-report-kicker">LAPORAN PIUTANG BENGKEL</span>
        <h2>Riwayat Pembayaran Hutang</h2>
    </div>
    <div class="debt-report-meta">
        <?php if ($pelangganTerpilih): ?>
        <span><strong>Pelanggan</strong><?= htmlspecialchars($pelangganTerpilih['nama']) ?> · <?= htmlspecialchars($pelangganTerpilih['telepon'] ?: 'Telepon tidak tersedia') ?></span>
        <?php else: ?>
        <span><strong>Cakupan</strong>Semua pelanggan</span>
        <?php endif; ?>
        <span><strong>Periode pembayaran</strong><?= htmlspecialchars($periodeCetak) ?></span>
    </div>
</div>

<div class="debt-summary-grid">
    <article class="debt-summary-card debt-summary-total">
        <span class="debt-summary-icon"><i class="bi bi-receipt"></i></span>
        <div>
            <span class="debt-summary-label">Total Nilai Hutang</span>
            <strong class="debt-summary-value"><?= formatRupiah($totalNominalHutang) ?></strong>
            <small>Akumulasi seluruh tagihan</small>
        </div>
    </article>
    <article class="debt-summary-card debt-summary-income">
        <span class="debt-summary-icon"><i class="bi bi-cash-stack"></i></span>
        <div>
            <span class="debt-summary-label">Uang Masuk</span>
            <strong class="debt-summary-value"><?= formatRupiah($totalPembayaran) ?></strong>
            <small><?= number_format($jumlahTransaksi, 0, ',', '.') ?> pembayaran pada periode ini</small>
        </div>
    </article>
    <article class="debt-summary-card debt-summary-balance">
        <span class="debt-summary-icon"><i class="bi bi-hourglass-split"></i></span>
        <div>
            <span class="debt-summary-label">Sisa Hutang Sekarang</span>
            <strong class="debt-summary-value"><?= formatRupiah($totalSisaHutang) ?></strong>
            <small>Belum tertagih saat ini</small>
        </div>
    </article>
</div>

<div class="debt-report-note">
    <i class="bi bi-info-circle-fill"></i>
    <span><strong>Cara membaca:</strong> Uang masuk mengikuti periode yang dipilih. Nilai hutang dan sisanya mencakup semua tagihan dalam laporan. Setiap baris adalah satu pembayaran.</span>
</div>

<div class="card debt-report-table-card">
    <div class="debt-table-heading">
        <div>
            <h3>Rincian Pembayaran</h3>
            <p>Nilai tagihan, pembayaran yang diterima, dan sisa terkini.</p>
        </div>
        <span class="debt-count-badge"><?= number_format($jumlahTransaksi, 0, ',', '.') ?> transaksi</span>
    </div>
    <div class="table-responsive">
        <table class="debt-report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Bayar</th>
                    <?php if (!$pelangganTerpilih): ?><th>Pelanggan</th><?php endif; ?>
                    <th>Hutang Karena</th>
                    <th class="money-heading">Total Tagihan</th>
                    <th class="money-heading">Uang Masuk</th>
                    <th class="money-heading">Sisa Sekarang</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($riwayat) === 0): ?>
                <tr><td colspan="<?= $pelangganTerpilih ? 6 : 7 ?>" class="debt-empty-state">Belum ada pembayaran pada periode yang dipilih.</td></tr>
                <?php else: ?>
                    <?php $no = 1; while ($pembayaran = mysqli_fetch_assoc($riwayat)): ?>
                    <?php
                    $labelJenis = [
                        'servis' => 'Servis',
                        'sparepart' => 'Pengambilan sparepart',
                        'lainnya' => 'Lainnya'
                    ][$pembayaran['jenis']] ?? 'Lainnya';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($pembayaran['tanggal']))) ?></td>
                        <?php if (!$pelangganTerpilih): ?><td>
                            <strong><?= htmlspecialchars($pembayaran['nama_pelanggan']) ?></strong><br>
                            <small><?= htmlspecialchars($pembayaran['telepon'] ?: '-') ?></small>
                        </td><?php endif; ?>
                        <td><strong><?= htmlspecialchars($labelJenis) ?></strong><br><?= nl2br(htmlspecialchars($pembayaran['alasan_hutang'])) ?></td>
                        <td class="money-cell"><?= formatRupiah($pembayaran['nominal_hutang']) ?></td>
                        <td class="money-cell money-income"><?= formatRupiah($pembayaran['jumlah']) ?></td>
                        <td class="money-cell money-balance"><?= formatRupiah($pembayaran['sisa_hutang']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
            <?php if ($jumlahTransaksi > 0): ?>
            <tfoot>
                <tr>
                    <th colspan="<?= $pelangganTerpilih ? 4 : 5 ?>">Total uang masuk pada periode ini</th>
                    <th class="money-cell money-income"><?= formatRupiah($totalPembayaran) ?></th>
                    <th></th>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>
