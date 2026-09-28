<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Edit Pengeluaran";
$id = (int)$_GET['id'];

if (isset($_POST['update'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jumlah = (float)$_POST['jumlah'];

    mysqli_query($conn,
        "UPDATE pengeluaran SET tanggal='$tanggal', jenis='$jenis',
         keterangan='$keterangan', jumlah=$jumlah WHERE id_pengeluaran=$id"
    );
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengeluaran WHERE id_pengeluaran=$id"));

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>
        </div>
        <div class="form-group">
            <label>Jenis</label>
            <select name="jenis" required>
                <option value="pembelian_sparepart" <?= $data['jenis'] === 'pembelian_sparepart' ? 'selected' : '' ?>>Pembelian Sparepart</option>
                <option value="operasional" <?= $data['jenis'] === 'operasional' ? 'selected' : '' ?>>Biaya Operasional</option>
                <option value="lainnya" <?= $data['jenis'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" value="<?= htmlspecialchars($data['keterangan']) ?>" required>
        </div>
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" name="jumlah" value="<?= $data['jumlah'] ?>" min="0" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
