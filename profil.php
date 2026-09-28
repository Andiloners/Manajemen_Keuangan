<?php
include "config/database.php";
include "includes/auth.php";
include "includes/upload.php";

$pageTitle = "Profil Saya";
$userId = (int)$_SESSION['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$userId"));
$base = getBaseUrl();
$fotoUrl = getProfilePhotoUrl($user['foto'] ?? null, $base);

if (isset($_POST['simpan'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $oldFoto = $user['foto'];

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' AND id != $userId");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $sql = "UPDATE users SET username='$username'";

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $passwordEsc = mysqli_real_escape_string($conn, $password);
            $sql .= ", password='$passwordEsc'";
        }

        $upload = uploadProfilePhoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], $userId, $oldFoto);
        if (!$upload['success']) {
            $error = $upload['error'];
        } else {
            if ($upload['filename']) {
                $fotoEsc = mysqli_real_escape_string($conn, $upload['filename']);
                $sql .= ", foto='$fotoEsc'";
            }
            $sql .= " WHERE id=$userId";
            mysqli_query($conn, $sql);

            $_SESSION['username'] = $username;
            refreshSessionFoto($conn, $userId);

            header("Location: profil.php?sukses=1");
            exit;
        }
    }
}

if (isset($_GET['sukses'])) {
    $sukses = "Profil berhasil diperbarui.";
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$userId"));
    $fotoUrl = getProfilePhotoUrl($user['foto'] ?? null, $base);
}

include "includes/header.php";
?>

<?php if (isset($sukses)): ?>
<div class="alert alert-info"><?= $sukses ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= $error ?></div>
<?php endif; ?>

<div class="profile-layout">
    <div class="card profile-preview-card">
        <img src="<?= $fotoUrl ?>" alt="Foto Profil" class="profile-avatar-lg" id="preview_foto">
        <h3><?= htmlspecialchars($user['username']) ?></h3>
        <span class="badge badge-<?= $user['level'] ?>"><?= ucfirst($user['level']) ?></span>
    </div>

    <div class="card profile-form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Foto Profil</label>
                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" onchange="previewImage(this)">
                <small style="color:#666;">JPG, PNG, WEBP. Maks. 2 MB.</small>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Level Akses</label>
                <input type="text" value="<?= ucfirst($user['level']) ?>" disabled>
            </div>
            <button type="submit" name="simpan" class="btn btn-success">Simpan Perubahan</button>
            <a href="dashboard.php" class="btn btn-warning">Kembali</a>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_foto').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include "includes/footer.php"; ?>
