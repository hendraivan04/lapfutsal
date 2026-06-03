<?php
require_once __DIR__ . '/penjaga.php';

$page_title = 'Pengaturan Lapangan';
$base_url   = '../../';

// Ambil data lapangan pertama (karena hanya ada 1 lapangan di sistem ini)
$lapangan_res = mysqli_query($conn, "SELECT * FROM lapangan LIMIT 1");
$lapangan = mysqli_fetch_assoc($lapangan_res);

// Jika tabel lapangan masih kosong secara tidak sengaja, buat default dummy
if (!$lapangan) {
    mysqli_query($conn, "INSERT INTO lapangan (nama, alamat, no_handphone) VALUES ('Lapangan Futsal Default', 'Alamat Belum Diatur', '081234567890')");
    $lapangan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM lapangan LIMIT 1"));
}

include '../../includes/header.php';
?>

<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-weight: 800; color: var(--dark);">Pengaturan Lapangan</h1>
            <p style="color: var(--text-muted);">Ubah nama tempat futsal, alamat, dan pengaturan lainnya yang akan tampil di halaman depan website.</p>
        </div>

        <div class="card" style="background: white; padding: 2.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); max-width: 600px;">
            <form action="../../actions/update_lapangan.php" method="POST">
                <input type="hidden" name="id_lapangan" value="<?= $lapangan['id_lapangan'] ?>">
                
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--dark);">Nama Lapangan / Tempat Futsal <span style="color: red;">*</span></label>
                    <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($lapangan['nama']) ?>" required>
                    <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 4px;">Nama ini akan muncul sebagai judul dan sambutan di halaman utama / beranda.</small>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label class="form-label" style="font-weight: 600; color: var(--dark);">Alamat Lengkap <span style="color: red;">*</span></label>
                    <textarea name="alamat" class="form-input" rows="3" required><?= htmlspecialchars($lapangan['alamat']) ?></textarea>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label class="form-label" style="font-weight: 600; color: var(--dark);">No Whatsapp / Handphone Penghubung</label>
                    <input type="text" name="no_handphone" class="form-input" value="<?= htmlspecialchars($lapangan['no_handphone'] ?? '') ?>" placeholder="08...">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; margin-top: 2rem; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
