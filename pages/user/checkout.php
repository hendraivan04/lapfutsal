<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../../login.php'); exit;
}

$jid = (int)($_GET['id_jadwal'] ?? 0);
if (!$jid) {
    header('Location: booking.php'); exit;
}

// Fetch slot info
$jres = mysqli_query($conn, "SELECT j.*, l.nama as lapangan_nama, l.alamat as lapangan_alamat
                             FROM jadwal_lapangan j
                             JOIN lapangan l ON j.id_lapangan = l.id_lapangan
                             WHERE j.id_jadwal = $jid AND j.status = 'tersedia'");
$j = mysqli_fetch_assoc($jres);

if (!$j) {
    set_flash_message('error', 'Jadwal tidak tersedia.');
    header('Location: booking.php'); exit;
}

// Check if slot is in the past
$slot_time = strtotime($j['tanggal'] . ' ' . $j['jam_mulai']);
if ($slot_time < time()) {
    set_flash_message('error', 'Jadwal sudah kedaluwarsa.');
    header('Location: booking.php'); exit;
}

// Calculate price for display
$hour = (int)substr($j['jam_mulai'], 0, 2);
$harga = ($hour >= 18) ? 120000 : 100000;

$page_title = 'Administrasi Booking';
$base_url   = '../../';
$u_name = $_SESSION['user_name'];
$u_id   = $_SESSION['user_id'];

// Get user phone
$ures = mysqli_query($conn, "SELECT no_handphone FROM pelanggan WHERE id_pelanggan = $u_id");
$u = mysqli_fetch_assoc($ures);
$u_phone = $u['no_handphone'] ?? '';

include '../../includes/header.php';
include '../../includes/navigasi_user.php';
?>

<div class="container" style="padding: 2rem 1rem; max-width: 700px;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-weight: 800; color: var(--dark); font-size: 1.5rem;">Selesaikan Pemesanan</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Silakan isi detail administrasi dan lampirkan bukti pembayaran.</p>
    </div>

    <!-- Summary Box -->
    <div class="card" style="background: #f0f7ff; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #bfdbfe; margin-bottom: 2rem;">
        <h4 style="margin-bottom: 0.5rem; color: var(--primary);">Ringkasan Jadwal:</h4>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>
                <small style="color: var(--text-muted);">Lapangan:</small>
                <div style="font-weight: 700;"><?= $j['lapangan_nama'] ?></div>
            </div>
            <div>
                <small style="color: var(--text-muted);">Tanggal:</small>
                <div style="font-weight: 700;"><?= date('d M Y', strtotime($j['tanggal'])) ?></div>
            </div>
            <div>
                <small style="color: var(--text-muted);">Waktu:</small>
                <div style="font-weight: 700;"><?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?></div>
            </div>
            <div>
                <small style="color: var(--text-muted);">Total Bayar:</small>
                <div style="font-weight: 700; color: var(--primary);">Rp <?= number_format($harga, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <div class="card" style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <form action="../../actions/pemesanan_user.php?action=create" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_jadwal" value="<?= $jid ?>">
            
            <div class="form-group">
                <label class="form-label">Nama Pemesan</label>
                <input type="text" name="nama_pemesan" class="form-input" value="<?= htmlspecialchars($u_name) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" name="no_wa" class="form-input" value="<?= htmlspecialchars($u_phone) ?>" required>
            </div>

            <div class="form-group" style="background: #fff9f0; padding: 1.25rem; border-radius: 0.5rem; border: 1px solid #ffedd5; margin: 2rem 0;">
                <label class="form-label" style="color: #9a3412;">
                    <i class="fas fa-info-circle"></i> Unggah Bukti Pembayaran
                </label>
                <p style="font-size: 0.8rem; color: #9a3412; margin-bottom: 1rem;">
                    Silakan transfer pembayaran sebesar <strong>Rp <?= number_format($harga, 0, ',', '.') ?></strong> ke rekening berikut:<br>
                    <strong>BANK BCA: 123-456-7890 (a.n. Lapangan Futsal)</strong><br>
                    Lalu upload foto bukti transfer di bawah ini.
                </p>
                <input type="file" name="bukti_pembayaran" class="form-input" style="background: white;" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                <i class="fas fa-check-circle"></i> Konfirmasi & Booking
            </button>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="booking.php" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
