<?php
session_start();
include "config/database.php";

if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $plainPassword = $_POST['password'];

    $query = mysqli_query($conn,
        "SELECT * FROM users WHERE username='$username'"
    );

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $hashedPassword = $data['password'];
        $authenticated = false;
        $needsRehash = false;

        if (password_verify($plainPassword, $hashedPassword)) {
            $authenticated = true;
        } elseif (strlen($hashedPassword) === 32 && md5($plainPassword) === $hashedPassword) {
            $authenticated = true;
            $needsRehash = true;
        }

        if ($authenticated) {
            if ($needsRehash) {
                $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
                $newHashEsc = mysqli_real_escape_string($conn, $newHash);
                $userId = (int)$data['id'];
                mysqli_query($conn, "UPDATE users SET password='$newHashEsc' WHERE id=$userId");
            }
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['level'] = $data['level'];
            $_SESSION['foto'] = $data['foto'] ?? null;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Keuangan dani speedshop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="assets/img/dss.jpeg">
</head>
<body class="login-body">
<div class="login-wrapper">
    <!-- Panel Kiri: Branding -->
    <div class="login-brand">
        <div class="login-brand-content">
            <img src="assets/img/dss.jpeg" alt="Logo Keuangan dani speedshop" class="login-logo">
            <h1>Keuangan dani speedshop</h1>
            <p class="login-tagline">Sistem Informasi Administrasi &amp; Manajemen Keuangan Dani Speed Shop</p>
            <ul class="login-features">
                <li>
                    <span class="feature-icon">&#128663;</span>
                    Kelola data kendaraan &amp; servis
                </li>
                <li>
                    <span class="feature-icon">&#128295;</span>
                    Stok sparepart otomatis
                </li>
                <li>
                    <span class="feature-icon">&#128176;</span>
                    Laporan keuangan real-time
                </li>
            </ul>
        </div>
        <div class="login-brand-footer">
            <p>&copy; <?= date('Y') ?> Keuangan dani speedshop. All rights reserved.</p>
        </div>
    </div>

    <!-- Panel Kanan: Form Login -->
    <div class="login-form-panel">
        <div class="login-box">
            <div class="login-box-header">
                <img src="assets/img/dss.jpeg" alt="Logo" class="login-logo-sm">
                <h2>Selamat Datang</h2>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger-login">
                <span>&#9888;</span> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">&#128100;</span>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">&#128274;</span>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-login">
                    Masuk ke Sistem
                </button>
            </form>

        </div>
    </div>
</div>
</body>
</html>
