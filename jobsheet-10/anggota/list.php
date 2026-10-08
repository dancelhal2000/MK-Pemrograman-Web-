<?php
require __DIR__ . '/../includes/auth.php';
$page_title = "Daftar Anggota";
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$perPage = 5;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $searchPattern = '%' . $keyword . '%';
    $hitung = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE (nama ILIKE :kw1 OR no_anggota ILIKE :kw2)");
    $hitung->execute(['kw1' => $searchPattern, 'kw2' => $searchPattern]);
    $totalRows = $hitung->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE (nama ILIKE :kw1 OR no_anggota ILIKE :kw2) ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue('kw1', $searchPattern);
    $stmt->bindValue('kw2', $searchPattern);
} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM anggota ORDER BY id DESC LIMIT :limit OFFSET :offset");
}
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarAnggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
?>
        <section>
            <h2>Daftar Anggota</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <div class="search-box">
                <form method="get" action="list.php">
                    <label for="search-input">Cari Nama / No. Anggota</label>
                    <div class="search-actions">
                        <input type="text" id="search-input" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Ketik nama atau no. anggota...">
                        <button type="submit" class="btn-cari">Cari</button>
                        <button type="button" id="btn-reload" class="btn-reload">Muat Ulang</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Anggota</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No. HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarAnggota)): ?>
                    <tr>
                        <td colspan="5">
                            <?php if ($keyword !== ''): ?>
                                Tidak ada data anggota yang cocok dengan kata kunci "<strong><?php echo htmlspecialchars($keyword); ?></strong>".
                            <?php else: ?>
                                Belum ada data anggota. Silakan tambah lewat menu "Tambah Anggota".
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($daftarAnggota as $anggota): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($anggota['no_anggota']); ?></td>
                            <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                            <td><?php echo htmlspecialchars($anggota['alamat']); ?></td>
                            <td><?php echo htmlspecialchars($anggota['no_hp']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $anggota['id']; ?>" class="btn-edit">Edit</a>
                                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <form class="form-hapus" method="post" action="hapus.php">
                                    <input type="hidden" name="id" value="<?php echo $anggota['id']; ?>">
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <nav class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="list.php?page=<?php echo $i; ?><?php echo $keyword !== '' ? '&q=' . urlencode($keyword) : ''; ?>"
                   class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
