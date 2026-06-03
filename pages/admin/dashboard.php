<?php
require_once __DIR__ . '/penjaga.php';
$page_title = 'Admin Dashboard';
$base_url   = '../../';

// Admin stats
$q_bookings = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pemesanan");
$q_users    = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pelanggan");
$q_pending  = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pemesanan WHERE status_pembayaran='pending'");
$q_revenue  = mysqli_query($conn, "SELECT SUM(total_harga) as rev FROM pemesanan WHERE status_pembayaran='lunas'");

$stats = [
    'bookings' => ($q_bookings ? mysqli_fetch_assoc($q_bookings)['cnt'] : 0),
    'users'    => ($q_users ? mysqli_fetch_assoc($q_users)['cnt'] : 0),
    'pending'  => ($q_pending ? mysqli_fetch_assoc($q_pending)['cnt'] : 0),
    'revenue'  => ($q_revenue ? mysqli_fetch_assoc($q_revenue)['rev'] : 0) ?? 0
];

// Recent Bookings
$q_recent = mysqli_query($conn, "
    SELECT p.*, u.nama as user_nama, l.nama as lapangan_nama, j.jam_mulai, j.jam_selesai
    FROM pemesanan p
    JOIN pelanggan u ON p.id_pelanggan = u.id_pelanggan
    JOIN lapangan l ON p.id_lapangan = l.id_lapangan
    JOIN jadwal_lapangan j ON p.id_jadwal = j.id_jadwal
    ORDER BY p.id_pemesanan DESC LIMIT 5
");
$recent_bookings = $q_recent ? mysqli_fetch_all($q_recent, MYSQLI_ASSOC) : [];


include '../../includes/header.php';
?>
<div class="admin-layout">
    <!-- Sidebar -->
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <!-- Main Content -->
    <div style="flex: 1; padding: 2rem;">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-weight: 800; color: var(--dark);">Dashboard Admin</h1>
            <p style="color: var(--text-muted);">Ringkasan aktivitas sistem booking Anda.</p>
        </div>

        <!-- Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Total Pesanan</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $stats['bookings'] ?></div>
            </div>
            <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Sedang Menunggu</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--warning);"><?= $stats['pending'] ?></div>
            </div>
            <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Member Terdaftar</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--success);"><?= $stats['users'] ?></div>
            </div>
            <div class="card" style="padding: 1.5rem; background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Total Pendapatan</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--dark);">Rp <?= number_format($stats['revenue'], 0, ',', '.') ?></div>
            </div>
        </div>



        <!-- Recent Table -->
        <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border);">
                <h3 style="font-weight: 700;">Pesanan Terbaru</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f9fafb;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">User</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">Jadwal</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $b): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($b['user_nama']) ?></div>
                            </td>
                            <td style="padding: 1rem;">
                                <div><?= date('d M Y', strtotime($b['tanggal'])) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= substr($b['jam_mulai'],0,5) ?> - <?= substr($b['jam_selesai'],0,5) ?></div>
                            </td>
                            <td style="padding: 1rem;">
                                <?php if ($b['status_pembayaran'] === 'lunas'): ?>
                                    <span class="badge badge-available">Lunas</span>
                                <?php elseif ($b['status_pembayaran'] === 'pending'): ?>
                                    <span class="badge" style="background: var(--warning);">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-taken">Batal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
