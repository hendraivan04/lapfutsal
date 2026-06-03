<?php
require_once __DIR__ . '/config/database.php';
$page_title = 'Daftar Akun';
$base_url   = '';

include 'includes/header.php';
include 'includes/navigasi_user.php';
?>
<div class="auth-container" style="padding-top:6rem;align-items:flex-start;min-height:100vh;">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:2rem;">
      <h2 style="font-weight:800;font-size:1.75rem;color:var(--dark);">Daftar Akun</h2>
      <p style="color:var(--text-muted);font-size:0.9rem;">Mulai pengalaman baru bermain futsal</p>
    </div>

    <form action="actions/autentikasi.php" method="POST">
      <input type="hidden" name="action" value="register">
      
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="nama" class="form-input" placeholder="Buat Username unik" required>
      </div>

      <div class="form-group">
        <label class="form-label">Nomor Handphone</label>
        <input type="text" name="no_handphone" class="form-input" placeholder="Contoh: 08123456789" required>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div style="position:relative;">
          <input type="password" name="password" id="password" class="form-input" placeholder="Minimal 2 karakter" required minlength="2">
          <button type="button" onclick="togglePassword('password', 'toggleIcon1')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
            <i class="fas fa-eye" id="toggleIcon1"></i>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <div style="position:relative;">
          <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Ulangi password" required minlength="2">
          <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
            <i class="fas fa-eye" id="toggleIcon2"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;padding:0.8rem;">Buat Akun Sekarang</button>
      
      <div style="text-align:center;margin-top:1.5rem;font-size:0.9rem;color:var(--text-muted);">
        Sudah punya akun? <a href="login.php" style="color:var(--primary);font-weight:600;text-decoration:none;">Masuk di sini</a>
      </div>
    </form>
  </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const pwd = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
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
