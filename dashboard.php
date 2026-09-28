<?php
include "config/database.php";
include "includes/auth.php";
include "includes/upload.php";

$pageTitle = "Dashboard";
$base = getBaseUrl();
$img = $base . 'assets/img/';

$totalPelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM pelanggan"))['jml'] ?? 0;
$totalKendaraan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM kendaraan"))['jml'] ?? 0;
$totalSparepart = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM sparepart"))['jml'] ?? 0;
$totalServis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM servis"))['jml'] ?? 0;

$bulanIni = date('Y-m');
$pemasukan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pemasukan WHERE DATE_FORMAT(tanggal,'%Y-%m')='$bulanIni'"
))['total'];
$pengeluaran = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(jumlah),0) AS total FROM pengeluaran WHERE DATE_FORMAT(tanggal,'%Y-%m')='$bulanIni'"
))['total'];
$saldo = $pemasukan - $pengeluaran;
$piutangAktif = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(h.jumlah - COALESCE(pb.total_bayar, 0)), 0) AS total
     FROM piutang h
     LEFT JOIN (
         SELECT id_piutang, SUM(jumlah) AS total_bayar
         FROM pembayaran_piutang
         GROUP BY id_piutang
     ) pb ON pb.id_piutang = h.id_piutang
     WHERE h.jumlah > COALESCE(pb.total_bayar, 0)"
))['total'];

$stokRendah = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS jml FROM sparepart WHERE stok <= 5"
))['jml'] ?? 0;

$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalHariIni = $hari[date('w')] . ', ' . date('d') . ' ' . $bulan[(int)date('m')] . ' ' . date('Y');

include "includes/header.php";
?>

<?php if (isset($_GET['akses']) && $_GET['akses'] === 'ditolak'): ?>
<div class="alert alert-warning">Akses ditolak. Hanya <strong>Admin</strong> yang dapat mengelola pengguna.</div>
<?php endif; ?>

<!-- Banner Dashboard -->
<div class="dashboard-hero">
    <img src="<?= $img ?>dashboard-banner-dss.png" alt="Banner Dani Speed Shop" class="dashboard-banner">
    <div class="dashboard-hero-overlay">
        <div class="dashboard-hero-user">
            <img src="<?= getProfilePhotoUrl($_SESSION['foto'] ?? null, $base) ?>" alt="Profil" class="profile-avatar-md">
            <div>
                <p class="hero-greeting">Welcome back,</p>
                <h2><?= htmlspecialchars($_SESSION['username']) ?></h2>
                <span class="badge badge-<?= $_SESSION['level'] ?>"><?= ucfirst($_SESSION['level']) ?></span>
            </div>
        </div>
        <p class="hero-date"><?= $tanggalHariIni ?></p>
    </div>
</div>

<!-- Statistik dengan Gambar -->
<div class="card-grid">
    <div class="stat-card-img blue">
        <img src="<?= $img ?>icons/pelanggan.svg" alt="Pelanggan" class="stat-icon">
        <div>
            <h3>Total Pelanggan</h3>
            <div class="value"><?= $totalPelanggan ?></div>
        </div>
    </div>
    <div class="stat-card-img">
        <img src="<?= $img ?>icons/kendaraan.svg" alt="Kendaraan" class="stat-icon">
        <div>
            <h3>Total Kendaraan</h3>
            <div class="value"><?= $totalKendaraan ?></div>
        </div>
    </div>
    <div class="stat-card-img orange">
        <img src="<?= $img ?>icons/sparepart.svg" alt="Sparepart" class="stat-icon">
        <div>
            <h3>Total Sparepart</h3>
            <div class="value"><?= $totalSparepart ?></div>
            <?php if ($stokRendah > 0): ?>
            <small class="stat-warning"><?= $stokRendah ?> stok rendah</small>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card-img purple">
        <img src="<?= $img ?>icons/servis.svg" alt="Servis" class="stat-icon">
        <div>
            <h3>Total Servis</h3>
            <div class="value"><?= $totalServis ?></div>
        </div>
    </div>
</div>

<div class="card-grid">
    <div class="stat-card-img green">
        <img src="<?= $img ?>icons/pemasukan.svg" alt="Pemasukan" class="stat-icon">
        <div>
            <h3>Pemasukan Bulan Ini</h3>
            <div class="value"><?= formatRupiah($pemasukan) ?></div>
        </div>
    </div>
    <div class="stat-card-img red">
        <img src="<?= $img ?>icons/pengeluaran.svg" alt="Pengeluaran" class="stat-icon">
        <div>
            <h3>Pengeluaran Bulan Ini</h3>
            <div class="value"><?= formatRupiah($pengeluaran) ?></div>
        </div>
    </div>
    <div class="stat-card-img <?= $saldo >= 0 ? 'green' : 'red' ?>">
        <img src="<?= $img ?>icons/saldo.svg" alt="Saldo" class="stat-icon">
        <div>
            <h3>Saldo Bulan Ini</h3>
            <div class="value"><?= formatRupiah($saldo) ?></div>
        </div>
    </div>
    <a href="pelanggan/index.php" class="stat-card-img orange" style="text-decoration:none;color:inherit;">
        <img src="<?= $img ?>icons/pelanggan.svg" alt="Piutang" class="stat-icon">
        <div>
            <h3>Sisa Piutang Pelanggan</h3>
            <div class="value"><?= formatRupiah($piutangAktif) ?></div>
        </div>
    </a>
</div>

<!-- Akses Cepat dengan Gambar -->
<div class="card">
    <h3 class="section-title">Akses Cepat</h3>
    <div class="quick-grid">
        <a href="pelanggan/tambah.php" class="quick-card">
            <img src="<?= $img ?>icons/pelanggan.svg" alt="">
            <span>Tambah Pelanggan</span>
        </a>
        <a href="kendaraan/tambah.php" class="quick-card">
            <img src="<?= $img ?>icons/kendaraan.svg" alt="">
            <span>Tambah Kendaraan</span>
        </a>
        <a href="sparepart/tambah.php" class="quick-card">
            <img src="<?= $img ?>icons/sparepart.svg" alt="">
            <span>Tambah Sparepart</span>
        </a>
        <a href="servis/tambah.php" class="quick-card">
            <img src="<?= $img ?>icons/servis.svg" alt="">
            <span>Input Servis</span>
        </a>
        <a href="penjualan/tambah.php" class="quick-card">
            <img src="<?= $img ?>icons/sparepart.svg" alt="">
            <span>Penjualan Sparepart</span>
        </a>
        <?php if (isAdmin()): ?>
        <a href="laporan/keuangan.php" class="quick-card">
            <img src="<?= $img ?>icons/saldo.svg" alt="">
            <span>Laporan Keuangan</span>
        </a>
        <a href="users/tambah.php?tipe=admin" class="quick-card">
            <img src="<?= $img ?>icons/user.svg" alt="">
            <span>Tambah Admin</span>
        </a>
        <a href="users/tambah.php?tipe=kasir" class="quick-card">
            <img src="<?= $img ?>icons/pelanggan.svg" alt="">
            <span>Tambah Kasir</span>
        </a>
        <a href="users/index.php" class="quick-card">
            <img src="<?= $img ?>icons/user.svg" alt="">
            <span>Kelola Pengguna</span>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card welcome-card">
    <img src="<?= $img ?>icons/servis.svg" alt="Info" class="welcome-illustration">
    <div>
        <h3>Keuangan dani speedshop</h3>
        <p>Kelola data pelanggan, kendaraan, sparepart, transaksi servis, penjualan, dan laporan keuangan Dani Speed Shop dalam satu aplikasi.</p>
        <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
            <a href="profil.php" class="btn btn-primary btn-sm">Kelola Profil</a>
            <a href="laporan/stok.php" class="btn btn-warning btn-sm">Cek Stok</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
