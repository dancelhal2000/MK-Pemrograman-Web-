<?php
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die("File .env tidak ditemukan. Salin .env.example menjadi .env.");
}

$env = parse_ini_file($envPath);

$host     = $env['DB_HOST'] ?? 'localhost';
$port     = $env['DB_PORT'] ?? '5432';
$dbname   = $env['DB_NAME'] ?? 'simpus_mini';
$username = $env['DB_USER'] ?? 'postgres';
$password = $env['DB_PASS'] ?? '';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
