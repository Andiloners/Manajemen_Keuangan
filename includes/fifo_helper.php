<?php
/**
 * Helper Algoritma FIFO (First-In, First-Out) untuk Inventaris Sparepart
 */

/**
 * Menambah batch stok FIFO baru (Pemasukan / Restock / Stok Awal)
 */
function tambahBatchFifo($conn, $id_sparepart, $qty, $harga_beli, $tanggal = null, $id_pengeluaran = null, $id_user = null) {
    $id_sparepart = (int)$id_sparepart;
    $qty = (int)$qty;
    $harga_beli = (float)$harga_beli;
    $tanggal = $tanggal ? mysqli_real_escape_string($conn, $tanggal) : date('Y-m-d');
    $id_pengeluaran_val = $id_pengeluaran ? (int)$id_pengeluaran : 'NULL';
    $id_user_val = $id_user ? (int)$id_user : (isset($_SESSION['id']) ? (int)$_SESSION['id'] : 'NULL');

    if ($qty <= 0) {
        return false;
    }

    $query = "INSERT INTO sparepart_stok_fifo (id_sparepart, tanggal_masuk, stok_masuk, stok_sisa, harga_beli, id_pengeluaran, id_user)
              VALUES ($id_sparepart, '$tanggal', $qty, $qty, $harga_beli, $id_pengeluaran_val, $id_user_val)";

    if (mysqli_query($conn, $query)) {
        $id_batch = mysqli_insert_id($conn);
        syncStokMasterSparepart($conn, $id_sparepart);
        return $id_batch;
    }

    return false;
}

/**
 * Memproses pengeluaran stok berdasarkan Algoritma FIFO
 * Menyedot stok dari batch terlama (tanggal_masuk ASC, id_batch ASC)
 */
function prosesKeluarFifo($conn, $id_detail, $id_sparepart, $qty_butuh) {
    $id_detail = (int)$id_detail;
    $id_sparepart = (int)$id_sparepart;
    $qty_butuh = (int)$qty_butuh;

    if ($qty_butuh <= 0) {
        return 0;
    }

    // 1. Cek ketersediaan total stok FIFO
    $checkQ = mysqli_query($conn, "SELECT COALESCE(SUM(stok_sisa),0) AS total_tersedia FROM sparepart_stok_fifo WHERE id_sparepart = $id_sparepart AND stok_sisa > 0");
    $totalTersedia = (int)mysqli_fetch_assoc($checkQ)['total_tersedia'];

    if ($totalTersedia < $qty_butuh) {
        throw new Exception("Stok FIFO tidak mencukupi! Tersedia: $totalTersedia, Dibutuhkan: $qty_butuh");
    }

    // 2. Ambil batch aktif dari yang TERLAMA (First-In)
    $batchesQ = mysqli_query($conn, "SELECT * FROM sparepart_stok_fifo 
                                     WHERE id_sparepart = $id_sparepart AND stok_sisa > 0 
                                     ORDER BY tanggal_masuk ASC, id_batch ASC");

    $sisaDibutuhkan = $qty_butuh;
    $totalHpp = 0;

    while ($batch = mysqli_fetch_assoc($batchesQ)) {
        if ($sisaDibutuhkan <= 0) {
            break;
        }

        $id_batch = (int)$batch['id_batch'];
        $stok_sisa_batch = (int)$batch['stok_sisa'];
        $harga_beli_batch = (float)$batch['harga_beli'];

        // Ambil sebanyak yang bisa diambil dari batch ini
        $diambil = min($sisaDibutuhkan, $stok_sisa_batch);
        $stok_sisa_baru = $stok_sisa_batch - $diambil;
        $subtotal_hpp = $diambil * $harga_beli_batch;
        $totalHpp += $subtotal_hpp;

        // Update sisa stok pada batch ini
        mysqli_query($conn, "UPDATE sparepart_stok_fifo SET stok_sisa = $stok_sisa_baru WHERE id_batch = $id_batch");

        // Simpan log detail konsumsi FIFO
        mysqli_query($conn, "INSERT INTO penjualan_fifo_detail (id_detail, id_batch, qty, harga_beli, subtotal_hpp)
                             VALUES ($id_detail, $id_batch, $diambil, $harga_beli_batch, $subtotal_hpp)");

        $sisaDibutuhkan -= $diambil;
    }

    // 3. Sinkronkan total stok master sparepart
    syncStokMasterSparepart($conn, $id_sparepart);

    return $totalHpp;
}

/**
 * Menyelaraskan stok total pada tabel `sparepart` dengan penjumlahan `stok_sisa` pada `sparepart_stok_fifo`
 */
function syncStokMasterSparepart($conn, $id_sparepart) {
    $id_sparepart = (int)$id_sparepart;
    $q = mysqli_query($conn, "SELECT COALESCE(SUM(stok_sisa), 0) AS total FROM sparepart_stok_fifo WHERE id_sparepart = $id_sparepart");
    $totalStok = (int)mysqli_fetch_assoc($q)['total'];
    mysqli_query($conn, "UPDATE sparepart SET stok = $totalStok WHERE id_sparepart = $id_sparepart");
}

/**
 * Menghitung Total HPP untuk 1 transaksi Penjualan
 */
function getHppPenjualan($conn, $id_penjualan) {
    $id_penjualan = (int)$id_penjualan;
    $q = mysqli_query($conn, "SELECT COALESCE(SUM(fd.subtotal_hpp), 0) AS total_hpp 
                              FROM penjualan_fifo_detail fd 
                              JOIN penjualan_detail d ON fd.id_detail = d.id_detail 
                              WHERE d.id_penjualan = $id_penjualan");
    return (float)mysqli_fetch_assoc($q)['total_hpp'];
}
