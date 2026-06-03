<?php
require_once __DIR__ . '/../config/database.php';

// Hanya admin yang boleh
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'Akses ditolak!');
    header('Location: ../index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash_message('error', 'Data tidak valid!');
    header('Location: ../index.php');
    exit;
}

// Baca data JSON
$json_file = __DIR__ . '/../data/fasilitas.json';
$data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Cari dan hapus
$new_data = [];
foreach ($data as $item) {
    if ($item['id'] === $id) {
        // Hapus file gambar jika bukan URL eksternal
        if (!empty($item['gambar']) && strpos($item['gambar'], 'http') !== 0) {
            $file = __DIR__ . '/../' . $item['gambar'];
            if (file_exists($file)) unlink($file);
        }
    } else {
        $new_data[] = $item;
    }
}

// Simpan ke JSON
file_put_contents($json_file, json_encode($new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

set_flash_message('success', 'Fasilitas berhasil dihapus!');
header('Location: ../index.php');
exit;
?>
