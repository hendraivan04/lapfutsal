<?php
/**
 * actions/confirm_pesanan.php
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$pid = (int)($_GET['id_pemesanan'] ?? 0);

if (!$pid) {
    header('Location: ../pages/admin/pemesanan.php'); exit;
}

// Fetch booking info
$res = mysqli_query($conn, "SELECT * FROM pemesanan WHERE id_pemesanan = $pid");
$p   = mysqli_fetch_assoc($res);

if ($p && $p['status_pembayaran'] === 'pending') {
    // 1. Update status to lunas and record which admin confirmed
    $admin_id = (int)$_SESSION['user_id'];
    mysqli_query($conn, "UPDATE pemesanan SET status_pembayaran='lunas', id_admin=$admin_id WHERE id_pemesanan=$pid");
    
    // 2. Record to Laporan Keuangan
    $tgl    = date('Y-m-d');
    $ket    = "Pembayaran " . $p['nama_pemesan'];
    $jumlah = $p['total_harga'];
    
    mysqli_query($conn, "INSERT INTO laporan_keuangan (id_pemesanan, tanggal, keterangan, jumlah, jenis) 
                         VALUES ($pid, '$tgl', '$ket', $jumlah, 'masuk')");



    set_flash_message('success', "Pesanan " . $p['nama_pemesan'] . " berhasil dikonfirmasi lunas.");
}

header('Location: ../pages/admin/pemesanan.php');
exit;
