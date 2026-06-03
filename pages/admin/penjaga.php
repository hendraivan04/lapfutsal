<?php
// pages/admin/penjaga.php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'Hanya admin yang diperbolehkan masuk.');
    header('Location: login.php');
    exit;
}
?>
