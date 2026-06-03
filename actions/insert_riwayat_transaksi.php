<?php
/**
 * actions/insert_riwayat_transaksi.php
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$tanggal = clean_input($_POST['tanggal']);
$ket     = clean_input($_POST['keterangan']);
$clean_jumlah = str_replace(['.', ','], '', $_POST['jumlah']);
$jumlah  = (float)$clean_jumlah;
$jenis   = ($_POST['jenis'] === 'masuk') ? 'masuk' : 'keluar';

$sql = "INSERT INTO laporan_keuangan (tanggal, keterangan, jumlah, jenis) 
        VALUES ('$tanggal', '$ket', $jumlah, '$jenis')";
        
if (mysqli_query($conn, $sql)) {
    set_flash_message('success', 'Transaksi berhasil ditambahkan.');
} else {
    set_flash_message('error', 'Gagal menambahkan transaksi: ' . mysqli_error($conn));
}

header('Location: ../pages/admin/laporan.php');
exit;
