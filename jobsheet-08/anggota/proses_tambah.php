<?php
session_start();
require __DIR__ . '/../includes/koneksi.php';

$nama = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');

// Validasi server-side
$errors = [];

// 1. Validasi field wajib diisi
if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
} elseif (strlen($nama) < 3) {
    $errors[] = "Nama minimal 3 karakter.";
}

if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
}

// 2. Cek keunikan No. Anggota (mencegah duplikasi data anggota)
if ($noAnggota !== '') {
    $cek = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE no_anggota = :no_anggota");
    $cek->execute(['no_anggota' => $noAnggota]);
    if ($cek->fetchColumn() > 0) {
        $errors[] = "No. Anggota sudah terdaftar.";
    }
}

// 3. Validasi format No. HP (opsional, jika diisi hanya angka/simbol telepon)
if ($noHp !== '' && !preg_match('/^[0-9+\s-]+$/', $noHp)) {
    $errors[] = "No. HP hanya boleh berisi angka, spasi, tanda +, atau tanda -.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO anggota (nama, no_anggota, alamat, no_hp)
         VALUES (:nama, :no_anggota, :alamat, :no_hp)
         RETURNING id"
    );
    $stmt->execute([
        'nama' => $nama,
        'no_anggota' => $noAnggota,
        'alamat' => $alamat,
        'no_hp' => $noHp,
    ]);
} catch (PDOException $e) {
    // Tangani error UNIQUE dengan rapi (Latihan 7.4 No. 1)
    if ($e->getCode() === '23505' || stripos($e->getMessage(), 'unique') !== false || stripos($e->getMessage(), 'duplicate') !== false) {
        $pesan = "No. Anggota sudah dipakai, gunakan nomor lain.";
    } else {
        $pesan = "Gagal menyimpan data anggota: " . $e->getMessage();
    }
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
    header('Location: tambah.php');
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil ditambahkan.'];
header('Location: list.php');
exit;
