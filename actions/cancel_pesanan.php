<?php
/**
 * actions/cancel_pesanan.php
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$pid    = (int)($_POST['id_pemesanan'] ?? 0);
$alasan = clean_input($_POST['alasan'] ?? 'Dibatalkan oleh admin.');

if (!$pid) {
    header('Location: ../pages/admin/pemesanan.php'); exit;
}

// Fetch booking info
$res = mysqli_query($conn, "SELECT * FROM pemesanan WHERE id_pemesanan = $pid");
$p   = mysqli_fetch_assoc($res);

if ($p) {
    $jid = $p['id_jadwal'];
    mysqli_query($conn, "UPDATE pemesanan SET status_pembayaran='batal', alasan_pembatalan='$alasan' WHERE id_pemesanan=$pid");
    
    // Re-open schedule
    mysqli_query($conn, "UPDATE jadwal_lapangan SET status='tersedia' WHERE id_jadwal=$jid");
    

    
    set_flash_message('success', "Pesanan " . $p['nama_pemesan'] . " telah dibatalkan dengan alasan: $alasan");
}

header('Location: ../pages/admin/pemesanan.php');
exit;
