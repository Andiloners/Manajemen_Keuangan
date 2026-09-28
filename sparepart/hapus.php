<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM sparepart WHERE id_sparepart=$id");
header("Location: index.php");
exit;
