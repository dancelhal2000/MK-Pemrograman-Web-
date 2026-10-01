<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek auto-login dari cookie "Ingat Saya"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../includes/remember.php';
    restore_remember_session();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Login";
include __DIR__ . '/../includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$lastUsername = $_SESSION['last_username'] ?? '';
unset($_SESSION['last_username']);
?>
        <section>
            <h2>Login Petugas</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <form method="post" action="proses_login.php">
                <p>
                    <label for="username">Username</label><br>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($lastUsername); ?>" required>
                </p>
                <p>
                    <label for="password">Password</label><br>
                    <input type="password" id="password" name="password" required>
                </p>
                <p>
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1"> Ingat Saya
                    </label>
                </p>
                <p>
                    <button type="submit">Masuk</button>
                </p>
            </form>
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
