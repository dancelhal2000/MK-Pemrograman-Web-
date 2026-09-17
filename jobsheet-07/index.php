<?php
$page_title = "Beranda";
include __DIR__ . '/includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$totalBuku = count($_SESSION['buku'] ?? []);
$totalAnggota = count($_SESSION['anggota'] ?? []);
?>
        <section>
            <h2>Selamat Datang di Sistem Perpustakaan Mini</h2>
            <p>Aplikasi sederhana untuk mengelola data buku dan anggota perpustakaan.</p>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>
        </section>

        <section>
            <h2>Ringkasan</h2>
            <article>
                <h3>Total Buku</h3>
                <p><?php echo $totalBuku; ?></p>
            </article>
            <article>
                <h3>Total Anggota</h3>
                <p><?php echo $totalAnggota; ?></p>
            </article>
            <article>
                <h3>Sedang Dipinjam</h3>
                <p>0</p>
            </article>
        </section>

        <section>
            <h2>Kelola Data Session</h2>
            <p>Kosongkan seluruh data sementara yang tersimpan di <code>$_SESSION</code> secara manual tanpa perlu menutup browser:</p>
            <form method="post" action="reset_session.php" onsubmit="return confirm('Apakah Anda yakin ingin mereset seluruh data session? Semua data buku dan anggota sementara akan dihapus.');">
                <button type="submit" style="background-color: #d9534f; color: #fff; border: none; padding: 0.6rem 1.2rem; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Reset Data</button>
            </form>
        </section>

        <section>
            <h2>Terminal / Log Output</h2>
            <pre>git clone https://github.com/perpustakaan-mini/aplikasi-sistem-informasi-perpustakaan-v3.git --branch master --depth 1</pre>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>