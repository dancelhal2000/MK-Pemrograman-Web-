<?php
$page_title = "Debug Session";
include __DIR__ . '/includes/header.php';
?>
        <section>
            <h2>Debug Isi $_SESSION</h2>
            <p>Halaman ini menampilkan isi mentah dari variabel superglobal <code>$_SESSION</code> di server saat ini:</p>
            <pre><?php print_r($_SESSION); ?></pre>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>