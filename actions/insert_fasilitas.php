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

$nama      = trim($_POST['nama_fasilitas'] ?? '');
$deskripsi = trim($_POST['deskripsi_fasilitas'] ?? '');

if (empty($nama)) {
    set_flash_message('error', 'Nama fasilitas wajib diisi!');
    header('Location: ../index.php');
    exit;
}

// Baca data JSON
$json_file = __DIR__ . '/../data/fasilitas.json';
$data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Upload gambar
$gambar_path = '';
if (isset($_FILES['gambar_fasilitas']) && $_FILES['gambar_fasilitas']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($_FILES['gambar_fasilitas']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        set_flash_message('error', 'Format gambar tidak didukung! Gunakan JPG, PNG, WEBP, atau GIF.');
        header('Location: ../index.php');
        exit;
    }
    
    $filename = 'fas_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $upload_dir = __DIR__ . '/../assets/uploads/fasilitas/';
    
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    if (move_uploaded_file($_FILES['gambar_fasilitas']['tmp_name'], $upload_dir . $filename)) {
        $gambar_path = 'assets/uploads/fasilitas/' . $filename;
    } else {
        set_flash_message('error', 'Gagal mengunggah gambar!');
        header('Location: ../index.php');
        exit;
    }
} else {
    set_flash_message('error', 'Gambar wajib diunggah!');
    header('Location: ../index.php');
    exit;
}

// Buat ID baru
$max_id = 0;
foreach ($data as $item) {
    if ($item['id'] > $max_id) $max_id = $item['id'];
}

$data[] = [
    'id'        => $max_id + 1,
    'nama'      => $nama,
    'gambar'    => $gambar_path,
    'deskripsi' => $deskripsi
];

// Simpan ke JSON
file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

set_flash_message('success', 'Fasilitas berhasil ditambahkan!');
header('Location: ../index.php');
exit;
?>
