<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM pemasukan WHERE id_pemasukan=$id AND jenis='lainnya'");
header("Location: index.php");
exit;
