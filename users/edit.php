<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/upload.php";
requireAdmin();

$pageTitle = "Edit Pengguna";
$id = (int)$_GET['id'];
$base = getBaseUrl();

if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));
    $upload = uploadProfilePhoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], $id, $data['foto'] ?? null);

    if (!$upload['success']) {
        $error = $upload['error'];
    } else {
        $sql = "UPDATE users SET username='$username', level='$level'";
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $passwordEsc = mysqli_real_escape_string($conn, $password);
            $sql .= ", password='$passwordEsc'";
        }
        if ($upload['filename']) {
            $fotoEsc = mysqli_real_escape_string($conn, $upload['filename']);
            $sql .= ", foto='$fotoEsc'";
        }
        $sql .= " WHERE id=$id";
        mysqli_query($conn, $sql);

        if ($id == $_SESSION['id']) {
            $_SESSION['username'] = $username;
            $_SESSION['level'] = $level;
            refreshSessionFoto($conn, $id);
        }

        header("Location: index.php");
        exit;
    }
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));
$fotoUrl = getProfilePhotoUrl($data['foto'] ?? null, $base);

include "../includes/header.php";
?>

<?php if (isset($error)): ?>
<div class="alert alert-warning"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div style="margin-bottom:16px;">
        <img src="<?= $fotoUrl ?>" alt="Foto Profil" class="profile-avatar-md" id="preview_foto">
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Foto Profil</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" onchange="previewImage(this)">
            <small style="color:#666;">Kosongkan jika tidak diubah.</small>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        </div>
        <div class="form-group">
            <label>Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" name="password">
        </div>
        <div class="form-group">
            <label>Level Akses</label>
            <select name="level" required <?= $data['id'] == $_SESSION['id'] ? 'disabled' : '' ?>>
                <option value="admin" <?= $data['level'] === 'admin' ? 'selected' : '' ?>>Admin — akses penuh + kelola pengguna</option>
                <option value="kasir" <?= $data['level'] === 'kasir' ? 'selected' : '' ?>>Kasir — hanya transaksi</option>
            </select>
            <?php if ($data['id'] == $_SESSION['id']): ?>
            <input type="hidden" name="level" value="<?= htmlspecialchars($data['level']) ?>">
            <small style="color:#666;">Level akun sendiri tidak dapat diubah.</small>
            <?php endif; ?>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-warning">Batal</a>
    </form>
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

<?php include "../includes/footer.php"; ?>
