<?php
// includes/navigasi_user.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <div class="container navbar-inner">
    <a href="<?= $base_url ?>index.php" class="navbar-brand">
      <i class="fas fa-futbol"></i> FutsalPOMPemenang
    </a>
    
    <div class="nav-links">
      <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
        <a href="<?= $base_url ?>pages/user/booking.php" <?= $current_page == 'booking.php' ? 'style="color:var(--primary);"' : '' ?>>
          <i class="fas fa-calendar-plus"></i> Booking Lapangan
        </a>
      <?php else: ?>
        <a href="<?= $base_url ?>index.php" <?= $current_page == 'index.php' ? 'style="color:var(--primary);"' : '' ?>>
          <i class="fas fa-home"></i> Beranda
        </a>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php 
          $uid = (int)$_SESSION['user_id'];
          $role = $_SESSION['user_role'] ?? 'user';

          if ($role === 'user'): 
        ?>
          <a href="<?= $base_url ?>pages/user/dashboard.php">Dashboard</a>
          <?php
            // Check if there are any new admin messages since user last checked
            // Uses cookie (persists across logout/login) instead of session
            $all_booking_q = mysqli_query($conn, "SELECT id_pemesanan, status_pembayaran FROM pemesanan WHERE id_pelanggan = $uid AND status_pembayaran IN ('lunas','batal')");
            $current_statuses = [];
            while ($row = mysqli_fetch_assoc($all_booking_q)) {
                $current_statuses[$row['id_pemesanan']] = $row['status_pembayaran'];
            }
            $cookie_key = 'seen_notif_' . $uid;
            $seen = isset($_COOKIE[$cookie_key]) ? json_decode($_COOKIE[$cookie_key], true) : [];
            $has_new_message = false;
            foreach ($current_statuses as $bid => $status) {
                if (!isset($seen[$bid]) || $seen[$bid] !== $status) {
                    $has_new_message = true;
                    break;
                }
            }
          ?>
          <a href="<?= $base_url ?>pages/user/riwayat.php" style="display:inline-flex; align-items:center; gap:5px; position:relative;">
            Riwayat Booking
            <?php if ($has_new_message): ?>
              <span style="background:var(--danger); color:white; font-size:0.65rem; padding:1px 6px; border-radius:10px; font-weight:700; min-width:18px; text-align:center; animation: notifPulse 2s infinite;">
                1
              </span>
            <?php endif; ?>
          </a>
        <?php else: ?>
          <a href="<?= $base_url ?>pages/admin/dashboard.php">Admin Lapangan</a>
        <?php endif; ?>
        
        <?php if ($role === 'user'): ?>
          <a href="<?= $base_url ?>pages/user/profile.php" title="Pengaturan Profil">Profil Saya</a>
        <?php endif; ?>
        
        <a href="<?= $base_url ?>keluar.php" class="btn btn-outline" style="padding:0.4rem 1.2rem;">Logout</a>
      <?php else: ?>
        <a href="<?= $base_url ?>login.php">Masuk</a>
        <a href="<?= $base_url ?>daftar.php" class="btn btn-primary">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
