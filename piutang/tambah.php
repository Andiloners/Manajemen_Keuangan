<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Catat Hutang Pelanggan";
$error = '';
$pelanggan = mysqli_query($conn, "SELECT id_pelanggan, nama, telepon FROM pelanggan ORDER BY nama");

if (isset($_POST['simpan'])) {
    $id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $jumlah = (float)($_POST['jumlah'] ?? 0);
    $jenisValid = ['servis', 'sparepart', 'lainnya'];
    $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)
        && checkdate((int)substr($tanggal, 5, 2), (int)substr($tanggal, 8, 2), (int)substr($tanggal, 0, 4));

    if ($id_pelanggan <= 0 || !$tanggalValid
        || !in_array($jenis, $jenisValid, true) || $keterangan === '' || strlen($keterangan) > 255 || $jumlah <= 0) {
        $error = 'Lengkapi pelanggan, tanggal, alasan hutang, dan jumlah yang valid.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO piutang (id_pelanggan, tanggal, jenis, keterangan, jumlah, id_user)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $id_user = (int)$_SESSION['id'];
        mysqli_stmt_bind_param($stmt, 'isssdi', $id_pelanggan, $tanggal, $jenis, $keterangan, $jumlah, $id_user);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../pelanggan/index.php");
            exit;
        }
        $error = 'Hutang tidak dapat disimpan. Pastikan pelanggan masih tersedia.';
    }
}

include "../includes/header.php";
?>

<?php if ($error): ?><div class="alert alert-warning"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="alert alert-info">
    Untuk servis atau penjualan baru, catat dari menu transaksi—sisa pembayaran akan menjadi piutang otomatis. Gunakan formulir ini untuk hutang lama atau tagihan lain agar tidak tercatat dua kali.
</div>

<div class="card">
    <?php if (!$pelanggan || mysqli_num_rows($pelanggan) === 0): ?>
        <div class="alert alert-info">Belum ada pelanggan. Tambahkan data pelanggan terlebih dahulu.</div>
        <a href="../pelanggan/tambah.php" class="btn btn-primary">+ Tambah Pelanggan</a>
    <?php else: ?>
    <form method="POST">
        <div class="form-group">
            <label>Pelanggan</label>
            <select name="id_pelanggan" required>
                <option value="">-- Pilih pelanggan --</option>
                <?php while ($p = mysqli_fetch_assoc($pelanggan)): ?>
                <option value="<?= (int)$p['id_pelanggan'] ?>" <?= (($_POST['id_pelanggan'] ?? '') == $p['id_pelanggan']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama']) ?><?= $p['telepon'] ? ' - ' . htmlspecialchars($p['telepon']) : '' ?>
                </option>
                <?php endwhile; ?>
            </select>
            <small><a href="../pelanggan/tambah.php">Pelanggan belum terdaftar? Tambahkan di sini.</a></small>
        </div>
        <div class="form-group">
            <label>Tanggal Hutang</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group">
            <label>Hutang karena</label>
            <select name="jenis" required>
                <option value="">-- Pilih alasan --</option>
                <option value="servis" <?= ($_POST['jenis'] ?? '') === 'servis' ? 'selected' : '' ?>>Servis</option>
                <option value="sparepart" <?= ($_POST['jenis'] ?? '') === 'sparepart' ? 'selected' : '' ?>>Pengambilan sparepart</option>
                <option value="lainnya" <?= ($_POST['jenis'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>
        <div class="form-group">
            <label>Rincian / Keterangan</label>
            <textarea name="keterangan" maxlength="255" required placeholder="Contoh: servis turun mesin; atau pengambilan oli 2 botol"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Total Hutang (Rp)</label>
            <input type="number" name="jumlah" min="1" step="0.01" value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>" required>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan Hutang</button>
        <a href="../pelanggan/index.php" class="btn btn-warning">Batal</a>
    </form>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
