<?php
$page_title = "Daftar Buku";
include __DIR__ . '/../includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$daftarBuku = $_SESSION['buku'] ?? [];
?>
        <section>
            <h2>Daftar Buku</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <div class="search-box">
                <label for="search-input">Cari Judul Buku</label>
                <div class="search-actions">
                    <input type="text" id="search-input" placeholder="Ketik judul buku...">
                    <button type="button" id="btn-reload" class="btn-reload">Muat Ulang</button>
                </div>
            </div>

            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarBuku)): ?>
                    <tr>
                        <td colspan="6">Belum ada data buku. Silakan tambah lewat menu "Tambah Buku".</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($daftarBuku as $buku): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($buku['judul']); ?></td>
                            <td><?php echo htmlspecialchars($buku['pengarang']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($buku['kategori'] ?? '-')); ?></td>
                            <td><?php echo htmlspecialchars($buku['tahun']); ?></td>
                            <td><?php echo htmlspecialchars($buku['stok']); ?></td>
                            <td>
                                <button type="button">Edit</button>
                                <button type="button" class="detail">Detail</button>
                                <button type="button" class="btn-hapus">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
