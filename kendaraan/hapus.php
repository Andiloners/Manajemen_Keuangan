<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM kendaraan WHERE id_kendaraan=$id");
header("Location: index.php");
exit;
