-- Jalankan satu kali pada database bengkel_db untuk mengaktifkan pencatatan hutang.
CREATE TABLE IF NOT EXISTS piutang (
    id_piutang INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    tanggal DATE NOT NULL,
    jenis ENUM('servis','sparepart','lainnya') NOT NULL DEFAULT 'lainnya',
    keterangan VARCHAR(255) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL DEFAULT 0,
    id_user INT NOT NULL,
    INDEX idx_piutang_pelanggan (id_pelanggan),
    INDEX idx_piutang_tanggal (tanggal),
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
