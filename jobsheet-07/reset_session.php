<?php
session_start();

// 1. Mengosongkan seluruh variabel superglobal $_SESSION
$_SESSION = [];

// 2. Menghapus cookie session di browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Menghancurkan session di server
session_destroy();

// 4. Mulai session baru agar bisa mengirim flash message notifikasi ke pengguna
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'pesan' => 'Seluruh data session berhasil direset.'
];

// Redirect kembali ke halaman utama (atau halaman pemanggil)
$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirectTo);
exit;