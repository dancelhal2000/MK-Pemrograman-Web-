-- Jobsheet 12 Latihan 6.4 No. 2: Tambah kolom tanggal_jatuh_tempo di tabel peminjaman
-- Jalankan bila tabel peminjaman sudah dibuat sebelumnya:
--   psql -d simpus_mini -f sql/05_tambah_kolom_tanggal_jatuh_tempo.sql

ALTER TABLE peminjaman
ADD COLUMN IF NOT EXISTS tanggal_jatuh_tempo DATE;

-- Update data yang sudah ada sebelumnya (otomatis 14 hari setelah tanggal_pinjam)
UPDATE peminjaman
SET tanggal_jatuh_tempo = tanggal_pinjam + INTERVAL '14 days'
WHERE tanggal_jatuh_tempo IS NULL;
