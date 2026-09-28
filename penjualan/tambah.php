<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/merk.php";
include "../includes/fifo_helper.php";

$pageTitle = "Transaksi Penjualan Sparepart";
$pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");
$sparepart = mysqli_query($conn, "SELECT * FROM sparepart WHERE stok > 0 ORDER BY merk_kendaraan, nama");

if (isset($_POST['simpan'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? '');
    $id_pelanggan = !empty($_POST['id_pelanggan']) ? (int)$_POST['id_pelanggan'] : null;
    $id_sparepart = (int)$_POST['id_sparepart'];
    $qty = (int)$_POST['qty'];

    $sp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sparepart WHERE id_sparepart=$id_sparepart"));

    if (!$sp || $qty <= 0) {
        $error = "Data sparepart atau qty tidak valid.";
    } elseif ($qty > $sp['stok']) {
        $error = "Stok tidak mencukupi! Stok tersedia: " . $sp['stok'];
    } else {
        $harga = $sp['harga'];
        $subtotal = round($harga * $qty, 2);
        $total = $subtotal;
        $jumlahDibayar = isset($_POST['jumlah_dibayar']) ? round((float)$_POST['jumlah_dibayar'], 2) : (float)$total;
        $sisaHutang = round($total - $jumlahDibayar, 2);

        if ($jumlahDibayar < 0 || $jumlahDibayar > $total) {
            $error = 'Jumlah pembayaran tidak valid atau melebihi total transaksi.';
        } elseif ($sisaHutang > 0 && !$id_pelanggan) {
            $error = 'Pilih nama pelanggan jika penjualan belum dibayar lunas agar hutangnya tercatat.';
        } else {
          mysqli_begin_transaction($conn);
          try {
            $id_user = $_SESSION['id'];
            $idPelangganSql = $id_pelanggan === null ? 'NULL' : (string)$id_pelanggan;
            mysqli_query($conn,
                "INSERT INTO penjualan (tanggal, id_user, id_pelanggan, total)
                 VALUES ('$tanggal', $id_user, $idPelangganSql, $total)"
            );
            $id_penjualan = mysqli_insert_id($conn);

            mysqli_query($conn,
                "INSERT INTO penjualan_detail (id_penjualan, id_sparepart, qty, harga, subtotal)
                 VALUES ($id_penjualan, $id_sparepart, $qty, $harga, $subtotal)"
            );
            $id_detail = mysqli_insert_id($conn);

            // Eksekusi Algoritma FIFO untuk Potong Stok per Batch & Record HPP
            $totalHppFifo = prosesKeluarFifo($conn, $id_detail, $id_sparepart, $qty);

            if ($jumlahDibayar > 0) {
                $ket = "Pembayaran penjualan sparepart [{$sp['merk_kendaraan']}]: " . $sp['nama'] . " ($qty pcs)";
                mysqli_query($conn,
                    "INSERT INTO pemasukan (tanggal, id_user, jenis, keterangan, jumlah, referensi_id)
                     VALUES ('$tanggal', $id_user, 'penjualan_sparepart', '$ket', $jumlahDibayar, $id_penjualan)"
                );
            }

            if ($sisaHutang > 0) {
                $sourceType = 'penjualan_sparepart';
                $ketHutang = mysqli_real_escape_string($conn, "Penjualan #$id_penjualan - {$sp['nama']} ($qty pcs)");
                mysqli_query($conn,
                    "INSERT INTO piutang (id_pelanggan, tanggal, jenis, keterangan, jumlah, id_user, sumber_jenis, sumber_id)
                     VALUES ($id_pelanggan, '$tanggal', 'sparepart', '$ketHutang', $sisaHutang, $id_user, '$sourceType', $id_penjualan)"
                );
            }

            mysqli_commit($conn);
            header("Location: index.php");
            exit;
          } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = "Transaksi gagal disimpan. Pastikan migrasi piutang keuangan sudah dijalankan.";
          }
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
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Pelanggan (wajib jika hutang)</label>
            <select name="id_pelanggan">
                <option value="">-- Umum --</option>
                <?php mysqli_data_seek($pelanggan, 0); while ($p = mysqli_fetch_assoc($pelanggan)): ?>
                <option value="<?= $p['id_pelanggan'] ?>" <?= (($_POST['id_pelanggan'] ?? '') == $p['id_pelanggan']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                <?php endwhile; ?>
            </select>
            <small>Pelanggan boleh dikosongkan jika pembayaran lunas.</small>
        </div>
        <div class="form-group">
            <label>Merk Kendaraan</label>
            <select id="filter_merk" onchange="filterSparepart()">
                <option value="">-- Semua Merk --</option>
                <?php renderMerkOptions(); ?>
            </select>
        </div>
        <div class="form-group">
            <label>Sparepart</label>
            <select name="id_sparepart" id="sparepart" required onchange="updateHarga()">
                <option value="">-- Pilih Sparepart --</option>
                <?php mysqli_data_seek($sparepart, 0); while ($s = mysqli_fetch_assoc($sparepart)): ?>
                <option value="<?= $s['id_sparepart'] ?>"
                    data-harga="<?= $s['harga'] ?>"
                    data-stok="<?= $s['stok'] ?>"
                    data-merk="<?= htmlspecialchars($s['merk_kendaraan']) ?>"
                    <?= (($_POST['id_sparepart'] ?? '') == $s['id_sparepart']) ? 'selected' : '' ?>>
                    [<?= htmlspecialchars($s['merk_kendaraan']) ?>] <?= htmlspecialchars($s['kode']) ?> - <?= htmlspecialchars($s['nama']) ?> (Stok: <?= $s['stok'] ?>) - <?= formatRupiah($s['harga']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" name="qty" id="qty" min="1" value="<?= htmlspecialchars($_POST['qty'] ?? '1') ?>" required onchange="updateHarga()">
        </div>
        <div class="form-group">
            <label>Estimasi Total</label>
            <input type="text" id="total_display" readonly value="Rp 0">
        </div>
        <div class="form-group">
            <label>Dibayar Sekarang (Rp)</label>
            <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" min="0" step="0.01" value="<?= htmlspecialchars($_POST['jumlah_dibayar'] ?? '') ?>">
            <small>Otomatis mengikuti total. Jika belum lunas, ubah sesuai jumlah yang diterima; sisanya masuk ke piutang pelanggan.</small>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan & Update Stok</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
</div>

<script>
function filterSparepart() {
    var merk = document.getElementById('filter_merk').value;
    var opts = document.getElementById('sparepart').options;
    for (var i = 1; i < opts.length; i++) {
        var itemMerk = opts[i].getAttribute('data-merk');
        opts[i].style.display = (!merk || itemMerk === merk) ? '' : 'none';
        opts[i].disabled = (merk && itemMerk !== merk);
    }
    document.getElementById('sparepart').selectedIndex = 0;
    updateHarga();
}

function updateHarga() {
    var sel = document.getElementById('sparepart');
    var opt = sel.options[sel.selectedIndex];
    var harga = parseFloat(opt.getAttribute('data-harga')) || 0;
    var qty = parseInt(document.getElementById('qty').value) || 0;
    var total = harga * qty;
    document.getElementById('total_display').value = 'Rp ' + total.toLocaleString('id-ID');
    if (!pembayaranDiubah) document.getElementById('jumlah_dibayar').value = total;
}

var inputPembayaran = document.getElementById('jumlah_dibayar');
var pembayaranDiubah = inputPembayaran.value !== '';
inputPembayaran.addEventListener('input', function () { pembayaranDiubah = true; });
updateHarga();
</script>

<?php include "../includes/footer.php"; ?>
