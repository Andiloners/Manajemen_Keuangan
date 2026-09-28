-- Jalankan file ini jika database sudah ada sebelumnya
USE bengkel_db;

-- Abaikan error jika kolom sudah ada
ALTER TABLE sparepart
ADD COLUMN merk_kendaraan ENUM('Suzuki','Honda','Yamaha','Kawasaki') NOT NULL DEFAULT 'Honda' AFTER nama;
