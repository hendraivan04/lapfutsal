<?php
require_once __DIR__ . '/../../config/database.php';
$page_title = 'Admin Login';
$base_url   = '../../';

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php'); exit;
}

include '../../includes/header.php';
?>
<div class="auth-container" style="background: #f0f2f5;">
  <div class="auth-card" style="border-top: 5px solid var(--primary);">
    <div style="text-align:center;margin-bottom:2rem;">
      <i class="fas fa-shield-alt" style="font-size:3rem;color:var(--primary);margin-bottom:1rem;"></i>
      <h2 style="font-weight:800;font-size:1.5rem;color:var(--dark);">Admin Lapangan</h2>
      <p style="color:var(--text-muted);">Masuk untuk mengelola sistem FutsalPOMPemenang</p>
    </div>

    <form action="../../actions/autentikasi.php" method="POST">
      <input type="hidden" name="action" value="admin_login">
      
      <div class="form-group">
        <label class="form-label">Username Admin</label>
        <input type="text" name="username" class="form-input" placeholder="Masukkan Username Admin" required>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div style="position:relative;">
          <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
          <button type="button" onclick="togglePassword()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;padding:0.8rem;border-radius:0.5rem;">Login ke Dashboard</button>
      
      <div style="text-align:center;margin-top:1.5rem;">
        <a href="../../index.php" style="font-size:0.85rem;color:var(--text-muted);text-decoration:none;">
          <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
      </div>
    </form>
  </div>
</div>
<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
