<?php
/**
 * actions/delete_pesanan.php
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

if ($p) {
    $jid = $p['id_jadwal'];
    
    // 1. If not already cancelled, re-open schedule
    if ($p['status_pembayaran'] !== 'batal') {
        mysqli_query($conn, "UPDATE jadwal_lapangan SET status='tersedia' WHERE id_jadwal=$jid");
    }
    
    // 2. Delete the record
    mysqli_query($conn, "DELETE FROM pemesanan WHERE id_pemesanan=$pid");
    set_flash_message('success', "Data pesanan " . $p['nama_pemesan'] . " telah dihapus.");
}

header('Location: ../pages/admin/pemesanan.php');
exit;
