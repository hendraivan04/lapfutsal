<?php
/**
 * actions/delete_riwayat_transaksi.php
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$id = (int)($_GET['id_laporan'] ?? 0);

if ($id) {
    mysqli_query($conn, "DELETE FROM laporan_keuangan WHERE id_laporan = $id");
    set_flash_message('success', 'Transaksi berhasil dihapus.');
}

header('Location: ../pages/admin/laporan.php');
exit;
