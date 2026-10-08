<?php
$page_title = "Daftar Buku";
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$keyword = trim($_GET['q'] ?? '');

// Latihan 6.4 (no. 1): Proteksi CSRF pada form pencarian
if (isset($_GET['q'])) {
    csrf_verify_get();
}

if ($keyword !== '') {
    $searchPattern = '%' . $keyword . '%';
    $hitung = $pdo->prepare("SELECT COUNT(*) FROM buku WHERE (judul ILIKE :kw1 OR pengarang ILIKE :kw2)");
    $hitung->execute(['kw1' => $searchPattern, 'kw2' => $searchPattern]);
    $totalRows = $hitung->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM buku WHERE (judul ILIKE :kw1 OR pengarang ILIKE :kw2) ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue('kw1', $searchPattern);
    $stmt->bindValue('kw2', $searchPattern);
} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM buku ORDER BY id DESC LIMIT :limit OFFSET :offset");
}
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarBuku = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
?>
        <section>
            <h2>Daftar Buku</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['pesan']); ?></p>
            <?php endif; ?>

            <div class="search-box">
                <form method="get" action="list.php">
                    <?php echo csrf_field(); ?>
                    <label for="search-input">Cari Judul / Pengarang Buku</label>
                    <div class="search-actions">
                        <input type="text" id="search-input" name="q" value="<?php echo e($keyword); ?>" placeholder="Ketik judul atau pengarang buku...">
                        <button type="submit" class="btn-cari">Cari</button>
                        <button type="button" id="btn-reload" class="btn-reload">Muat Ulang</button>
                    </div>
                </form>
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
                        <th>Ditambahkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarBuku)): ?>
                    <tr>
                        <td colspan="7">
                            <?php if ($keyword !== ''): ?>
                                Tidak ada data buku yang cocok dengan kata kunci "<strong><?php echo e($keyword); ?></strong>".
                            <?php else: ?>
                                Belum ada data buku. Silakan tambah lewat menu "Tambah Buku".
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($daftarBuku as $buku): ?>
                        <tr>
                            <td><?php echo e($buku['judul']); ?></td>
                            <td><?php echo e($buku['pengarang']); ?></td>
                            <td><?php echo e(ucfirst($buku['kategori'] ?? '-')); ?></td>
                            <td><?php echo e($buku['tahun']); ?></td>
                            <td><?php echo e($buku['stok']); ?></td>
                            <td><?php echo !empty($buku['tanggal_ditambahkan']) ? e(date('d-m-Y H:i', strtotime($buku['tanggal_ditambahkan']))) : '-'; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo e($buku['id']); ?>" class="btn-edit">Edit</a>
                                <button type="button" class="detail">Detail</button>
                                <form class="form-hapus" method="post" action="hapus.php">
                                    <input type="hidden" name="id" value="<?php echo e($buku['id']); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <nav class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="list.php?page=<?php echo $i; ?><?php echo $keyword !== '' ? '&q=' . urlencode($keyword) . '&csrf_token=' . urlencode(csrf_token()) : ''; ?>"
                   class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
