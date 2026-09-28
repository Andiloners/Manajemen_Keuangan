<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Laporan Transaksi Servis";

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
$filterTanggal = $tanggalPilihan ? "s.tanggal IN ($tanggalSql)" : "s.tanggal BETWEEN '$dari' AND '$sampai'";
$filterTanggalTotal = $tanggalPilihan ? "tanggal IN ($tanggalSql)" : "tanggal BETWEEN '$dari' AND '$sampai'";
$labelPeriode = $tanggalPilihan
    ? implode(', ', array_map(function ($tanggal) { return date('d/m/Y', strtotime($tanggal)); }, $tanggalPilihan))
    : date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai));

$data = mysqli_query($conn,
    "SELECT s.*, k.no_polisi, k.merk, p.nama AS nama_pelanggan, u.username
     FROM servis s
     JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
     JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     LEFT JOIN users u ON s.id_user = u.id
    WHERE $filterTanggal
     ORDER BY s.tanggal DESC"
);

$total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(biaya_jasa),0) AS jml FROM servis WHERE $filterTanggalTotal"
))['jml'];

include "../includes/header.php";
?>

<div class="filter-bar">
    <form method="GET">
        <div class="form-group">
            <label>Tanggal tertentu</label>
            <div id="tanggal-pilihan-servis">
                <?php if ($tanggalPilihan): ?>
                    <?php foreach ($tanggalPilihan as $tanggal): ?>
                    <input type="date" name="tanggal[]" value="<?= htmlspecialchars($tanggal) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="date" name="tanggal[]" value="">
                <?php endif; ?>
                <button type="button" class="btn btn-secondary btn-sm" onclick="tambahTanggalServis()">+ Tanggal</button>
                <small>Pilih tanggal tertentu, misalnya tanggal 1 dan 5.</small>
            </div>
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

<div class="card">
    <h3 style="margin-bottom:16px;">Riwayat Transaksi Servis</h3>
    <p class="screen-only" style="margin-bottom:12px;">Periode: <strong><?= htmlspecialchars($labelPeriode) ?></strong></p>
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Polisi</th>
            <th>Pelanggan</th>
            <th>Jenis Servis</th>
            <th>Biaya</th>
            <th>Input oleh</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
            <td><?= htmlspecialchars($d['no_polisi']) ?></td>
            <td><?= htmlspecialchars($d['nama_pelanggan']) ?></td>
            <td><?= htmlspecialchars($d['jenis_servis']) ?></td>
            <td><?= formatRupiah($d['biaya_jasa']) ?></td>
            <td><?= htmlspecialchars($d['username'] ?? '-') ?></td>
        </tr>
        <?php endwhile; ?>
        <tr style="font-weight:bold;background:#f0f0f0;">
            <td colspan="5" style="text-align:right;">Total</td>
            <td><?= formatRupiah($total) ?></td>
            <td></td>
        </tr>
    </table>
</div>

<script>
function tambahTanggalServis() {
    var input = document.createElement('input');
    input.type = 'date';
    input.name = 'tanggal[]';
    document.getElementById('tanggal-pilihan-servis').insertBefore(input, document.querySelector('#tanggal-pilihan-servis .btn'));
}
</script>

<?php include "../includes/footer.php"; ?>
