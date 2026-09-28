<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";

$pageTitle = "Edit Kendaraan";
$id = (int)$_GET['id'];
$pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");

if (isset($_POST['update'])) {
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $no_polisi = mysqli_real_escape_string($conn, $_POST['no_polisi']);
    $merk = mysqli_real_escape_string($conn, $_POST['merk']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);

    mysqli_query($conn,
        "UPDATE kendaraan SET id_pelanggan=$id_pelanggan, no_polisi='$no_polisi',
         merk='$merk', jenis='$jenis' WHERE id_kendaraan=$id"
    );
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kendaraan WHERE id_kendaraan=$id"));

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Pemilik (Pelanggan)</label>
            <select name="id_pelanggan" required>
                <?php while ($p = mysqli_fetch_assoc($pelanggan)): ?>
                <option value="<?= $p['id_pelanggan'] ?>" <?= $p['id_pelanggan'] == $data['id_pelanggan'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>No. Polisi</label>
            <input type="text" name="no_polisi" value="<?= htmlspecialchars($data['no_polisi']) ?>" required>
        </div>
        <div class="form-group">
            <label>Merk</label>
            <select name="merk" required>
                <?php
                $merkList = getMerkKendaraanList();
                if (!in_array($data['merk'], $merkList)) {
                    echo '<option value="' . htmlspecialchars($data['merk']) . '" selected>' . htmlspecialchars($data['merk']) . '</option>';
                }
                renderMerkOptions($data['merk']);
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jenis Kendaraan</label>
            <input type="text" name="jenis" value="<?= htmlspecialchars($data['jenis']) ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
