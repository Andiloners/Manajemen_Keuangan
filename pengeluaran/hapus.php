<?php
include "../config/database.php";
include "../includes/auth.php";

if (isKasir()) {
    header("Location: " . getBaseUrl() . "dashboard.php?akses=ditolak");
    exit;
}

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM pengeluaran WHERE id_pengeluaran=$id");
header("Location: index.php");
exit;
