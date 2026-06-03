<?php
require_once __DIR__ . '/penjaga.php';
$page_title = 'Manajemen Pesanan';
$base_url   = '../../';

$filter = clean_input($_GET['status'] ?? 'all');
$where  = "WHERE 1=1";
if ($filter === 'pending') $where .= " AND p.status_pembayaran = 'pending'";
if ($filter === 'lunas')   $where .= " AND p.status_pembayaran = 'lunas'";

// Status and filter handling completed

$bookings = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, u.nama as user_nama, u.no_handphone as user_phone, j.jam_mulai, j.jam_selesai
    FROM pemesanan p
    JOIN pelanggan u ON p.id_pelanggan = u.id_pelanggan
    JOIN jadwal_lapangan j ON p.id_jadwal = j.id_jadwal
    $where
    ORDER BY p.id_pemesanan DESC
"), MYSQLI_ASSOC);

include '../../includes/header.php';
?>
<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-weight: 800; color: var(--dark);">Manajemen Pesanan</h1>
                <p style="color: var(--text-muted);">Konfirmasi atau Tolak Pesanan dari Pelanggan.</p>
            </div>
            
            <div style="display: flex; gap: 0.5rem; background: #e5e7eb; padding: 0.25rem; border-radius: 9999px;">
                <a href="?status=all" class="btn <?= $filter == 'all' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1.2rem; border-radius: 9999px;">Semua</a>
                <a href="?status=pending" class="btn <?= $filter == 'pending' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1.2rem; border-radius: 9999px;">Pending</a>
                <a href="?status=lunas" class="btn <?= $filter == 'lunas' ? 'btn-primary' : '' ?>" style="font-size: 0.8rem; padding: 0.4rem 1.2rem; border-radius: 9999px;">Lunas</a>
            </div>
        </div>

        <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f9fafb; border-bottom: 1px solid var(--border);">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">User / HP</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">Jadwal</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-size: 0.85rem; font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="4" style="padding: 4rem; text-align: center; color: var(--text-muted);">Tidak ada pesanan ditemukan.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1.25rem;">
                                <div style="font-weight: 700;"><?= htmlspecialchars($b['nama_pemesan'] ?: $b['user_nama']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($b['no_wa'] ?: $b['user_phone']) ?></div>
                            </td>
                            <td style="padding: 1.25rem;">
                                <div style="font-weight: 600;"><?= date('d F Y', strtotime($b['tanggal'])) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= substr($b['jam_mulai'],0,5) ?> - <?= substr($b['jam_selesai'],0,5) ?></div>
                            </td>
                            <td style="padding: 1.25rem;">
                                <?php if ($b['bukti_pembayaran']): ?>
                                    <a href="../../assets/uploads/bukti/<?= $b['bukti_pembayaran'] ?>" target="_blank" class="badge" style="background: #6366f1; text-decoration: none;">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="badge" style="background: #cbd5e1;">No File</span>
                                <?php endif; ?>
                                <div style="margin-top: 0.5rem;">
                                    <?php if ($b['status_pembayaran'] === 'lunas'): ?>
                                        <span class="badge badge-available">Lunas</span>
                                    <?php elseif ($b['status_pembayaran'] === 'pending'): ?>
                                        <span class="badge" style="background: var(--warning);">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-taken">Tolak</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <?php if ($b['status_pembayaran'] === 'pending'): ?>
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <a href="../../actions/confirm_pesanan.php?id_pemesanan=<?= $b['id_pemesanan'] ?>" 
                                       class="btn btn-primary" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 4px;">
                                        Konfirmasi
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline" 
                                            style="font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 4px; color: var(--danger); border-color: var(--danger);"
                                            onclick="requestCancelReason(<?= $b['id_pemesanan'] ?>)">
                                        Tolak
                                    </button>
                                </div>

                                <?php else: ?>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: center;">
                                        <?php if ($b['status_pembayaran'] === 'lunas'): ?>
                                            <a href="../user/kwitansi.php?id_pemesanan=<?= $b['id_pemesanan'] ?>" target="_blank"
                                               class="btn btn-outline" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 4px; color: var(--primary); border-color: var(--primary); display: flex; align-items: center; gap: 5px;">
                                                <i class="fas fa-print"></i> Kwitansi
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">Selesai</span>
                                        <?php endif; ?>
                                        
                                        <a href="../../actions/delete_pesanan.php?id_pemesanan=<?= $b['id_pemesanan'] ?>" 
                                           class="btn btn-outline" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 4px; color: #64748b;"
                                           onclick="return confirm('Hapus permanen data pesanan ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
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
</div>

    <!-- Form Tersembunyi untuk Penolakan -->
    <form id="cancelForm" action="../../actions/cancel_pesanan.php" method="POST" style="display: none;">
        <input type="hidden" name="id_pemesanan" id="cancel_id">
        <input type="hidden" name="alasan" id="cancel_reason">
    </form>

    <script>
    function requestCancelReason(id) {
        Swal.fire({
            title: 'Alasan Penolakan',
            input: 'textarea',
            inputPlaceholder: 'Ketik alasan penolakan di sini...',
            inputAttributes: {
                'aria-label': 'Ketik alasan penolakan di sini'
            },
            showCancelButton: true,
            confirmButtonText: 'Tolak Pesanan',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#ef4444',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan harus diisi!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel_id').value = id;
                document.getElementById('cancel_reason').value = result.value;
                document.getElementById('cancelForm').submit();
            }
        })
    }
    </script>
<?php include '../../includes/footer.php'; ?>
