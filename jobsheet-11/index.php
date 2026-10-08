<?php
$page_title = "Beranda";
include __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$totalBuku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$totalAnggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
?>
        <section>
            <h2>Selamat Datang di Sistem Perpustakaan Mini</h2>
            <p>Aplikasi sederhana untuk mengelola data buku dan anggota perpustakaan.</p>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['pesan']); ?></p>
            <?php endif; ?>
        </section>

        <section>
            <h2>Ringkasan</h2>
            <article>
                <h3>Total Buku</h3>
                <p><?php echo e($totalBuku); ?></p>
            </article>
            <article>
                <h3>Total Anggota</h3>
                <p><?php echo e($totalAnggota); ?></p>
            </article>
            <article>
                <h3>Sedang Dipinjam</h3>
                <p>0</p>
            </article>
        </section>

        <section>
            <h2>Migrasi Data Lama</h2>
            <p>Pindahkan data koleksi buku lama dari file JSON (Jobsheet 6) ke dalam database PostgreSQL secara otomatis:</p>
            <p style="margin-top: 0.75rem;">
                <a href="migrasi_buku.php" class="btn-cari" style="text-decoration: none; display: inline-block;">Jalankan Migrasi Buku</a>
            </p>
        </section>

        <section>
            <h2>Terminal / Log Output</h2>
            <pre>git clone https://github.com/perpustakaan-mini/aplikasi-sistem-informasi-perpustakaan-v3.git --branch master --depth 1</pre>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
