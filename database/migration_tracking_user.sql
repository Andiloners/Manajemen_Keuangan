-- Menambah kolom id_user untuk tracking siapa yang upload data

-- Tambah kolom id_user ke tabel penjualan
ALTER TABLE penjualan ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE penjualan ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel pemasukan
ALTER TABLE pemasukan ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE pemasukan ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel pengeluaran
ALTER TABLE pengeluaran ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE pengeluaran ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel servis
ALTER TABLE servis ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE servis ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel kendaraan
ALTER TABLE kendaraan ADD COLUMN id_user INT AFTER id_pelanggan;
ALTER TABLE kendaraan ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel pelanggan
ALTER TABLE pelanggan ADD COLUMN id_user INT AFTER id_pelanggan;
ALTER TABLE pelanggan ADD FOREIGN KEY (id_user) REFERENCES users(id);

-- Tambah kolom id_user ke tabel sparepart
ALTER TABLE sparepart ADD COLUMN id_user INT AFTER id_sparepart;
ALTER TABLE sparepart ADD FOREIGN KEY (id_user) REFERENCES users(id);
