<?php
// includes/remember.php
// Modul pengelolaan cookie "Ingat Saya" (Remember Me)

function get_remember_secret(): string {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
        if (!empty($env['APP_KEY'])) {
            return $env['APP_KEY'];
        }
    }
    return 'simpus-mini-secret-salt-key-2026';
}

function set_remember_cookie(int $userId, string $passwordHash): void {
    if (headers_sent()) {
        return;
    }
    $secret = get_remember_secret();
    // HMAC signature berbasis userId dan hash password agar otomatis invalid jika password diubah
    $signature = hash_hmac('sha256', $userId . '|' . $passwordHash, $secret);
    $payload = base64_encode($userId . ':' . $signature);

    // Cookie berlaku selama 30 hari
    $expire = time() + (30 * 24 * 60 * 60);

    // Set cookie dengan atribut keamanan HttpOnly dan SameSite Lax
    setcookie('remember_token', $payload, [
        'expires' => $expire,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}

function clear_remember_cookie(): void {
    if (isset($_COOKIE['remember_token'])) {
        if (!headers_sent()) {
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
        }
        unset($_COOKIE['remember_token']);
    }
}

function restore_remember_session(): bool {
    if (empty($_COOKIE['remember_token'])) {
        return false;
    }

    $decoded = base64_decode($_COOKIE['remember_token'], true);
    if (!$decoded || strpos($decoded, ':') === false) {
        clear_remember_cookie();
        return false;
    }

    [$userIdStr, $receivedSignature] = explode(':', $decoded, 2);
    $userId = (int) $userIdStr;
    if ($userId <= 0) {
        clear_remember_cookie();
        return false;
    }

    try {
        require_once __DIR__ . '/koneksi.php';
        global $pdo;

        if (!isset($pdo)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT id, nama, username, password, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            clear_remember_cookie();
            return false;
        }

        $secret = get_remember_secret();
        $expectedSignature = hash_hmac('sha256', $user['id'] . '|' . $user['password'], $secret);

        if (hash_equals($expectedSignature, $receivedSignature)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            return true;
        } else {
            clear_remember_cookie();
            return false;
        }
    } catch (\Throwable $e) {
        return false;
    }
}
