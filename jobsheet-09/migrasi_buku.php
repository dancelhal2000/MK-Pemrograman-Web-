<?php
// Jobsheet 8 Latihan 7.4 No. 4: Migrasi data lama dari jobsheet-06/data/buku.json
$isCli = (php_sapi_name() === 'cli');

require __DIR__ . '/includes/koneksi.php';

// Tentukan path file data/buku.json dari jobsheet-06
$jsonPaths = [
    dirname(__DIR__) . '/jobsheet-06/data/buku.json',
    __DIR__ . '/data/buku.json',
];

$jsonFile = null;
foreach ($jsonPaths as $path) {
    if (file_exists($path)) {
        $jsonFile = $path;
        break;
    }
}

$hasilMigrasi = [];
$errorMsg = null;

if (!$jsonFile) {
    $errorMsg = "File data/buku.json tidak ditemukan pada: " . implode(' atau ', $jsonPaths);
} else {
    $rawJson = file_get_contents($jsonFile);
    $dataBuku = json_decode($rawJson, true);

    if (!is_array($dataBuku)) {
        $errorMsg = "Gagal mem-parsing data JSON dari " . $jsonFile;
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM buku WHERE judul = :judul AND pengarang = :pengarang");
        $insertStmt = $pdo->prepare(
            "INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori)
             VALUES (:judul, :pengarang, :tahun, :isbn, :stok, :kategori)
             RETURNING id"
        );

        foreach ($dataBuku as $item) {
            $judul = trim($item['judul'] ?? '');
            $pengarang = trim($item['pengarang'] ?? '');
            $tahun = (int) ($item['tahun'] ?? 2026);
            $isbn = $item['isbn'] ?? null;
            $stok = (int) ($item['stok'] ?? 0);
            $kategori = strtolower(trim($item['kategori'] ?? 'fiksi'));

            if ($judul === '') {
                continue;
            }

            // Cek apakah buku sudah ada di database (idempoten)
            $checkStmt->execute(['judul' => $judul, 'pengarang' => $pengarang]);
            if ($checkStmt->fetchColumn() > 0) {
                $hasilMigrasi[] = [
                    'judul' => $judul,
                    'pengarang' => $pengarang,
                    'status' => 'Dilewati (Sudah ada)'
                ];
                continue;
            }

            // Masukkan data ke PostgreSQL
            try {
                $insertStmt->execute([
                    'judul' => $judul,
                    'pengarang' => $pengarang,
                    'tahun' => $tahun,
                    'isbn' => $isbn,
                    'stok' => $stok,
                    'kategori' => $kategori,
                ]);
                $hasilMigrasi[] = [
                    'judul' => $judul,
                    'pengarang' => $pengarang,
                    'status' => 'Berhasil'
                ];
            } catch (PDOException $e) {
                $hasilMigrasi[] = [
                    'judul' => $judul,
                    'pengarang' => $pengarang,
                    'status' => 'Gagal: ' . $e->getMessage()
                ];
            }
        }
    }
}

// Jika dijalankan lewat CLI:
if ($isCli) {
    echo "=============================================\n";
    echo "  MIGRASI DATA BUKU DARI JOBSHEET-06 KE POSTGRESQL\n";
    echo "=============================================\n";
    if ($errorMsg) {
        echo "[ERROR] $errorMsg\n";
        exit(1);
    }

    $berhasil = 0;
    $dilewati = 0;
    $gagal = 0;

    foreach ($hasilMigrasi as $item) {
        if ($item['status'] === 'Berhasil') {
            $berhasil++;
            echo " [OK] {$item['judul']} - {$item['pengarang']}\n";
        } elseif (strpos($item['status'], 'Dilewati') !== false) {
            $dilewati++;
            echo " [SKIP] {$item['judul']} ({$item['status']})\n";
        } else {
            $gagal++;
            echo " [FAIL] {$item['judul']} ({$item['status']})\n";
        }
    }

    echo "---------------------------------------------\n";
    echo "Ringkasan Migrasi:\n";
    echo " - Total diproses : " . count($hasilMigrasi) . "\n";
    echo " - Berhasil di-insert: $berhasil\n";
    echo " - Dilewati (duplikat): $dilewati\n";
    echo " - Gagal              : $gagal\n";
    echo "=============================================\n";
    exit(0);
}

// Jika dijalankan lewat Web Browser:
$page_title = "Migrasi Data Buku";
include __DIR__ . '/includes/header.php';
?>
        <section>
            <h2>Migrasi Data Lama (Jobsheet 6 &rarr; Jobsheet 8)</h2>
            <p>Halaman ini memindahkan data buku dari file <code>data/buku.json</code> (Jobsheet 6) ke dalam database PostgreSQL.</p>

            <?php if ($errorMsg): ?>
                <p class="flash flash-error"><?php echo htmlspecialchars($errorMsg); ?></p>
            <?php else: ?>
                <?php
                $berhasil = count(array_filter($hasilMigrasi, fn($r) => $r['status'] === 'Berhasil'));
                $dilewati = count(array_filter($hasilMigrasi, fn($r) => strpos($r['status'], 'Dilewati') !== false));
                $gagal = count($hasilMigrasi) - $berhasil - $dilewati;
                ?>
                <p class="flash flash-success">
                    Migrasi selesai: <strong><?php echo $berhasil; ?></strong> berhasil dimasukkan,
                    <strong><?php echo $dilewati; ?></strong> sudah ada (dilewati),
                    <strong><?php echo $gagal; ?></strong> gagal.
                </p>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Buku</th>
                                <th>Pengarang</th>
                                <th>Status Migrasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hasilMigrasi as $idx => $row): ?>
                            <tr>
                                <td><?php echo $idx + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars($row['pengarang']); ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Berhasil'): ?>
                                        <span style="color: #155724; font-weight: 600;">&#10003; Berhasil</span>
                                    <?php elseif (strpos($row['status'], 'Dilewati') !== false): ?>
                                        <span style="color: #856404;">&#8212; Dilewati (Sudah ada)</span>
                                    <?php else: ?>
                                        <span style="color: #721c24; font-weight: 600;">&#10007; <?php echo htmlspecialchars($row['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p style="margin-top: 1.5rem;">
                    <a href="buku/list.php" class="btn-reload" style="text-decoration: none; display: inline-block;">Lihat Daftar Buku</a>
                </p>
            <?php endif; ?>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
