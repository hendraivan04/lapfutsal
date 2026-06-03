<?php
require_once __DIR__ . '/../config/database.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'Akses ditolak!');
    header('Location: ../index.php');
    exit;
}

// Cek request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin/lapangan.php');
    exit;
}

$id_lapangan   = (int)($_POST['id_lapangan'] ?? 0);
$nama          = clean_input($_POST['nama'] ?? '');
$alamat        = clean_input($_POST['alamat'] ?? '');
$no_handphone  = clean_input($_POST['no_handphone'] ?? '');

if ($id_lapangan <= 0 || empty($nama) || empty($alamat)) {
    set_flash_message('error', 'Semua field dengan tanda bintang (*) wajib diisi!');
    header('Location: ../pages/admin/lapangan.php');
    exit;
}

// Eksekusi query update database
$query = "UPDATE lapangan SET 
            nama = '$nama', 
            alamat = '$alamat', 
            no_handphone = '$no_handphone' 
          WHERE id_lapangan = $id_lapangan";

if (mysqli_query($conn, $query)) {
    set_flash_message('success', 'Berhasil menyimpan pengaturan lapangan!');
} else {
    set_flash_message('error', 'Gagal memperbarui data: ' . mysqli_error($conn));
}

header('Location: ../pages/admin/lapangan.php');
exit;
?>
