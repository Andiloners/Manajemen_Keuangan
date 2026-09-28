<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: " . getBaseUrl() . "login.php");
    exit;
}

function getBaseUrl() {
    $root = '/bengkel-keuangan';
    $dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($dir === $root || $dir === $root . '/') {
        return '';
    }
    $relative = substr($dir, strlen($root) + 1);
    $depth = $relative ? substr_count($relative, '/') + 1 : 1;
    return str_repeat('../', $depth);
}

function isAdmin() {
    return isset($_SESSION['level']) && $_SESSION['level'] === 'admin';
}

function isKasir() {
    return isset($_SESSION['level']) && $_SESSION['level'] === 'kasir';
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
        exit;
    }
}

function requireAdminOnlyFinance() {
    if (isKasir()) {
        header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
        exit;
    }
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getSafeDate($conn, $date, $default) {
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return mysqli_real_escape_string($conn, $default);
    }
    return mysqli_real_escape_string($conn, $date);
}

