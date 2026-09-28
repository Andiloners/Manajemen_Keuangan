<?php

function getProfilePhotoPath($filename = '') {
    return __DIR__ . '/../uploads/profil/' . $filename;
}

function getProfilePhotoUrl($foto, $base = '') {
    if ($foto && file_exists(getProfilePhotoPath($foto))) {
        return $base . 'uploads/profil/' . rawurlencode($foto);
    }
    return $base . 'assets/img/default-avatar.svg';
}

function deleteProfilePhoto($foto) {
    if ($foto && file_exists(getProfilePhotoPath($foto))) {
        unlink(getProfilePhotoPath($foto));
    }
}

function uploadProfilePhoto($file, $userId, $oldFoto = null) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => $oldFoto];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal mengunggah file.'];
    }

    $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        return ['success' => false, 'error' => 'Format harus JPG, PNG, atau WEBP.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Ukuran maksimal 2 MB.'];
    }

    if ($mime === 'image/png') {
        $ext = 'png';
    } elseif ($mime === 'image/webp') {
        $ext = 'webp';
    } else {
        $ext = 'jpg';
    }

    $dir = getProfilePhotoPath();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = 'profil_' . $userId . '_' . time() . '.' . $ext;
    $dest = getProfilePhotoPath($filename);

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file.'];
    }

    if ($oldFoto && $oldFoto !== $filename) {
        deleteProfilePhoto($oldFoto);
    }

    return ['success' => true, 'filename' => $filename];
}

function refreshSessionFoto($conn, $userId) {
    $id = (int)$userId;
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM users WHERE id=$id"));
    $_SESSION['foto'] = $row['foto'] ?? null;
}
