<?php
require_once __DIR__ . '/penjaga.php';

$page_title = 'Pengaturan Profil Admin';
$base_url   = '../../';
$user_id    = (int)$_SESSION['user_id'];

// Fetch user data
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = $user_id"));

include '../../includes/header.php';
?>

<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-weight: 800; color: var(--dark);">Profil Admin</h1>
            <p style="color: var(--text-muted);">Perbarui informasi kredensial login Anda.</p>
        </div>

        <div class="card" style="background: white; padding: 2.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); max-width: 600px;">
            <form action="../../actions/autentikasi.php" method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($u['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Handphone</label>
                    <input type="text" name="no_handphone" class="form-input" value="<?= htmlspecialchars($u['no_handphone']) ?>" required>
                </div>

                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border);">
                
                <div style="margin-bottom: 2rem;">
                    <h4 style="font-weight: 700; margin-bottom: 0.5rem;">Ubah Password</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Biarkan kosong jika Anda tidak ingin mengganti password login admin.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div style="position:relative;">
                        <input type="password" name="new_password" id="new_password" class="form-input" placeholder="Masukkan password baru" minlength="2">
                        <button type="button" onclick="togglePassword()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; margin-top: 1rem;">Simpan Perubahan Profil</button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('new_password');
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
