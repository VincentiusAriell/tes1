<?php
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../app/functions.php';

$file = $_GET['file'] ?? '';
$path = __DIR__ . '/' . $file;

if (!file_exists($path)) {
    die("File tidak ditemukan");
}

// Buat file sementara hasil dekripsi
$tmpPath = __DIR__ . '/tmp_' . basename($path);

if (decryptFileCamellia($path, $tmpPath)) {
    // Kirim header download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($tmpPath));

    // Output file dan hapus sementara
    readfile($tmpPath);
    unlink($tmpPath);
    exit;
} else {
    die("Gagal mendekripsi file.");
}
?>
