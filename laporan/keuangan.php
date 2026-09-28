<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Laporan Keuangan";

$dari = getSafeDate($conn, $_GET['dari'] ?? null, date('Y-m-01'));
$sampai = getSafeDate($conn, $_GET['sampai'] ?? null, date('Y-m-d'));
$tanggalPilihan = [];
foreach ((array)($_GET['tanggal'] ?? []) as $tanggal) {
    if (!is_string($tanggal)) {
        continue;
    }
    $tanggalValid = DateTime::createFromFormat('Y-m-d', $tanggal);
    if ($tanggalValid && $tanggalValid->format('Y-m-d') === $tanggal) {
        $tanggalPilihan[] = $tanggal;
    }
}
$tanggalPilihan = array_values(array_unique($tanggalPilihan));
$tanggalSql = implode(',', array_map(function ($tanggal) use ($conn) {
    return "'" . mysqli_real_escape_string($conn, $tanggal) . "'";
}, $tanggalPilihan));
$filterPemasukan = $tanggalPilihan ? "tanggal IN ($tanggalSql)" : "tanggal BETWEEN '$dari' AND '$sampai'";
$filterPengeluaran = $tanggalPilihan ? "tanggal IN ($tanggalSql)" : "tanggal BETWEEN '$dari' AND '$sampai'";
$filterPenjualan = $tanggalPilihan ? "pj.tanggal IN ($tanggalSql)" : "pj.tanggal BETWEEN '$dari' AND '$sampai'";
$labelPeriode = $tanggalPilihan
    ? implode(', ', array_map(function ($tanggal) { return date('d/m/Y', strtotime($tanggal)); }, $tanggalPilihan))
    : date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai));

if (isset($_POST['simpan_arus_kas'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $tipe = $_POST['tipe'] === 'pengeluaran' ? 'pengeluaran' : 'pemasukan';
    $keterangan = mysqli_real_escape_string($conn, trim($_POST['keterangan']));
    $jumlah = (float)$_POST['jumlah'];
    $id_user = (int)$_SESSION['id'];

    if ($keterangan !== '' && $jumlah > 0) {
        if ($tipe === 'pemasukan') {
            mysqli_query($conn,
                "INSERT INTO pemasukan (tanggal, id_user, jenis, keterangan, jumlah)
                 VALUES ('$tanggal', $id_user, 'lainnya', '$keterangan', $jumlah)"
            );
        } else {
            $jenis = mysqli_real_escape_string($conn, $_POST['jenis_pengeluaran']);
            mysqli_query($conn,
                "INSERT INTO pengeluaran (tanggal, id_user, jenis, keterangan, jumlah)
                 VALUES ('$tanggal', $id_user, '$jenis', '$keterangan', $jumlah)"
            );
        }
        header("Location: keuangan.php?dari=$dari&sampai=$sampai&tersimpan=1");
        exit;
    }
    $errorArusKas = 'Keterangan dan jumlah wajib diisi dengan benar.';
}

$pemasukan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pemasukan WHERE $filterPemasukan"
))['total'];

$pengeluaran = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pengeluaran WHERE $filterPengeluaran"
))['total'];

$saldo = $pemasukan - $pengeluaran;

$pemasukanServis = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pemasukan
    WHERE jenis='servis' AND $filterPemasukan"
))['total'];

$pemasukanSparepart = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pemasukan
    WHERE jenis='penjualan_sparepart' AND $filterPemasukan"
))['total'];

$pemasukanPiutang = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pemasukan
    WHERE jenis='pembayaran_piutang' AND $filterPemasukan"
))['total'];

$pemasukanLainnya = $pemasukan - $pemasukanServis - $pemasukanSparepart - $pemasukanPiutang;

$piutangAktif = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(h.jumlah - COALESCE(pb.total_bayar, 0)), 0) AS total
     FROM piutang h
     LEFT JOIN (
         SELECT id_piutang, SUM(jumlah) AS total_bayar
         FROM pembayaran_piutang
         GROUP BY id_piutang
     ) pb ON pb.id_piutang = h.id_piutang
     WHERE h.jumlah > COALESCE(pb.total_bayar, 0)"
))['total'];

$hppPenjualanFifo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(fd.subtotal_hpp), 0) AS total_hpp
     FROM penjualan_fifo_detail fd
     JOIN penjualan_detail d ON fd.id_detail = d.id_detail
     JOIN penjualan pj ON d.id_penjualan = pj.id_penjualan
    WHERE $filterPenjualan"
))['total_hpp'];

$omsetPenjualan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(pj.total), 0) AS total FROM penjualan pj WHERE $filterPenjualan"
))['total'];

$labaKotorPenjualan = $omsetPenjualan - $hppPenjualanFifo;

$arusKas = mysqli_query($conn,
    "SELECT tanggal, jenis, keterangan, jumlah, 'Pemasukan' AS tipe, 1 AS urutan
     FROM pemasukan
    WHERE $filterPemasukan
     UNION ALL
     SELECT tanggal, jenis, keterangan, jumlah, 'Pengeluaran' AS tipe, 2 AS urutan
     FROM pengeluaran
    WHERE $filterPengeluaran
     ORDER BY tanggal DESC, urutan ASC"
);

include "../includes/header.php";
?>

<?php if (isset($_GET['tersimpan'])): ?>
<div class="alert alert-success">Transaksi keuangan berhasil disimpan.</div>
<?php endif; ?>
<?php if (isset($errorArusKas)): ?>
<div class="alert alert-warning"><?= htmlspecialchars($errorArusKas) ?></div>
<?php endif; ?>

<div class="card no-print">
    <h3 style="margin-bottom:12px;">Tambah Arus Kas</h3>
    <form method="POST" class="filter-bar">
        <div class="form-group">
            <label>Tipe</label>
            <select name="tipe" id="tipe_arus_kas" onchange="toggleJenisPengeluaran()">
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>
        <div class="form-group" id="jenis_pengeluaran_wrap" style="display:none;">
            <label>Jenis Pengeluaran</label>
            <select name="jenis_pengeluaran">
                <option value="operasional">Biaya Operasional</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" placeholder="Contoh: Listrik, jasa tambahan" required>
        </div>
        <div class="form-group">
            <label>Jumlah (Rp)</label>
            <input type="number" name="jumlah" min="1" required>
        </div>
        <button type="submit" name="simpan_arus_kas" class="btn btn-success">Simpan</button>
    </form>
    <small>Servis dan penjualan tercatat dari menu transaksi. Pembayaran hutang masuk otomatis dari menu Piutang; jangan catat ulang di sini. Pembelian sparepart/biaya operasional dicatat sebagai pengeluaran.</small>
</div>

<div class="filter-bar">
    <form method="GET">
        <div class="form-group" id="tanggal-pilihan">
            <label>Tanggal tertentu</label>
            <?php if ($tanggalPilihan): ?>
                <?php foreach ($tanggalPilihan as $tanggal): ?>
                <input type="date" name="tanggal[]" value="<?= htmlspecialchars($tanggal) ?>">
                <?php endforeach; ?>
            <?php else: ?>
                <input type="date" name="tanggal[]" value="">
            <?php endif; ?>
            <button type="button" class="btn btn-secondary btn-sm" onclick="tambahTanggal()">+ Tanggal</button>
            <small>Pilih tanggal tertentu, misalnya tanggal 1 dan 5.</small>
        </div>
        <div class="form-group">
            <label>Dari</label>
            <input type="date" name="dari" value="<?= $dari ?>" <?= $tanggalPilihan ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label>Sampai</label>
            <input type="date" name="sampai" value="<?= $sampai ?>" <?= $tanggalPilihan ? 'disabled' : '' ?>>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <button type="button" onclick="window.print()" class="btn btn-warning">Cetak Hasil Filter</button>
    </form>
</div>

<div class="print-only" style="display:none;">
    <p>Periode laporan: <strong><?= htmlspecialchars($labelPeriode) ?></strong></p>
</div>

<div class="card-grid">
    <div class="stat-card green">
        <h3>Total Pemasukan</h3>
        <div class="value"><?= formatRupiah($pemasukan) ?></div>
    </div>
    <div class="stat-card red">
        <h3>Total Pengeluaran</h3>
        <div class="value"><?= formatRupiah($pengeluaran) ?></div>
    </div>
    <div class="stat-card <?= $saldo >= 0 ? 'green' : 'red' ?>">
        <h3>Saldo / Cashflow Net</h3>
        <div class="value"><?= formatRupiah($saldo) ?></div>
    </div>
</div>

<div class="card-grid">
    <div class="stat-card green">
        <h3>Pemasukan Servis</h3>
        <div class="value"><?= formatRupiah($pemasukanServis) ?></div>
    </div>
    <div class="stat-card green">
        <h3>Penerimaan Penjualan Sparepart</h3>
        <div class="value"><?= formatRupiah($pemasukanSparepart) ?></div>
    </div>
    <div class="stat-card green">
        <h3>Penerimaan Piutang</h3>
        <div class="value"><?= formatRupiah($pemasukanPiutang) ?></div>
    </div>
    <div class="stat-card green">
        <h3>Pemasukan Lainnya</h3>
        <div class="value"><?= formatRupiah($pemasukanLainnya) ?></div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <strong>Total piutang yang belum tertagih</strong>
    <span style="font-size:1.25rem;font-weight:800;color:#dc2626;margin-left:8px;"><?= formatRupiah($piutangAktif) ?></span>
    <a href="../pelanggan/index.php" class="btn btn-primary btn-sm" style="margin-left:12px;">Lihat Piutang</a>
</div>

<div class="alert alert-info">
    Pemasukan adalah uang yang benar-benar diterima. Sisa tagihan belum dihitung sebagai pemasukan; saat pelanggan membayar, penerimaan piutang otomatis masuk ke kas pada tanggal pembayaran. Hutang pelanggan tidak dicatat sebagai pengeluaran.
</div>
<div class="alert alert-warning">
    Transaksi sebelum perubahan ini tidak dihitung ulang otomatis. Periksa piutang lama terhadap pemasukan transaksi sebelumnya agar penerimaan lama tidak terhitung ganda.
</div>

<div class="card">
    <h3 style="margin-bottom:12px;">Omzet Transaksi & Analisa HPP Sparepart (Metode FIFO)</h3>
    <small>Omzet dan laba kotor dihitung berdasarkan nilai penjualan, sedangkan total pemasukan di atas berdasarkan uang yang benar-benar diterima.</small>
    <table>
        <tr><th width="300">Omset Penjualan Sparepart</th><td><strong><?= formatRupiah($omsetPenjualan) ?></strong></td></tr>
        <tr><th>Total HPP Modal Sparepart (FIFO)</th><td style="color:#dc3545;"><strong><?= formatRupiah($hppPenjualanFifo) ?></strong></td></tr>
        <tr style="font-weight:bold;">
            <th>Laba Kotor Penjualan Sparepart</th>
            <td style="color:#28a745;"><?= formatRupiah($labaKotorPenjualan) ?></td>
        </tr>
    </table>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;">Arus Kas Gabungan</h3>
    <p class="screen-only" style="margin-bottom:12px;">Periode: <strong><?= htmlspecialchars($labelPeriode) ?></strong></p>
    <table>
        <tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Tipe</th><th>Jumlah</th></tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($arusKas)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $d['jenis'])) ?></td>
            <td><?= htmlspecialchars($d['keterangan']) ?></td>
            <td style="color:<?= $d['tipe'] === 'Pemasukan' ? '#28a745' : '#dc3545' ?>;"><?= $d['tipe'] ?></td>
            <td><?= formatRupiah($d['jumlah']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
function tambahTanggal() {
    var input = document.createElement('input');
    input.type = 'date';
    input.name = 'tanggal[]';
    document.getElementById('tanggal-pilihan').insertBefore(input, document.querySelector('#tanggal-pilihan .btn'));
}

function toggleJenisPengeluaran() {
    document.getElementById('jenis_pengeluaran_wrap').style.display =
        document.getElementById('tipe_arus_kas').value === 'pengeluaran' ? 'block' : 'none';
}
</script>

<?php include "../includes/footer.php"; ?>
