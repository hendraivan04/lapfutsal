<?php
require_once __DIR__ . '/penjaga.php';
$page_title = 'Kelola Pengguna';
$base_url   = '../../';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM pelanggan WHERE id_pelanggan=$id");
    set_flash_message('success', 'Pengguna berhasil dihapus.');
    header('Location: pengguna.php'); exit;
}

$users = mysqli_fetch_all(mysqli_query($conn, "
    SELECT u.*, (SELECT COUNT(*) FROM pemesanan WHERE id_pelanggan=u.id_pelanggan) as total_booking 
    FROM pelanggan u ORDER BY id_pelanggan DESC
"), MYSQLI_ASSOC);

include '../../includes/header.php';
?>
<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-weight: 800; color: var(--dark);">Kelola Pengguna</h1>
            <p style="color: var(--text-muted);">Daftar member terdaftar di sistem.</p>
        </div>

        <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f9fafb;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">Nama</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">Username</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem;">No. HP</th>
                            <th style="padding: 1rem; text-align: center; font-size: 0.85rem;">Total Booking</th>
                            <th style="padding: 1rem; text-align: center; font-size: 0.85rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary);">
                                        <?= strtoupper(substr($u['nama'],0,1)) ?>
                                    </div>
                                    <span style="font-weight: 600;"><?= htmlspecialchars($u['nama']) ?></span>
                                </div>
                            </td>
                            <td style="padding: 1.25rem;">
                                <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; color: #475569;"><?= htmlspecialchars($u['username']) ?></code>
                            </td>
                            <td style="padding: 1.25rem;"><?= htmlspecialchars($u['no_handphone']) ?></td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <span class="badge" style="background: var(--light); color: var(--dark); font-weight: 600;"><?= $u['total_booking'] ?></span>
                            </td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <a href="?delete=<?= $u['id_pelanggan'] ?>" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); font-size: 0.75rem;" onclick="return confirm('Hapus user ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
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
