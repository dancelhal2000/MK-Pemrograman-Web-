<?php
$page_title = "Beranda";
include __DIR__ . '/includes/header.php';

$totalBuku = count($_SESSION['buku'] ?? []);
$totalAnggota = count($_SESSION['anggota'] ?? []);
?>
        <section>
            <h2>Selamat Datang di Sistem Perpustakaan Mini</h2>
            <p>Aplikasi sederhana untuk mengelola data buku dan anggota perpustakaan.</p>
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
            <h2>Terminal / Log Output</h2>
            <pre>git clone https://github.com/perpustakaan-mini/aplikasi-sistem-informasi-perpustakaan-v3.git --branch master --depth 1</pre>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
