CREATE DATABASE IF NOT EXISTS bengkel_db;
USE bengkel_db;

-- Data Pengguna
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    level ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    foto VARCHAR(255) NULL
);

-- Data Pelanggan
CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20),
    alamat TEXT
);

-- Data Kendaraan
CREATE TABLE IF NOT EXISTS kendaraan (
    id_kendaraan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    no_polisi VARCHAR(20) NOT NULL,
    merk VARCHAR(50) NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE
);

-- Data Sparepart
CREATE TABLE IF NOT EXISTS sparepart (
    id_sparepart INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    merk_kendaraan ENUM('Suzuki','Honda','Yamaha','Kawasaki') NOT NULL DEFAULT 'Honda',
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0
);

-- Data Servis
CREATE TABLE IF NOT EXISTS servis (
    id_servis INT AUTO_INCREMENT PRIMARY KEY,
    id_kendaraan INT NOT NULL,
    tanggal DATE NOT NULL,
    jenis_servis VARCHAR(100) NOT NULL,
    biaya_jasa DECIMAL(12,2) NOT NULL DEFAULT 0,
    keterangan TEXT,
    FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id_kendaraan) ON DELETE CASCADE
);

-- Transaksi Penjualan Sparepart
CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    id_pelanggan INT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS penjualan_detail (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_sparepart INT NOT NULL,
    qty INT NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    FOREIGN KEY (id_sparepart) REFERENCES sparepart(id_sparepart)
);

-- Data Pemasukan
CREATE TABLE IF NOT EXISTS pemasukan (
    id_pemasukan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis ENUM('servis','penjualan_sparepart','pembayaran_piutang','lainnya') NOT NULL,
    keterangan VARCHAR(255),
    jumlah DECIMAL(12,2) NOT NULL,
    referensi_id INT NULL,
    id_pembayaran_piutang INT NULL UNIQUE
);

-- Data Pengeluaran
CREATE TABLE IF NOT EXISTS pengeluaran (
    id_pengeluaran INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis ENUM('pembelian_sparepart','operasional','lainnya') NOT NULL,
    keterangan VARCHAR(255),
    jumlah DECIMAL(12,2) NOT NULL
);

-- Data Hutang Pelanggan dan Pembayaran
CREATE TABLE IF NOT EXISTS piutang (
    id_piutang INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    tanggal DATE NOT NULL,
    jenis ENUM('servis','sparepart','lainnya') NOT NULL DEFAULT 'lainnya',
    keterangan VARCHAR(255) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL DEFAULT 0,
    id_user INT NOT NULL,
    sumber_jenis ENUM('servis','penjualan_sparepart','manual') NOT NULL DEFAULT 'manual',
    sumber_id INT NULL,
    INDEX idx_piutang_pelanggan (id_pelanggan),
    INDEX idx_piutang_tanggal (tanggal),
    UNIQUE KEY uq_piutang_sumber (sumber_jenis, sumber_id),
    CONSTRAINT fk_piutang_pelanggan FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan) ON DELETE RESTRICT,
    CONSTRAINT fk_piutang_user FOREIGN KEY (id_user)
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembayaran_piutang (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_piutang INT NOT NULL,
    tanggal DATE NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    keterangan VARCHAR(255) NULL,
    id_user INT NOT NULL,
    INDEX idx_pembayaran_piutang (id_piutang),
    INDEX idx_pembayaran_tanggal (tanggal),
    CONSTRAINT fk_pembayaran_piutang FOREIGN KEY (id_piutang)
        REFERENCES piutang(id_piutang) ON DELETE CASCADE,
    CONSTRAINT fk_pembayaran_user FOREIGN KEY (id_user)
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tidak ada akun bawaan di file SQL ini.
-- Buat akun pengguna melalui aplikasi setelah database diimpor.
