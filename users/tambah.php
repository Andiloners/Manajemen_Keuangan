<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/upload.php";
requireAdmin();

$tipe = $_GET['tipe'] ?? $_POST['level'] ?? 'kasir';
if (!in_array($tipe, ['admin', 'kasir'], true)) {
    $tipe = 'kasir';
}

$labelTipe = $tipe === 'admin' ? 'Admin' : 'Kasir (User)';
$pageTitle = "Tambah " . $labelTipe;

if (isset($_POST['simpan'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    if (!in_array($level, ['admin', 'kasir'], true)) {
        $error = "Level akses tidak valid.";
    } elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $password2) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $passHashEsc = mysqli_real_escape_string($conn, $passHash);
            mysqli_query($conn, "INSERT INTO users (username, password, level) VALUES ('$username','$passHashEsc','$level')");
            $userId = mysqli_insert_id($conn);

            $upload = uploadProfilePhoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], $userId);
            if (!$upload['success']) {
                mysqli_query($conn, "DELETE FROM users WHERE id=$userId");
                $error = $upload['error'];
            } else {
                if ($upload['filename']) {
                    $fotoEsc = mysqli_real_escape_string($conn, $upload['filename']);
                    mysqli_query($conn, "UPDATE users SET foto='$fotoEsc' WHERE id=$userId");
                }
                header("Location: index.php?sukses=1&tipe=$level");
                exit;
            }
        }
    }
    $tipe = $level ?? $tipe;
}

include "../includes/header.php";
?>

<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= $error ?></div>
<?php endif; ?>

<div class="card" style="max-width:560px;">
    <div class="alert <?= $tipe === 'admin' ? 'alert-warning' : 'alert-info' ?>" style="margin-bottom:20px;">
        <?php if ($tipe === 'admin'): ?>
            <strong>Admin</strong> — Akses penuh: kelola data, transaksi, keuangan, laporan, dan pengguna.
        <?php else: ?>
            <strong>Kasir (User)</strong> — Hanya dapat menginput transaksi dan melihat data master operasional. Tidak bisa melihat atau mengelola laporan pemasukan, pengeluaran, dan keuangan. Tidak bisa kelola pengguna.
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="level" value="<?= htmlspecialchars($tipe) ?>">

        <div class="form-group">
            <label>Level Akses</label>
            <input type="text" value="<?= $labelTipe ?>" disabled>
        </div>
        <div class="form-group">
            <label>Foto Profil</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
            <small style="color:#666;">Opsional. JPG, PNG, WEBP. Maks. 2 MB.</small>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required minlength="3" placeholder="Min. 3 karakter">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required minlength="6" placeholder="Min. 6 karakter">
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="password2" required minlength="6" placeholder="Ulangi password">
        </div>
        <button type="submit" name="simpan" class="btn btn-success">Simpan <?= $labelTipe ?></button>
        <a href="index.php" class="btn btn-warning">Batal</a>
        <?php if ($tipe === 'admin'): ?>
        <a href="tambah.php?tipe=kasir" class="btn btn-primary">Tambah Kasir</a>
        <?php else: ?>
        <a href="tambah.php?tipe=admin" class="btn btn-danger">Tambah Admin</a>
        <?php endif; ?>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
