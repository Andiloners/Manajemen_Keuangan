<?php
include "../config/database.php";
include "../includes/auth.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$linkedDebt = mysqli_query($conn,
    "SELECT id_piutang FROM piutang WHERE sumber_jenis='penjualan_sparepart' AND sumber_id=$id LIMIT 1"
);
if ($linkedDebt && mysqli_num_rows($linkedDebt) > 0) {
    header("Location: index.php?piutang_terkait=1");
    exit;
}

mysqli_begin_transaction($conn);
try {
    // Kembalikan stok master sparepart.
    $details = mysqli_query($conn, "SELECT * FROM penjualan_detail WHERE id_penjualan=$id");
    while ($d = mysqli_fetch_assoc($details)) {
        mysqli_query($conn,
            "UPDATE sparepart SET stok = stok + {$d['qty']} WHERE id_sparepart = {$d['id_sparepart']}"
        );
    }

    // Penerimaan yang sudah masuk kas tetap disimpan sebagai jejak keuangan.
    mysqli_query($conn,
        "UPDATE pemasukan
         SET referensi_id=NULL, keterangan=LEFT(CONCAT(COALESCE(keterangan, ''), ' (transaksi penjualan dihapus)'), 255)
         WHERE jenis='penjualan_sparepart' AND referensi_id=$id"
    );
    mysqli_query($conn, "DELETE FROM penjualan WHERE id_penjualan=$id");
    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    header("Location: index.php?gagal_hapus=1");
    exit;
}

header("Location: index.php");
exit;
