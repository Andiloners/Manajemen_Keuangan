# Dokumentasi Fitur Tracking User Upload

## Ringkasan Perubahan
Sistem telah dimodifikasi untuk mencatat siapa saja (user) yang mengupload atau membuat setiap data transaksi. Informasi ini ditampilkan di:

1. **Halaman Daftar Data** - Menampilkan username yang membuat data
2. **Halaman Laporan** - Menampilkan siapa yang membuat/menginput setiap transaksi

## Data yang Ditambahkan Tracking-nya
✓ Penjualan Sparepart
✓ Pemasukan (manual dan otomatis)
✓ Pengeluaran
✓ Servis
✓ Pelanggan
✓ Kendaraan
✓ Sparepart

## Langkah Implementasi

### 1. Jalankan Migration Database
Buka file: `database/migration_tracking_user.sql`

Jalankan script SQL ini di phpMyAdmin atau MySQL client Anda untuk menambahkan kolom `id_user` ke semua tabel yang relevan:

```sql
-- Tambah kolom id_user untuk tracking siapa yang upload data
ALTER TABLE penjualan ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE penjualan ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE pemasukan ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE pemasukan ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE pengeluaran ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE pengeluaran ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE servis ADD COLUMN id_user INT AFTER tanggal;
ALTER TABLE servis ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE kendaraan ADD COLUMN id_user INT AFTER id_pelanggan;
ALTER TABLE kendaraan ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE pelanggan ADD COLUMN id_user INT AFTER id_pelanggan;
ALTER TABLE pelanggan ADD FOREIGN KEY (id_user) REFERENCES users(id);

ALTER TABLE sparepart ADD COLUMN id_user INT AFTER id_sparepart;
ALTER TABLE sparepart ADD FOREIGN KEY (id_user) REFERENCES users(id);
```

### 2. File-file yang Dimodifikasi

#### Input Data (Tambah)
- `penjualan/tambah.php` - Menyimpan `$_SESSION['id']` saat insert penjualan & pemasukan
- `pemasukan/tambah.php` - Menyimpan `$_SESSION['id']` saat insert pemasukan manual
- `pengeluaran/tambah.php` - Menyimpan `$_SESSION['id']` saat insert pengeluaran
- `servis/tambah.php` - Menyimpan `$_SESSION['id']` saat insert servis & pemasukan

#### Halaman Daftar (Menampilkan User)
- `penjualan/index.php` - Tambah kolom "Input oleh" dengan JOIN ke users table
- `pemasukan/index.php` - Tambah kolom "Upload oleh" dengan JOIN ke users table
- `pengeluaran/index.php` - Tambah kolom "Upload oleh" dengan JOIN ke users table
- `servis/index.php` - Tambah kolom "Input oleh" dengan JOIN ke users table

#### Halaman Laporan
- `laporan/penjualan.php` - Menampilkan "Input oleh" di laporan penjualan
- `laporan/pemasukan.php` - Menampilkan "Oleh" di laporan pemasukan
- `laporan/pengeluaran.php` - Menampilkan "Oleh" di laporan pengeluaran
- `laporan/servis.php` - Menampilkan "Input oleh" di laporan servis

## Fitur Tambahan

### Session Tracking
Sistem menggunakan `$_SESSION['id']` yang sudah ada, sehingga setiap user yang login akan otomatis tercatat saat menginput data.

### LEFT JOIN untuk Kompatibilitas
Query menggunakan LEFT JOIN sehingga data lama yang belum memiliki `id_user` tetap tampil dengan nilai "-" atau kosong.

### Pencatatan Otomatis
Data dari transaksi servis dan penjualan yang otomatis masuk ke pemasukan juga akan mencatat user yang sama.

## Pengujian

1. **Login** sebagai kasir atau admin
2. Buka halaman **Penjualan/Pemasukan/Pengeluaran/Servis**
3. Buat data baru
4. Lihat daftar data - kolom "Upload oleh" atau "Input oleh" akan menampilkan username Anda
5. Buka halaman **Laporan** - semua laporan akan menampilkan siapa yang membuat setiap transaksi

## Troubleshooting

### Error: Unknown column 'id_user'
**Solusi:** Pastikan Anda sudah menjalankan migration database terlebih dahulu.

### Kolom "Upload oleh" kosong untuk data lama
**Normal** - Data yang dibuat sebelum migration ini tidak memiliki `id_user`. Anda dapat update manual jika diperlukan:
```sql
UPDATE penjualan SET id_user = 1 WHERE id_user IS NULL;
-- 1 = ID user yang bertanggung jawab
```

### Data tidak tersimpan
Pastikan user sudah login. Jika belum login, sistem akan redirect ke login.php.

## Backup
Sebaiknya backup database sebelum menjalankan migration!

---
**Dibuat:** Juni 2024
**Version:** 1.0
