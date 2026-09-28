<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)($_GET['id'] ?? $_POST['id_piutang'] ?? 0);
$pageTitle = "Catat Pembayaran Hutang";
$error = '';

$stmt = mysqli_prepare($conn,
    "SELECT h.id_piutang, h.id_pelanggan, h.tanggal, h.jenis, h.keterangan, h.jumlah,
            p.nama, COALESCE(SUM(pb.jumlah), 0) AS terbayar
     FROM piutang h
     JOIN pelanggan p ON p.id_pelanggan = h.id_pelanggan
     LEFT JOIN pembayaran_piutang pb ON pb.id_piutang = h.id_piutang
     WHERE h.id_piutang = ?
    GROUP BY h.id_piutang, h.id_pelanggan, h.tanggal, h.jenis, h.keterangan, h.jumlah, p.nama"
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$hutang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$hutang) {
    header("Location: ../pelanggan/index.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $tanggal = $_POST['tanggal'] ?? '';
    $jumlah = round((float)($_POST['jumlah'] ?? 0), 2);
    $keterangan = trim($_POST['keterangan'] ?? '');

    $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)
        && checkdate((int)substr($tanggal, 5, 2), (int)substr($tanggal, 8, 2), (int)substr($tanggal, 0, 4));
    if (!$tanggalValid || $jumlah <= 0 || strlen($keterangan) > 255) {
        $error = 'Tanggal dan jumlah pembayaran harus valid.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $lock = mysqli_prepare($conn, "SELECT jumlah FROM piutang WHERE id_piutang = ? FOR UPDATE");
            mysqli_stmt_bind_param($lock, 'i', $id);
            mysqli_stmt_execute($lock);
            $lockedHutang = mysqli_fetch_assoc(mysqli_stmt_get_result($lock));
            if (!$lockedHutang) {
                throw new DomainException('Data hutang tidak ditemukan.');
            }

            $paid = mysqli_prepare($conn, "SELECT COALESCE(SUM(jumlah), 0) AS total FROM pembayaran_piutang WHERE id_piutang = ?");
            mysqli_stmt_bind_param($paid, 'i', $id);
            mysqli_stmt_execute($paid);
            $totalTerbayar = (float)mysqli_fetch_assoc(mysqli_stmt_get_result($paid))['total'];
            $sisa = (float)$lockedHutang['jumlah'] - $totalTerbayar;
            if ($jumlah > $sisa + 0.00001) {
                throw new DomainException('Jumlah pembayaran melebihi sisa hutang.');
            }

            $insert = mysqli_prepare($conn,
                "INSERT INTO pembayaran_piutang (id_piutang, tanggal, jumlah, keterangan, id_user)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $id_user = (int)$_SESSION['id'];
            mysqli_stmt_bind_param($insert, 'isdsi', $id, $tanggal, $jumlah, $keterangan, $id_user);
            if (!mysqli_stmt_execute($insert)) {
                throw new RuntimeException('Pembayaran tidak dapat disimpan.');
            }
            $id_pembayaran = mysqli_insert_id($conn);

            $jenisPemasukan = 'pembayaran_piutang';
            $keteranganPemasukan = 'Penerimaan piutang #' . $id . ' - ' . $hutang['nama'];
            $income = mysqli_prepare($conn,
                "INSERT INTO pemasukan
                    (tanggal, id_user, jenis, keterangan, jumlah, referensi_id, id_pembayaran_piutang)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $income,
                'sissdii',
                $tanggal,
                $id_user,
                $jenisPemasukan,
                $keteranganPemasukan,
                $jumlah,
                $id_pembayaran,
                $id_pembayaran
            );
            if (!mysqli_stmt_execute($income)) {
                throw new RuntimeException('Pencatatan pemasukan gagal; pembayaran dibatalkan. Silakan coba lagi.');
            }

            mysqli_commit($conn);
            header("Location: ../pelanggan/index.php");
            exit;
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = $e instanceof DomainException
                ? $e->getMessage()
                : 'Pembayaran gagal dicatat ke pemasukan. Transaksi dibatalkan; silakan coba lagi.';
        }
    }
}

$sisa = max(0, (float)$hutang['jumlah'] - (float)$hutang['terbayar']);
$riwayat = mysqli_query($conn,
    "SELECT pb.tanggal, pb.jumlah, pb.keterangan, u.username,
            pm.id_pemasukan
     FROM pembayaran_piutang pb
     LEFT JOIN users u ON u.id = pb.id_user
     LEFT JOIN pemasukan pm ON pm.id_pembayaran_piutang = pb.id_pembayaran
     WHERE pb.id_piutang = $id
     ORDER BY pb.tanggal DESC, pb.id_pembayaran DESC"
);
include "../includes/header.php";
?>

<?php if ($error): ?><div class="alert alert-warning"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
    <p><strong>Pelanggan:</strong> <?= htmlspecialchars($hutang['nama']) ?></p>
    <p><strong>Alasan:</strong> <?= htmlspecialchars(ucfirst($hutang['jenis'])) ?> — <?= nl2br(htmlspecialchars($hutang['keterangan'])) ?></p>
    <p><strong>Total hutang:</strong> <?= formatRupiah($hutang['jumlah']) ?></p>
    <p><strong>Sudah dibayar:</strong> <?= formatRupiah($hutang['terbayar']) ?></p>
    <p><strong>Sisa hutang:</strong> <span style="color: #dc2626; font-weight: 800;"><?= formatRupiah($sisa) ?></span></p>

    <?php if ($sisa > 0): ?>
    <hr>
    <form method="POST">
        <input type="hidden" name="id_piutang" value="<?= (int)$id ?>">
        <div class="form-group">
            <label>Tanggal Pembayaran</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group">
            <label>Jumlah Dibayar (maks. <?= formatRupiah($sisa) ?>)</label>
            <input type="number" name="jumlah" min="0.01" max="<?= htmlspecialchars((string)$sisa) ?>" step="0.01" value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Catatan Pembayaran (opsional)</label>
            <textarea name="keterangan" maxlength="255"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan Pembayaran</button>
        <a href="../pelanggan/index.php" class="btn btn-warning">Batal</a>
    </form>
    <?php else: ?>
        <span class="badge bg-success">Hutang sudah lunas</span>
        <a href="../pelanggan/index.php?status=semua" class="btn btn-warning">Kembali</a>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <h3 style="margin:0;">Riwayat Pembayaran dan Pemasukan Kas</h3>
        <a href="riwayat.php?id_pelanggan=<?= (int)$hutang['id_pelanggan'] ?>" class="btn btn-primary btn-sm">Cetak Riwayat Pelanggan Ini</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Catatan</th><th>Dicatat oleh</th><th>Status Kas</th></tr></thead>
            <tbody>
                <?php if (!$riwayat || mysqli_num_rows($riwayat) === 0): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada pembayaran.</td></tr>
                <?php else: ?>
                    <?php while ($pembayaran = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($pembayaran['tanggal']))) ?></td>
                        <td><?= formatRupiah($pembayaran['jumlah']) ?></td>
                        <td><?= htmlspecialchars($pembayaran['keterangan'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($pembayaran['username'] ?? '-') ?></td>
                        <td><?= $pembayaran['id_pemasukan'] ? 'Sudah masuk pemasukan' : 'Belum terhubung ke pemasukan' ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
