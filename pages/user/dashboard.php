<?php
require_once __DIR__ . '/../../config/database.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../../login.php'); exit;
}

$page_title = 'User Dashboard';
$base_url   = '../../';
$user_id    = (int)$_SESSION['user_id'];

// Stats from PEMESANAN
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_pembayaran='lunas' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status_pembayaran='pending' THEN 1 ELSE 0 END) as pending
    FROM pemesanan WHERE id_pelanggan = $user_id
"));

// Recent bookings
$recent_bookings = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, l.nama as lapangan_nama, j.jam_mulai, j.jam_selesai
    FROM pemesanan p
    JOIN lapangan l ON p.id_lapangan = l.id_lapangan
    JOIN jadwal_lapangan j ON p.id_jadwal = j.id_jadwal
    WHERE p.id_pelanggan = $user_id
    ORDER BY p.id_pemesanan DESC LIMIT 5
"), MYSQLI_ASSOC);

include '../../includes/header.php';
include '../../includes/navigasi_user.php';
?>

<div class="container" style="padding: 2rem 1rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-weight: 800; color: var(--dark); font-size: 1.5rem;">Halo, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Selamat datang di dashboard Anda. Cek status booking Anda di sini.</p>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Total Booking</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $stats['total'] ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Booking Lunas</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--success);"><?= $stats['confirmed'] ?? 0 ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Menunggu Pembayaran</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--warning);"><?= $stats['pending'] ?? 0 ?></div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-weight: 700;">Booking Terbaru</h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: #f9fafb; border-bottom: 1px solid var(--border);">
                    <tr>
                        <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Tanggal</th>
                        <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Waktu</th>
                        <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Durasi</th>
                        <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_bookings)): ?>
                    <tr>
                        <td colspan="4" style="padding: 3rem; text-align: center; color: var(--text-muted);">Anda belum memiliki riwayat booking.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_bookings as $b): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem; font-weight: 500;"><?= date('d M Y', strtotime($b['tanggal'])) ?></td>
                        <td style="padding: 1rem;"><?= substr($b['jam_mulai'],0,5) ?> - <?= substr($b['jam_selesai'],0,5) ?></td>
                        <td style="padding: 1rem;"><?= $b['durasi'] ?> Jam</td>
                        <td style="padding: 1rem;">
                            <?php if ($b['status_pembayaran'] === 'lunas'): ?>
                                <span class="badge badge-available">Lunas</span>
                            <?php elseif ($b['status_pembayaran'] === 'pending'): ?>
                                <span class="badge" style="background: var(--warning);">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-taken">Ditolak</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
