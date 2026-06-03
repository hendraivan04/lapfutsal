<?php
// Shared page footer
?>
  <!-- Footer -->
  <?php 
    $current_page = $_SERVER['PHP_SELF'];
    $is_admin_page = strpos($current_page, '/admin/') !== false;
    $is_admin_user = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $is_auth_page = (strpos($current_page, 'login.php') !== false || strpos($current_page, 'daftar.php') !== false);
    
    if (!$is_admin_page && !$is_admin_user && !$is_auth_page): 
  ?>
  <footer class="main-footer">
    <div class="container">
      <div class="footer-grid">
        <!-- Col 1: Brand -->
        <div class="footer-col">
          <?php
            // Fetch lapangan info just for the footer
            $conn_ft = mysqli_connect("localhost", "root", "", "db_futsal");
            $lap_ft = mysqli_fetch_assoc(mysqli_query($conn_ft, "SELECT * FROM lapangan LIMIT 1"));
            mysqli_close($conn_ft);
          ?>
          <div style="font-size:1.5rem; font-weight:800; color:var(--primary); margin-bottom:1rem; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-futbol"></i> <?= htmlspecialchars($lap_ft['nama'] ?? 'FutsalPOMPemenang') ?>
          </div>
          <p style="font-size:0.9rem; color:#9ca3af; line-height:1.6;">
            Penyedia layanan booking lapangan futsal terbaik dengan fasilitas lengkap dan sistem pemesanan online yang mudah dan cepat.
          </p>
        </div>

        <!-- Col 2: Quick Links -->
        <div class="footer-col">
          <h4>Tautan Cepat</h4>
          <ul class="footer-links">
            <li><a href="<?= $base_url ?>index.php">Beranda</a></li>
            <li><a href="<?= $base_url ?>pages/user/booking.php">Jadwal Lapangan</a></li>
            <li><a href="<?= $base_url ?>pages/user/riwayat.php">Riwayat Pesanan</a></li>
          </ul>
        </div>

        <!-- Col 3: Contact -->
        <div class="footer-col">
          <h4>Hubungi Kami</h4>
          <div class="footer-contact">
            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lap_ft['alamat'] ?? 'Jl. kantor desa pemenang') ?></p>
            <p><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($lap_ft['no_handphone'] ?? '+62 812-3456-7890') ?></p>
            <p><i class="fas fa-envelope"></i> info@futsalpompemenang.com</p>
            <p><i class="fas fa-clock"></i> 10:00 - 23:00 WITA</p>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> FutsalPOMPemenang. All rights reserved.</p>
      </div>
    </div>
  </footer>
  <?php endif; ?>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= $base_url ?? '' ?>assets/js/main.js"></script>
  <?php display_flash_message(); ?>
</body>
</html>
