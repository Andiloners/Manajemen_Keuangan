<?php
$base = getBaseUrl();
$current = basename($_SERVER['PHP_SELF'], '.php');
$dir = basename(dirname($_SERVER['PHP_SELF']));

if (!function_exists('getProfilePhotoUrl')) {
    include_once __DIR__ . '/upload.php';
}
$sidebarFoto = getProfilePhotoUrl($_SESSION['foto'] ?? null, $base);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Keuangan dani speedshop' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=20260928-mobile2">
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>assets/img/logo.svg">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <button type="button" class="sidebar-close-btn" aria-label="Tutup menu" onclick="closeSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="sidebar-brand">
            <a href="<?= $base ?>profil.php" class="profile-sidebar-link">
                <img src="<?= $sidebarFoto ?>" alt="Profil" class="profile-avatar">
                <div>
                    <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    <small><?= ucfirst($_SESSION['level']) ?></small>
                </div>
            </a>
            <div class="sidebar-app-title">
                <img src="<?= $base ?>assets/img/logo.svg" alt="Logo" class="sidebar-logo">
                <h2>Keuangan dani speedshop</h2>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= $base ?>dashboard.php" class="<?= $current === 'dashboard' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
            <a href="<?= $base ?>profil.php" class="<?= $current === 'profil' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-person-circle"></i><span>Profil Saya</span></a>

            <span class="nav-label">Master Data</span>
            <a href="<?= $base ?>pelanggan/index.php" class="<?= ($dir === 'pelanggan' && $current === 'index') || $dir === 'piutang' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-wallet2"></i><span>Hutang Pelanggan</span></a>
            <a href="<?= $base ?>pelanggan/master.php" class="<?= $dir === 'pelanggan' && $current !== 'index' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-people"></i><span>Master Pelanggan</span></a>
            <a href="<?= $base ?>kendaraan/index.php" class="<?= $dir === 'kendaraan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-car-front"></i><span>Data Kendaraan</span></a>
            <a href="<?= $base ?>sparepart/index.php" class="<?= $dir === 'sparepart' && $current === 'index' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-gear"></i><span>Data Sparepart</span></a>
            <a href="<?= $base ?>sparepart/fifo.php" class="<?= $dir === 'sparepart' && $current === 'fifo' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-boxes"></i><span>Kartu Stok FIFO</span></a>
            <?php if ($_SESSION['level'] === 'admin'): ?>
            <a href="<?= $base ?>users/index.php" class="<?= $dir === 'users' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-person-gear"></i><span>Kelola Pengguna</span></a>
            <?php endif; ?>

            <span class="nav-label">Transaksi</span>
            <a href="<?= $base ?>servis/index.php" class="<?= $dir === 'servis' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-wrench-adjustable-circle"></i><span>Servis Kendaraan</span></a>
            <a href="<?= $base ?>penjualan/index.php" class="<?= $dir === 'penjualan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-cart3"></i><span>Penjualan Sparepart</span></a>

            <?php if ($_SESSION['level'] === 'admin'): ?>
            <span class="nav-label">Keuangan</span>
            <a href="<?= $base ?>laporan/keuangan.php" class="<?= $dir === 'laporan' && $current === 'keuangan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-cash-coin"></i><span>Kas &amp; Keuangan</span></a>
            <?php endif; ?>

            <span class="nav-label">Laporan</span>
            <a href="<?= $base ?>laporan/pelanggan.php" class="<?= $dir === 'laporan' && $current === 'pelanggan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-person-lines-fill"></i><span>Info Pelanggan</span></a>
            <a href="<?= $base ?>laporan/kendaraan.php" class="<?= $dir === 'laporan' && $current === 'kendaraan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-car-front-fill"></i><span>Info Kendaraan</span></a>
            <a href="<?= $base ?>laporan/stok.php" class="<?= $dir === 'laporan' && $current === 'stok' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-box-seam"></i><span>Stok Sparepart</span></a>
            <a href="<?= $base ?>laporan/servis.php" class="<?= $dir === 'laporan' && $current === 'servis' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-clipboard2-check"></i><span>Transaksi Servis</span></a>
            <a href="<?= $base ?>laporan/penjualan.php" class="<?= $dir === 'laporan' && $current === 'penjualan' ? 'active' : '' ?>" onclick="closeSidebarOnLink()"><i class="bi bi-receipt"></i><span>Transaksi Penjualan</span></a>
        </nav>
        <a href="<?= $base ?>logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-left"></i><span>Keluar</span></a>
    </aside>
    <button type="button" class="sidebar-overlay" aria-label="Tutup menu" onclick="closeSidebar()"></button>
    <main class="content">
        <div class="content-header">
            <button type="button" class="mobile-menu-btn" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
        </div>
        <div class="content-body">
