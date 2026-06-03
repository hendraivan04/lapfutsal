<?php
/**
 * actions/auth.php
 * Updated to match the new schema: id_user, nama, no_handphone, password, role
 * Using MD5 as per user's database seed
 */
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? '';

// ── REGISTER ──────────────────────────────────────────────────────────────────
if ($action === 'register') {
    $nama    = clean_input($_POST['nama'] ?? '');
    $username = $nama; 
    $phone   = clean_input($_POST['no_handphone'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($phone) || empty($pass)) {
        set_flash_message('error', 'Username, No. HP, dan Password wajib diisi.');
        header('Location: ../daftar.php'); exit;
    }
    
    // Check duplicate phone in pelanggan table
    $check_phone = mysqli_query($conn, "SELECT id_pelanggan FROM pelanggan WHERE no_handphone = '$phone'");
    if (mysqli_num_rows($check_phone) > 0) {
        set_flash_message('error', 'Nomor handphone sudah terdaftar.');
        header('Location: ../daftar.php'); exit;
    }

    $hashed = md5($pass);
    $sql = "INSERT INTO pelanggan (nama, username, no_handphone, password) VALUES ('$nama', '$username', '$phone', '$hashed')";
    if (mysqli_query($conn, $sql)) {
        set_flash_message('success', 'Registrasi berhasil! Silakan login.');
        header('Location: ../login.php');
    } else {
        set_flash_message('error', 'Terjadi kesalahan sistem.');
        header('Location: ../daftar.php');
    }
    exit;
}

// ── LOGIN ─────────────────────────────────────────────────────────────────────
if ($action === 'login' || $action === 'admin_login') {
    $usernm = clean_input($_POST['username'] ?? '');
    $pass   = md5($_POST['password'] ?? '');
    $is_admin = ($action === 'admin_login');

    if (empty($usernm) || empty($_POST['password'])) {
        set_flash_message('error', 'Username dan password wajib diisi.');
        $loc = $is_admin ? '../pages/admin/login.php' : '../login.php';
        header("Location: $loc"); exit;
    }

    if ($is_admin) {
        $sql = "SELECT * FROM admin WHERE username = '$usernm'";
    } else {
        // Customers login with Nama/Username
        $sql = "SELECT * FROM pelanggan WHERE username = '$usernm'";
    }
    
    $res = mysqli_query($conn, $sql);
    $u   = mysqli_fetch_assoc($res);

    if ($u && $u['password'] === $pass) {
        // We keep user_id and user_role in session for backward compatibility in templates
        $_SESSION['user_id']   = $is_admin ? $u['id_admin'] : $u['id_pelanggan'];
        $_SESSION['user_name'] = $u['nama'];
        $_SESSION['user_role'] = $is_admin ? 'admin' : 'user';
        set_flash_message('success', 'Selamat datang, ' . $u['nama'] . '!');
        
        if ($is_admin) {
            header('Location: ../pages/admin/dashboard.php');
        } else {
            header('Location: ../pages/user/dashboard.php');
        }
    } else {
        set_flash_message('error', 'Username atau password salah.');
        $loc = $is_admin ? '../pages/admin/login.php' : '../login.php';
        header("Location: $loc");
    }
    exit;
}

// ── UPDATE PROFILE ────────────────────────────────────────────────────────────
if ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
    $uid   = (int)$_SESSION['user_id'];
    $role  = $_SESSION['user_role'];
    $nama  = clean_input($_POST['nama'] ?? '');
    $usernm = $nama; 
    $phone = clean_input($_POST['no_handphone'] ?? '');
    $pass  = $_POST['new_password'] ?? '';

    if (empty($nama)) {
        set_flash_message('error', 'Nama tidak boleh kosong.');
        $loc = ($role === 'admin') ? '../pages/admin/profil.php' : '../pages/user/profile.php';
        header("Location: $loc"); exit;
    }

    $table = ($role === 'admin') ? 'admin' : 'pelanggan';
    $pk    = ($role === 'admin') ? 'id_admin' : 'id_pelanggan';

    if (!empty($pass)) {
        $hashed = md5($pass);
        mysqli_query($conn, "UPDATE $table SET nama='$nama', username='$usernm', no_handphone='$phone', password='$hashed' WHERE $pk=$uid");
    } else {
        mysqli_query($conn, "UPDATE $table SET nama='$nama', username='$usernm', no_handphone='$phone' WHERE $pk=$uid");
    }

    $_SESSION['user_name'] = $nama;
    set_flash_message('success', 'Profil berhasil diperbarui.');
    $loc = ($role === 'admin') ? '../pages/admin/profil.php' : '../pages/user/profile.php';
    header("Location: $loc");
    exit;
}

header('Location: ../index.php');
exit;
