<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/upload.php";
requireAdmin();

$id = (int)$_GET['id'];
if ($id != $_SESSION['id']) {
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM users WHERE id=$id"));
    if ($user && $user['foto']) {
        deleteProfilePhoto($user['foto']);
    }
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
}
header("Location: index.php");
exit;
