<?php
require_once __DIR__ . '/config/database.php';

$sql = "ALTER TABLE pemesanan ADD COLUMN alasan_pembatalan TEXT NULL AFTER status_pembayaran";

if (mysqli_query($conn, $sql)) {
    echo "Kolom 'alasan_pembatalan' berhasil ditambahkan.";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
