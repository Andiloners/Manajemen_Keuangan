<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Input Servis Baru";
$kendaraan = mysqli_query($conn,
    "SELECT k.id_kendaraan, k.no_polisi, k.merk, p.nama
     FROM kendaraan k JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
     ORDER BY k.no_polisi"
);

if (isset($_POST['simpan'])) {
    $id_kendaraan = (int)$_POST['id_kendaraan'];
    $tanggal = $_POST['tanggal'] ?? '';
    $jenis_servis = trim($_POST['jenis_servis'] ?? '');
    $biaya_jasa = round((float)$_POST['biaya_jasa'], 2);
    $jumlahDibayar = isset($_POST['jumlah_dibayar']) ? round((float)$_POST['jumlah_dibayar'], 2) : $biaya_jasa;
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)
        && checkdate((int)substr($tanggal, 5, 2), (int)substr($tanggal, 8, 2), (int)substr($tanggal, 0, 4));

    if (!$tanggalValid || $jenis_servis === '' || $biaya_jasa < 0 || $jumlahDibayar < 0 || $jumlahDibayar > $biaya_jasa) {
        $error = 'Pastikan tanggal, jenis servis, dan pembayaran valid. Pembayaran tidak boleh melebihi biaya servis.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $id_user = (int)$_SESSION['id'];
            $stmt = mysqli_prepare($conn,
                "INSERT INTO servis (id_kendaraan, tanggal, id_user, jenis_servis, biaya_jasa, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'isisds', $id_kendaraan, $tanggal, $id_user, $jenis_servis, $biaya_jasa, $keterangan);
            if (!mysqli_stmt_execute($stmt)) {
                throw new DomainException('Data servis tidak dapat disimpan.');
            }
            $id_servis = mysqli_insert_id($conn);

            if ($jumlahDibayar > 0) {
                $ketPemasukan = 'Pembayaran servis #' . $id_servis . ': ' . $jenis_servis;
                $income = mysqli_prepare($conn,
                    "INSERT INTO pemasukan (tanggal, id_user, jenis, keterangan, jumlah, referensi_id)
                     VALUES (?, ?, 'servis', ?, ?, ?)"
                );
                mysqli_stmt_bind_param($income, 'sisdi', $tanggal, $id_user, $ketPemasukan, $jumlahDibayar, $id_servis);
                if (!mysqli_stmt_execute($income)) {
                    throw new DomainException('Pemasukan servis tidak dapat dicatat.');
                }
            }

            $sisaHutang = round($biaya_jasa - $jumlahDibayar, 2);
            if ($sisaHutang > 0) {
                $sourceType = 'servis';
                $ketHutang = 'Servis #' . $id_servis . ' - ' . $jenis_servis;
                $debt = mysqli_prepare($conn,
                    "INSERT INTO piutang (id_pelanggan, tanggal, jenis, keterangan, jumlah, id_user, sumber_jenis, sumber_id)
                     SELECT k.id_pelanggan, ?, 'servis', ?, ?, ?, ?, ?
                     FROM kendaraan k WHERE k.id_kendaraan = ?"
                );
                mysqli_stmt_bind_param($debt, 'ssdisii', $tanggal, $ketHutang, $sisaHutang, $id_user, $sourceType, $id_servis, $id_kendaraan);
                if (!mysqli_stmt_execute($debt) || mysqli_stmt_affected_rows($debt) !== 1) {
                    throw new DomainException('Piutang servis tidak dapat dicatat.');
                }
            }

            mysqli_commit($conn);
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = $e instanceof DomainException ? $e->getMessage() : 'Transaksi servis gagal disimpan. Transaksi keuangan dibatalkan.';
        }
    }
}

include "../includes/header.php";
?>

<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Kendaraan</label>
<?php
                $hasKendaraan = mysqli_num_rows($kendaraan) > 0;
                ?>
                <select name="id_kendaraan" required>
                    <?php if ($hasKendaraan): ?>
                        <option value="">-- Pilih Kendaraan --</option>
                        <?php while ($k = mysqli_fetch_assoc($kendaraan)): ?>
                            <option value="<?= $k['id_kendaraan'] ?>">
                                <?= htmlspecialchars($k['no_polisi']) ?> - <?= htmlspecialchars($k['merk']) ?> (<?= htmlspecialchars($k['nama']) ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>-- Tidak ada kendaraan, tambahkan dulu --</option>
                    <?php endif; ?>
                </select>
                <?php if (!$hasKendaraan): ?>
                    <small><a href="<?= $base ?>kendaraan/tambah.php">Tambah Kendaraan</a></small>
                <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Tanggal Servis</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Jenis Servis</label>
            <input type="text" name="jenis_servis" required placeholder="Ganti oli, tune up, dll">
        </div>
        <div class="form-group">
            <label>Biaya Jasa</label>
            <input type="number" name="biaya_jasa" id="biaya_jasa" min="0" step="0.01" value="<?= htmlspecialchars($_POST['biaya_jasa'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Dibayar Sekarang (Rp)</label>
            <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" min="0" step="0.01" value="<?= htmlspecialchars($_POST['jumlah_dibayar'] ?? '') ?>">
            <small>Otomatis mengikuti biaya servis. Jika belum lunas, ubah sesuai jumlah yang diterima; sisanya masuk ke piutang pelanggan.</small>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan Transaksi</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<script>
const biayaJasa = document.getElementById('biaya_jasa');
const jumlahDibayar = document.getElementById('jumlah_dibayar');
let pembayaranDiubah = jumlahDibayar.value !== '';
jumlahDibayar.addEventListener('input', () => pembayaranDiubah = true);
biayaJasa.addEventListener('input', () => {
    if (!pembayaranDiubah) jumlahDibayar.value = biayaJasa.value;
});
if (!pembayaranDiubah) jumlahDibayar.value = biayaJasa.value;
</script>

<?php include "../includes/footer.php"; ?>
