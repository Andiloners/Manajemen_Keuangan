<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Edit Pelanggan";
$id = (int)$_GET['id'];

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);

    mysqli_query($conn, "UPDATE pelanggan SET nama='$nama', alamat='$alamat', telepon='$telepon' WHERE id_pelanggan=$id");
    header("Location: master.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan=$id"));

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($data['telepon']) ?>">
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat"><?= htmlspecialchars($data['alamat']) ?></textarea>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="master.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
