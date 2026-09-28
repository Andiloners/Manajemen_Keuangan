<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	header("Location: index.php");
	exit;
}

$linkedDebt = mysqli_query($conn,
	"SELECT id_piutang FROM piutang WHERE sumber_jenis='servis' AND sumber_id=$id LIMIT 1"
);
if ($linkedDebt && mysqli_num_rows($linkedDebt) > 0) {
	header("Location: index.php?piutang_terkait=1");
	exit;
}

mysqli_begin_transaction($conn);
try {
	// Jaga riwayat kas: hapus servis tidak boleh menghapus uang yang sudah diterima.
	mysqli_query($conn,
		"UPDATE pemasukan
		 SET referensi_id=NULL, keterangan=LEFT(CONCAT(COALESCE(keterangan, ''), ' (transaksi servis dihapus)'), 255)
		 WHERE jenis='servis' AND referensi_id=$id"
	);
	mysqli_query($conn, "DELETE FROM servis WHERE id_servis=$id");
	mysqli_commit($conn);
} catch (Throwable $e) {
	mysqli_rollback($conn);
	header("Location: index.php?gagal_hapus=1");
	exit;
}

header("Location: index.php");
exit;
