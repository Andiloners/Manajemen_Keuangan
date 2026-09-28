<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";
include "../includes/fifo_helper.php";

$pageTitle = "Tambah Sparepart";

if (isset($_POST['simpan'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $merk_kendaraan = mysqli_real_escape_string($conn, $_POST['merk_kendaraan']);
    $harga = (float)$_POST['harga'];
    $harga_beli = (float)$_POST['harga_beli'];
    $stok = (int)$_POST['stok'];

    if (!isValidMerk($merk_kendaraan)) {
        $error = "Merk kendaraan tidak valid.";
    } else {
        $id_user = $_SESSION['id'];
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn,
                "INSERT INTO sparepart (kode, nama, merk_kendaraan, harga, stok, id_user)
                 VALUES ('$kode','$nama','$merk_kendaraan',$harga,$stok, $id_user)"
            );
            $id_sparepart = mysqli_insert_id($conn);

            if ($stok > 0) {
                tambahBatchFifo($conn, $id_sparepart, $stok, $harga_beli, date('Y-m-d'), null, $id_user);
            }

            mysqli_commit($conn);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

include "../includes/header.php";
?>

<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Kode Sparepart</label>
            <input type="text" name="kode" required>
        </div>
        <div class="form-group">
            <label>Nama Sparepart</label>
            <input type="text" name="nama" required>
        </div>
        <div class="form-group">
            <label>Merk Kendaraan</label>
            <select name="merk_kendaraan" required>
                <option value="">-- Pilih Merk --</option>
                <?php renderMerkOptions($_POST['merk_kendaraan'] ?? ''); ?>
            </select>
        </div>
        <div class="form-group">
            <label>Harga Beli (HPP Awal Batch #1)</label>
            <input type="number" name="harga_beli" min="0" required placeholder="Harga beli per unit dari supplier">
        </div>
        <div class="form-group">
            <label>Harga Jual (ke Pelanggan)</label>
            <input type="number" name="harga" min="0" required placeholder="Harga jual per unit">
        </div>
        <div class="form-group">
            <label>Stok Awal</label>
            <input type="number" name="stok" min="0" value="0" required>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
