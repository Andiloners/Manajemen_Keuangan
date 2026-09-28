<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";
include "../includes/fifo_helper.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$pageTitle = "Tambah Pengeluaran";

if (isset($_POST['simpan'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $jumlah = (float)$_POST['jumlah'];
    $id_user = $_SESSION['id'];

    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn,
            "INSERT INTO pengeluaran (tanggal, id_user, jenis, keterangan, jumlah)
             VALUES ('$tanggal', $id_user, '$jenis', '$keterangan', $jumlah)"
        );
        $id_pengeluaran = mysqli_insert_id($conn);

        if ($jenis === 'pembelian_sparepart' && !empty($_POST['id_sparepart'])) {
            $id_sparepart = (int)$_POST['id_sparepart'];
            $qty = (int)$_POST['qty_beli'];
            if ($qty > 0) {
                // Hitung harga beli per unit untuk batch ini
                $harga_beli_per_unit = $jumlah / $qty;
                tambahBatchFifo($conn, $id_sparepart, $qty, $harga_beli_per_unit, $tanggal, $id_pengeluaran, $id_user);
            }
        }

        mysqli_commit($conn);
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Gagal menyimpan pengeluaran: " . $e->getMessage();
    }
}

$sparepart = mysqli_query($conn, "SELECT * FROM sparepart ORDER BY merk_kendaraan, nama");

include "../includes/header.php";
?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Jenis Pengeluaran</label>
            <select name="jenis" id="jenis" required onchange="toggleSparepart()">
                <option value="pembelian_sparepart">Pembelian Sparepart</option>
                <option value="operasional">Biaya Operasional</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
        <div id="sparepart_fields">
            <div class="form-group">
                <label>Filter Merk Kendaraan</label>
                <select id="filter_merk" onchange="filterSparepartBeli()">
                    <option value="">-- Semua Merk --</option>
                    <?php renderMerkOptions(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Sparepart (untuk update stok)</label>
                <select name="id_sparepart" id="id_sparepart">
                    <option value="">-- Pilih --</option>
                    <?php while ($s = mysqli_fetch_assoc($sparepart)): ?>
                    <option value="<?= $s['id_sparepart'] ?>" data-merk="<?= htmlspecialchars($s['merk_kendaraan']) ?>">
                        [<?= htmlspecialchars($s['merk_kendaraan']) ?>] <?= htmlspecialchars($s['kode']) ?> - <?= htmlspecialchars($s['nama']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Beli (pcs)</label>
                <input type="number" name="qty_beli" min="0" value="0">
            </div>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" required>
        </div>
        <div class="form-group">
            <label>Jumlah (Rp)</label>
            <input type="number" name="jumlah" min="0" required>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<script>
function toggleSparepart() {
    document.getElementById('sparepart_fields').style.display =
        document.getElementById('jenis').value === 'pembelian_sparepart' ? 'block' : 'none';
}

function filterSparepartBeli() {
    var merk = document.getElementById('filter_merk').value;
    var opts = document.getElementById('id_sparepart').options;
    for (var i = 1; i < opts.length; i++) {
        var itemMerk = opts[i].getAttribute('data-merk');
        opts[i].style.display = (!merk || itemMerk === merk) ? '' : 'none';
        opts[i].disabled = (merk && itemMerk !== merk);
    }
    document.getElementById('id_sparepart').selectedIndex = 0;
}

toggleSparepart();
</script>

<?php include "../includes/footer.php"; ?>
