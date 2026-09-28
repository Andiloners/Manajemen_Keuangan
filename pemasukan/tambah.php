<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Tambah Pemasukan";

if (isset($_POST['simpan'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jumlah = (float)$_POST['jumlah'];
    $id_user = $_SESSION['id'];

    mysqli_query($conn,
        "INSERT INTO pemasukan (tanggal, id_user, jenis, keterangan, jumlah)
         VALUES ('$tanggal', $id_user, 'lainnya', '$keterangan', $jumlah)"
    );
    header("Location: index.php");
    exit;
}

include "../includes/header.php";
?>

<div class="alert alert-info">
    Gunakan formulir manual hanya untuk pemasukan lain. Penerimaan servis, penjualan sparepart, dan pembayaran piutang dicatat otomatis dari transaksinya—jangan masukkan ulang.
</div>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" required>
        </div>
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" name="jumlah" min="0" required>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
