<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Username dan password wajib diisi.'];
    header('Location: login.php');
    exit;
}

// Konfigurasi pembatasan percobaan login (Latihan 6.4 no. 3)
$maxAttempts = 3;
$lockoutDuration = 60; // 60 detik pembekuan sementara

$attemptData = $_SESSION['login_attempts'][$username] ?? null;
if ($attemptData && $attemptData['count'] >= $maxAttempts) {
    $timeSinceLastAttempt = time() - $attemptData['last_attempt'];
    if ($timeSinceLastAttempt < $lockoutDuration) {
        $remainingSeconds = $lockoutDuration - $timeSinceLastAttempt;
        $_SESSION['last_username'] = $username;
        $_SESSION['flash'] = [
            'type' => 'error',
            'pesan' => "Peringatan Keamanan: Terlalu banyak percobaan login gagal untuk username '{$username}'. Akun dibekukan sementara untuk mencegah serangan brute-force. Silakan coba lagi dalam {$remainingSeconds} detik."
        ];
        header('Location: login.php');
        exit;
    } else {
        // Masa pembekuan selesai, reset counter percobaan
        unset($_SESSION['login_attempts'][$username]);
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // Login berhasil: bersihkan counter kegagalan dan username sementara
    unset($_SESSION['login_attempts'][$username]);
    unset($_SESSION['last_username']);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];

    // Kelola cookie "Ingat Saya" (Remember Me)
    require_once __DIR__ . '/../includes/remember.php';
    if (!empty($_POST['remember'])) {
        set_remember_cookie($user['id'], $user['password']);
    } else {
        clear_remember_cookie();
    }

    header('Location: ../index.php');
    exit;
}

// Login gagal: simpan username agar tetap terisi di form
$_SESSION['last_username'] = $username;

// Catat dan tingkatkan counter percobaan gagal per username di session
if (!isset($_SESSION['login_attempts'][$username])) {
    $_SESSION['login_attempts'][$username] = [
        'count' => 0,
        'last_attempt' => time(),
    ];
}
$_SESSION['login_attempts'][$username]['count']++;
$_SESSION['login_attempts'][$username]['last_attempt'] = time();
$currentAttempts = $_SESSION['login_attempts'][$username]['count'];

if ($currentAttempts >= $maxAttempts) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Peringatan Keamanan: Percobaan login gagal {$currentAttempts} kali berturut-turut. Akun '{$username}' dibekukan sementara selama {$lockoutDuration} detik untuk mencegah serangan brute-force."
    ];
} else {
    $sisa = $maxAttempts - $currentAttempts;
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Username atau password salah. (Percobaan ke-{$currentAttempts} dari {$maxAttempts}. Sisa {$sisa} kesempatan)."
    ];
}

header('Location: login.php');
exit;

