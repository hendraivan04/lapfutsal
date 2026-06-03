<?php
/**
 * actions/admin_schedule.php
 * Handles status updates for time slots
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/admin/login.php'); exit;
}

$jid    = (int)($_GET['id_jadwal'] ?? 0);
$status = clean_input($_GET['status'] ?? '');

if ($jid && ($status === 'tersedia' || $status === 'tidak tersedia')) {
    mysqli_query($conn, "UPDATE jadwal_lapangan SET status='$status' WHERE id_jadwal=$jid");
    set_flash_message('success', 'Status jadwal berhasil diperbarui.');
}

$ref = $_SERVER['HTTP_REFERER'] ?? '../pages/admin/jadwal.php';
header("Location: $ref");
exit;
