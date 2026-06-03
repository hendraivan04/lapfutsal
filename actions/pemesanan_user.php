<?php
/**
 * actions/booking.php
 * Updated to handle checkout details (Nama, WA, Bukti Pembayaran)
 */
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); exit;
}

$action   = $_GET['action'] ?? '';
$user_id  = (int)$_SESSION['user_id'];

// ── CREATE BOOKING (from Checkout) ───────────────────────────────────────────
if ($action === 'create') {
    $jid    = (int)($_POST['id_jadwal'] ?? 0);
    $nama   = clean_input($_POST['nama_pemesan'] ?? '');
    $wa     = clean_input($_POST['no_wa'] ?? '');
    $file   = $_FILES['bukti_pembayaran'] ?? null;
    
    if (!$jid || empty($nama) || empty($wa) || !$file) {
        set_flash_message('error', 'Lengkapi data administrasi dan bukti pembayaran.');
        header('Location: ../pages/user/booking.php'); exit;
    }

    // Fetch jadwal info
    $jres = mysqli_query($conn, "SELECT * FROM jadwal_lapangan WHERE id_jadwal = $jid");
    $j = mysqli_fetch_assoc($jres);

    if (!$j || $j['status'] !== 'tersedia') {
        set_flash_message('error', 'Jadwal sudah tidak tersedia.');
        header('Location: ../pages/user/booking.php'); exit;
    }

    // Check if slot is in the past
    $slot_time = strtotime($j['tanggal'] . ' ' . $j['jam_mulai']);
    if ($slot_time < time()) {
        set_flash_message('error', 'Jadwal sudah tidak tersedia (Waktu Lampau).');
        header('Location: ../pages/user/booking.php'); exit;
    }

    $lid = $j['id_lapangan'];
    $date = $j['tanggal'];
    $durasi = 1;

    // Time-based pricing logic
    $hour = (int)substr($j['jam_mulai'], 0, 2);
    if ($hour >= 18) {
        $harga = 120000;
    } else {
        $harga = 100000;
    }
    $total = $durasi * $harga;

    // Handle File Upload
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . $user_id . '.' . $ext;
    $target_dir = __DIR__ . '/../assets/uploads/bukti/';
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        // Insert into pemesanan
        $sql = "INSERT INTO pemesanan (id_pelanggan, nama_pemesan, no_wa, id_lapangan, id_jadwal, tanggal, durasi, total_harga, status_pembayaran, bukti_pembayaran) 
                VALUES ($user_id, '$nama', '$wa', $lid, $jid, '$date', $durasi, $total, 'pending', '$filename')";
        
        if (mysqli_query($conn, $sql)) {
            // Update jadwal status
            mysqli_query($conn, "UPDATE jadwal_lapangan SET status = 'tidak tersedia' WHERE id_jadwal = $jid");
            
            
            set_flash_message('success', 'Pesanan sedang diproses oleh Admin.');
            header('Location: ../pages/user/dashboard.php');
        } else {
            set_flash_message('error', 'Gagal mencatat pemesanan: ' . mysqli_error($conn));
            header('Location: ../pages/user/booking.php');
        }
    } else {
        set_flash_message('error', 'Format file tidak didukung atau upload gagal.');
        header('Location: ../pages/user/booking.php');
    }
    exit;
}

// ── CANCEL BOOKING ────────────────────────────────────────────────────────────
if ($action === 'cancel') {
    $pid = (int)($_GET['id_pemesanan'] ?? 0);
    
    $pres = mysqli_query($conn, "SELECT * FROM pemesanan WHERE id_pemesanan = $pid AND id_pelanggan = $user_id");
    $p = mysqli_fetch_assoc($pres);

    if ($p && $p['status_pembayaran'] === 'pending') {
        $jid = $p['id_jadwal'];
        mysqli_query($conn, "UPDATE pemesanan SET status_pembayaran = 'batal' WHERE id_pemesanan = $pid");
        mysqli_query($conn, "UPDATE jadwal_lapangan SET status = 'tersedia' WHERE id_jadwal = $jid");
        set_flash_message('success', 'Booking berhasil dibatalkan.');
    } else {
        set_flash_message('error', 'Booking tidak dapat dibatalkan.');
    }
    header('Location: ../pages/user/riwayat.php');
    exit;
}

header('Location: ../index.php');
exit;
