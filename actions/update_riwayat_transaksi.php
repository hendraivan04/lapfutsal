<?php
/**
 * actions/update_riwayat_transaksi.php
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$id      = (int)($_POST['id_laporan'] ?? 0);
$tanggal = clean_input($_POST['tanggal']);
$ket     = clean_input($_POST['keterangan']);
$clean_jumlah = str_replace(['.', ','], '', $_POST['jumlah']);
$jumlah  = (float)$clean_jumlah;
$jenis   = ($_POST['jenis'] === 'masuk') ? 'masuk' : 'keluar';

if (!$id) {
    header('Location: ../pages/admin/laporan.php'); exit;
}

$sql = "UPDATE laporan_keuangan 
        SET tanggal = '$tanggal', keterangan = '$ket', jumlah = $jumlah, jenis = '$jenis' 
        WHERE id_laporan = $id";
        
if (mysqli_query($conn, $sql)) {
    set_flash_message('success', 'Transaksi berhasil diperbarui.');
} else {
    set_flash_message('error', 'Gagal memperbarui transaksi: ' . mysqli_error($conn));
}

header('Location: ../pages/admin/laporan.php');
exit;
