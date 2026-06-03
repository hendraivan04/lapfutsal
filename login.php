<?php
require_once __DIR__ . '/config/database.php';
$page_title = 'Masuk';
$base_url   = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') header('Location: pages/admin/dashboard.php');
    else header('Location: pages/user/dashboard.php');
    exit;
}

include 'includes/header.php';
include 'includes/navigasi_user.php';
?>
<div class="auth-container">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:2rem;">
      <h2 style="font-weight:800;font-size:1.75rem;color:var(--dark);">Selamat Datang</h2>
      <p style="color:var(--text-muted);font-size:0.9rem;">Masuk untuk mulai booking lapangan</p>
    </div>

    <form action="actions/autentikasi.php" method="POST">
      <input type="hidden" name="action" value="login">
      
      <div class="form-group">
        <label class="form-label">Username</label>
        <div style="position:relative;">
          <i class="fas fa-user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.9rem;"></i>
          <input type="text" name="username" class="form-input" placeholder="Masukkan Username" style="padding-left:38px;" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div style="position:relative;">
          <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.9rem;"></i>
          <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" style="padding-left:38px;" required>
          <button type="button" onclick="togglePassword()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;padding:0.8rem;">Masuk Sekaranag</button>
      
      <div style="text-align:center;margin-top:1.5rem;font-size:0.9rem;color:var(--text-muted);">
        Belum punya akun? <a href="daftar.php" style="color:var(--primary);font-weight:600;text-decoration:none;">Daftar di sini</a>
      </div>
      
      <div style="text-align:center;margin-top:1rem;">
        <a href="pages/admin/login.php" style="font-size:0.8rem;color:var(--text-muted);text-decoration:none;">Masuk sebagai Admin</a>
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

<?php include 'includes/footer.php'; ?>
