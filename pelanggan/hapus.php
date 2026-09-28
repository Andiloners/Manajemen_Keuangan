<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM pelanggan WHERE id_pelanggan=$id");
header("Location: master.php");
exit;
