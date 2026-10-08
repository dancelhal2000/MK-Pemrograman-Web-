-- Jobsheet 8 Latihan 7.4 No. 2: Tambah kolom tanggal_ditambahkan di tabel buku
-- Jalankan bila database simpus_mini sudah dibuat sebelumnya:
--   psql -d simpus_mini -f sql/02_tambah_kolom_tanggal_ditambahkan.sql

ALTER TABLE buku
ADD COLUMN IF NOT EXISTS tanggal_ditambahkan TIMESTAMP DEFAULT NOW();
