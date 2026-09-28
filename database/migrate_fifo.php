<?php
include __DIR__ . "/../config/database.php";

echo "Mengisi struktur database FIFO...\n";

// 1. Table sparepart_stok_fifo
$sql1 = "CREATE TABLE IF NOT EXISTS sparepart_stok_fifo (
    id_batch INT AUTO_INCREMENT PRIMARY KEY,
    id_sparepart INT NOT NULL,
    tanggal_masuk DATE NOT NULL,
    stok_masuk INT NOT NULL,
    stok_sisa INT NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
    id_pengeluaran INT NULL,
    id_user INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sparepart) REFERENCES sparepart(id_sparepart) ON DELETE CASCADE,
    FOREIGN KEY (id_pengeluaran) REFERENCES pengeluaran(id_pengeluaran) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql1)) {
    echo "✔ Tabel sparepart_stok_fifo berhasil dibuat/dikonfirmasi.\n";
} else {
    echo "❌ Gagal membuat sparepart_stok_fifo: " . mysqli_error($conn) . "\n";
}

// 2. Table penjualan_fifo_detail
$sql2 = "CREATE TABLE IF NOT EXISTS penjualan_fifo_detail (
    id_fifo_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_detail INT NOT NULL,
    id_batch INT NOT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL,
    subtotal_hpp DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_detail) REFERENCES penjualan_detail(id_detail) ON DELETE CASCADE,
    FOREIGN KEY (id_batch) REFERENCES sparepart_stok_fifo(id_batch) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql2)) {
    echo "✔ Tabel penjualan_fifo_detail berhasil dibuat/dikonfirmasi.\n";
} else {
    echo "❌ Gagal membuat penjualan_fifo_detail: " . mysqli_error($conn) . "\n";
}

// 3. Migrasi Stok Terpasang
$sql3 = "INSERT INTO sparepart_stok_fifo (id_sparepart, tanggal_masuk, stok_masuk, stok_sisa, harga_beli, id_user)
SELECT 
    s.id_sparepart, 
    CURDATE() AS tanggal_masuk, 
    s.stok AS stok_masuk, 
    s.stok AS stok_sisa, 
    ROUND(s.harga * 0.8, 2) AS harga_beli,
    s.id_user
FROM sparepart s
WHERE s.stok > 0 
  AND NOT EXISTS (
      SELECT 1 FROM sparepart_stok_fifo f WHERE f.id_sparepart = s.id_sparepart
  )";

if (mysqli_query($conn, $sql3)) {
    $rows = mysqli_affected_rows($conn);
    echo "✔ Initial batch FIFO berhasil dibuat untuk $rows jenis sparepart.\n";
} else {
    echo "❌ Gagal membuat initial batch: " . mysqli_error($conn) . "\n";
}

echo "Migrasi Selesai.\n";
