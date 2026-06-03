<?php
require_once __DIR__ . '/../config/database.php';

// Hanya admin yang boleh
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'Akses ditolak!');
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$id        = (int)($_POST['id_fasilitas'] ?? 0);
$nama      = trim($_POST['nama_fasilitas'] ?? '');
$deskripsi = trim($_POST['deskripsi_fasilitas'] ?? '');
$gambar_lama = trim($_POST['gambar_lama'] ?? '');

if (empty($nama) || $id <= 0) {
    set_flash_message('error', 'Data tidak valid!');
    header('Location: ../index.php');
    exit;
}

// Baca data JSON
$json_file = __DIR__ . '/../data/fasilitas.json';
$data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Upload gambar baru jika ada
$gambar_path = $gambar_lama;
if (isset($_FILES['gambar_fasilitas']) && $_FILES['gambar_fasilitas']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($_FILES['gambar_fasilitas']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        set_flash_message('error', 'Format gambar tidak didukung!');
        header('Location: ../index.php');
        exit;
    }
    
    $filename = 'fas_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $upload_dir = __DIR__ . '/../assets/uploads/fasilitas/';
    
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    if (move_uploaded_file($_FILES['gambar_fasilitas']['tmp_name'], $upload_dir . $filename)) {
        $gambar_path = 'assets/uploads/fasilitas/' . $filename;
        
        // Hapus gambar lama jika bukan URL eksternal
        if (!empty($gambar_lama) && strpos($gambar_lama, 'http') !== 0) {
            $old_file = __DIR__ . '/../' . $gambar_lama;
            if (file_exists($old_file)) unlink($old_file);
        }
    }
}

// Update data
foreach ($data as &$item) {
    if ($item['id'] === $id) {
        $item['nama']      = $nama;
        $item['gambar']    = $gambar_path;
        $item['deskripsi'] = $deskripsi;
        break;
    }
}
unset($item);

// Simpan ke JSON
file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

set_flash_message('success', 'Fasilitas berhasil diperbarui!');
header('Location: ../index.php');
exit;
?>
