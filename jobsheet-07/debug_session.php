<?php
$page_title = "Debug Session";
include __DIR__ . '/includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
        <section>
            <h2>Debug Isi $_SESSION</h2>
            <p>Halaman ini menampilkan isi mentah dari variabel superglobal <code>$_SESSION</code> di server saat ini:</p>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <pre><?php print_r($_SESSION); ?></pre>

            <form method="post" action="reset_session.php" onsubmit="return confirm('Apakah Anda yakin ingin mereset seluruh data session?');" style="margin-top: 1.5rem;">
                <button type="submit" style="background-color: #d9534f; color: #fff; border: none; padding: 0.6rem 1.2rem; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Reset Data</button>
            </form>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>