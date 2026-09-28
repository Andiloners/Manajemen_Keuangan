<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";

$pageTitle = "Edit Sparepart";
$id = (int)$_GET['id'];

if (isset($_POST['update'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $merk_kendaraan = mysqli_real_escape_string($conn, $_POST['merk_kendaraan']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];

    if (!isValidMerk($merk_kendaraan)) {
        $error = "Merk kendaraan tidak valid.";
    } else {
        mysqli_query($conn,
            "UPDATE sparepart SET kode='$kode', nama='$nama', merk_kendaraan='$merk_kendaraan',
             harga=$harga, stok=$stok WHERE id_sparepart=$id"
        );
        header("Location: index.php");
        exit;
    }
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sparepart WHERE id_sparepart=$id"));

include "../includes/header.php";
?>

<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Kode Sparepart</label>
            <input type="text" name="kode" value="<?= htmlspecialchars($data['kode']) ?>" required>
        </div>
        <div class="form-group">
            <label>Nama Sparepart</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label>Merk Kendaraan</label>
            <select name="merk_kendaraan" required>
                <?php renderMerkOptions($data['merk_kendaraan'] ?? 'Honda'); ?>
            </select>
        </div>
        <div class="form-group">
            <label>Harga</label>
            <input type="number" name="harga" value="<?= $data['harga'] ?>" min="0" required>
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" value="<?= $data['stok'] ?>" min="0" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
