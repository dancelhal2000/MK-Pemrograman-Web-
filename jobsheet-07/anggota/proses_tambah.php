<?php
session_start();

$nama = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');

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
if ($noAnggota !== '' && !empty($_SESSION['anggota'])) {
    foreach ($_SESSION['anggota'] as $existing) {
        if ($existing['no_anggota'] === $noAnggota) {
            $errors[] = "No. Anggota sudah terdaftar.";
            break;
        }
    }
}

// 3. Validasi format No. HP (opsional, jika diisi hanya angka/simbol telepon)
if ($noHp !== '' && !preg_match('/^[0-9+\s-]+$/', $noHp)) {
    $errors[] = "No. HP hanya boleh berisi angka, spasi, tanda +, atau tanda -.";
}

// 4. Validasi format Email (opsional, jika diisi harus format email yang valid)
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}

// Jika ada error validasi, kirim pesan error lewat flash message dan redirect kembali
if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

if (!isset($_SESSION['anggota'])) {
    $_SESSION['anggota'] = [];
}

$_SESSION['anggota'][] = [
    'nama' => $nama,
    'no_anggota' => $noAnggota,
    'alamat' => $alamat,
    'no_hp' => $noHp,
    'email' => $email,
    'tanggal_bergabung' => date('d-m-Y'),
];

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil ditambahkan.'];
header('Location: list.php');
exit;