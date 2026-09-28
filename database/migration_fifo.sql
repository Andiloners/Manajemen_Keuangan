-- Migration Script: Algoritma FIFO Sparepart & Detail HPP
USE bengkel_db;

-- 1. Tabel Batch Stok FIFO (Pemasukan Batch Sparepart)
CREATE TABLE IF NOT EXISTS sparepart_stok_fifo (
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
);

-- 2. Tabel Tracking Konsumsi Batch FIFO per Penjualan
CREATE TABLE IF NOT EXISTS penjualan_fifo_detail (
    id_fifo_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_detail INT NOT NULL,
    id_batch INT NOT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL,
    subtotal_hpp DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_detail) REFERENCES penjualan_detail(id_detail) ON DELETE CASCADE,
    FOREIGN KEY (id_batch) REFERENCES sparepart_stok_fifo(id_batch) ON DELETE CASCADE
);

-- 3. Migrasi Stok Lama: Buat Initial Batch untuk Sparepart yang Sudah Ada Stoknya (jika belum ada batch)
INSERT INTO sparepart_stok_fifo (id_sparepart, tanggal_masuk, stok_masuk, stok_sisa, harga_beli, id_user)
SELECT 
    s.id_sparepart, 
    CURDATE() AS tanggal_masuk, 
    s.stok AS stok_masuk, 
    s.stok AS stok_sisa, 
    ROUND(s.harga * 0.8, 2) AS harga_beli, -- Asumsi HPP awal 80% dari harga jual jika belum ada record
    s.id_user
FROM sparepart s
WHERE s.stok > 0 
  AND NOT EXISTS (
      SELECT 1 FROM sparepart_stok_fifo f WHERE f.id_sparepart = s.id_sparepart
  );
