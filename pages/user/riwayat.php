<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../../login.php'); exit;
}

$page_title = 'Riwayat Booking';
$base_url   = '../../';
$user_id    = (int)$_SESSION['user_id'];

// Mark all current lunas/batal statuses as seen via cookie (persists across logout/login)
$all_status_q = mysqli_fetch_all(mysqli_query($conn, "
    SELECT id_pemesanan, status_pembayaran FROM pemesanan 
    WHERE id_pelanggan = $user_id AND status_pembayaran IN ('lunas','batal')
"), MYSQLI_ASSOC);
$seen_data = [];
foreach ($all_status_q as $row) {
    $seen_data[$row['id_pemesanan']] = $row['status_pembayaran'];
}
setcookie('seen_notif_' . $user_id, json_encode($seen_data), time() + 86400 * 365, '/');

// Filter
$filter = clean_input($_GET['status'] ?? 'all');
$where  = "WHERE p.id_pelanggan = $user_id";
if ($filter === 'pending') $where .= " AND p.status_pembayaran = 'pending'";
if ($filter === 'lunas')   $where .= " AND p.status_pembayaran = 'lunas'";

// Status filter applied

$bookings = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, l.nama as lapangan_nama, j.jam_mulai, j.jam_selesai
    FROM pemesanan p
    JOIN lapangan l ON p.id_lapangan = l.id_lapangan
    JOIN jadwal_lapangan j ON p.id_jadwal = j.id_jadwal
    $where
    ORDER BY p.id_pemesanan DESC
"), MYSQLI_ASSOC);

include '../../includes/header.php';
include '../../includes/navigasi_user.php';
?>

<div class="container" style="padding: 2rem 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-weight: 800; color: var(--dark); font-size: 1.5rem;">Riwayat Booking</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola pesanan lapangan Anda di sini.</p>
        </div>
        
        <div style="display: flex; gap: 0.5rem; background: #eee; padding: 0.25rem; border-radius: 9999px;">
            <a href="?status=all" class="btn <?= $filter == 'all' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1rem;">Semua</a>
            <a href="?status=pending" class="btn <?= $filter == 'pending' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1rem;">Pending</a>
            <a href="?status=lunas" class="btn <?= $filter == 'lunas' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1rem;">Lunas</a>
        </div>
    </div>

    <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid var(--border);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">ID</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">Jadwal</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">Lama Main</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">Status</th>
                        <th style="padding: 1rem; text-align: center; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="5" style="padding: 4rem; text-align: center; color: var(--text-muted);">Tidak ada riwayat booking ditemukan.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem; color: var(--text-muted); font-weight: 700;"><?= sprintf('%02d', $b['id_pemesanan']) ?></td>
                        <td style="padding: 1rem; white-space: nowrap;">
                            <div style="font-weight: 600;"><?= date('d M Y', strtotime($b['tanggal'])) ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= substr($b['jam_mulai'],0,5) ?> - <?= substr($b['jam_selesai'],0,5) ?></div>
                        </td>
                        <td style="padding: 1rem; white-space: nowrap;"><?= $b['durasi'] ?> Jam</td>
                        <td style="padding: 1rem; white-space: nowrap;">
                            <?php if ($b['status_pembayaran'] === 'lunas'): ?>
                                <span class="badge badge-available">Lunas</span>
                            <?php elseif ($b['status_pembayaran'] === 'pending'): ?>
                                <span class="badge" style="background: var(--warning);">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-taken">Ditolak</span>
                                <?php if (!empty($b['alasan_pembatalan'])): ?>
                                    <div style="font-size: 0.7rem; color: var(--danger); margin-top: 4px; max-width: 150px; line-height: 1.2; white-space: normal;">
                                        <strong>Alasan:</strong> <?= htmlspecialchars($b['alasan_pembatalan']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem; text-align: center; white-space: nowrap;">
                            <?php if ($b['status_pembayaran'] === 'pending'): ?>
                            <a href="../../actions/pemesanan_user.php?action=cancel&id_pemesanan=<?= $b['id_pemesanan'] ?>" 
                               class="btn btn-outline btn-sm" 
                               style="color: var(--danger); border-color: var(--danger); font-size: 0.75rem; padding: 0.3rem 0.6rem;"
                               onclick="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                Batal
                            </a>
                            <?php elseif ($b['status_pembayaran'] === 'lunas'): ?>
                            <a href="kwitansi.php?id_pemesanan=<?= $b['id_pemesanan'] ?>" target="_blank"
                               class="btn btn-outline btn-sm" 
                               style="color: var(--primary); border-color: var(--primary); font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; padding: 0.3rem 0.6rem;">
                                <i class="fas fa-print"></i> Kwitansi
                            </a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.75rem;">Selesai</span>
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
