<?php
include "../config/database.php";
include "../includes/auth.php";

$pageTitle = "Tambah Pelanggan";

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $id_user = $_SESSION['id'];

    mysqli_query($conn, "INSERT INTO pelanggan (nama, alamat, telepon, id_user) VALUES ('$nama','$alamat','$telepon', $id_user)");
    header("Location: master.php");
    exit;
}

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" required>
        </div>
        <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon">
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat"></textarea>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
