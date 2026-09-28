-- Jalankan satu kali setelah migration_piutang.sql pada database bengkel_db.
-- Untuk database yang sudah memiliki kedua tabel piutang.
-- Tidak mengubah atau mengisi ulang pemasukan/pembayaran historis secara otomatis.
-- Pemasukan dari pembayaran ditautkan langsung ke satu catatan pembayaran.
ALTER TABLE pemasukan
    MODIFY jenis ENUM('servis','penjualan_sparepart','pembayaran_piutang','lainnya') NOT NULL,
    ADD COLUMN id_pembayaran_piutang INT NULL UNIQUE AFTER referensi_id;

-- Tautkan piutang baru dari transaksi servis / penjualan ke sumbernya.
ALTER TABLE piutang
    ADD COLUMN sumber_jenis ENUM('servis','penjualan_sparepart','manual') NOT NULL DEFAULT 'manual' AFTER id_user,
    ADD COLUMN sumber_id INT NULL AFTER sumber_jenis,
    ADD UNIQUE KEY uq_piutang_sumber (sumber_jenis, sumber_id);
