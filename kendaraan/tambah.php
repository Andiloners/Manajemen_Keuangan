<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";

$pageTitle = "Tambah Kendaraan";
$pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");

if (isset($_POST['simpan'])) {
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $no_polisi = mysqli_real_escape_string($conn, $_POST['no_polisi']);
    $merk = mysqli_real_escape_string($conn, $_POST['merk']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);

    $id_user = $_SESSION['id'];

    mysqli_query($conn,
        "INSERT INTO kendaraan (id_pelanggan, no_polisi, merk, jenis, id_user)
         VALUES ($id_pelanggan, '$no_polisi', '$merk', '$jenis', $id_user)"
    );
    header("Location: index.php");
    exit;
}

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Pemilik (Pelanggan)</label>
            <select name="id_pelanggan" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php while ($p = mysqli_fetch_assoc($pelanggan)): ?>
                <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>No. Polisi</label>
            <input type="text" name="no_polisi" required placeholder="B 1234 XYZ">
        </div>
        <div class="form-group">
            <label>Merk</label>
            <select name="merk" required>
                <option value="">-- Pilih Merk --</option>
                <?php renderMerkOptions(); ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jenis Kendaraan</label>
            <input type="text" name="jenis" required placeholder="Mobil, Motor, dll">
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
