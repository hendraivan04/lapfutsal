<?php
// includes/navigasi_admin.php
$current_page = basename($_SERVER['PHP_SELF']);
$is_data_master = in_array($current_page, ['jadwal.php', 'pengguna.php', 'lapangan.php']);
?>
<div class="admin-sidebar">
    <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; color: var(--primary);">
        <i class="fas fa-shield-alt" style="font-size: 1.5rem;"></i>
        <span class="admin-brand-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: -0.5px; color: white;">Admin Lapangan</span>
    </div>

    <a href="profil.php" class="admin-nav-link <?= $current_page == 'profil.php' ? 'active' : '' ?>">
        <i class="fas fa-user-cog"></i> <span>Profil Saya</span>
    </a>
    <a href="dashboard.php" class="admin-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> <span>Dashboard</span>
    </a>

    <!-- Dropdown Data Master -->
    <div style="cursor: pointer; margin-top: 0.5rem;" onclick="toggleDataMaster()">
        <div class="admin-nav-link <?= $is_data_master ? 'active' : '' ?>">
            <i class="fas fa-database"></i> <span style="white-space: nowrap;">Data Master</span>
            <i class="fas <?= $is_data_master ? 'fa-chevron-up' : 'fa-chevron-down' ?>" id="dm-icon" style="margin-left: auto; flex-shrink: 0;"></i>
        </div>
    </div>
    
    <div id="dm-menu" style="display: <?= $is_data_master ? 'block' : 'none' ?>; padding-left: 1rem; margin-bottom: 0.5rem; background: rgba(0,0,0,0.15); border-radius: 0.5rem;">
        <a href="jadwal.php" class="admin-nav-link <?= $current_page == 'jadwal.php' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i> <span style="white-space: nowrap;">Jadwal</span>
        </a>
        <a href="pengguna.php" class="admin-nav-link <?= $current_page == 'pengguna.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span style="white-space: nowrap;">Pengguna</span>
        </a>
        <a href="lapangan.php" class="admin-nav-link <?= $current_page == 'lapangan.php' ? 'active' : '' ?>">
            <i class="fas fa-building"></i> <span style="white-space: nowrap;">Lapangan</span>
        </a>
    </div>

    <?php
        $notif_q = mysqli_query($conn, "SELECT COUNT(*) as unread FROM pemesanan WHERE status_pembayaran = 'pending'");
        $notif_data = mysqli_fetch_assoc($notif_q);
        $admin_unread = $notif_data['unread'] ?? 0;
    ?>
    <a href="pemesanan.php" class="admin-nav-link <?= $current_page == 'pemesanan.php' ? 'active' : '' ?>" style="margin-top: 0.5rem;">
        <i class="fas fa-calendar-check"></i> 
        <span>Pesanan</span>
        <?php if ($admin_unread > 0): ?>
            <span style="background:var(--danger); color:white; font-size:0.7rem; padding:1px 6px; border-radius:10px; font-weight:700; margin-left:auto;">
                <?= $admin_unread ?>
            </span>
        <?php endif; ?>
    </a>
    <a href="laporan.php" class="admin-nav-link <?= $current_page == 'laporan.php' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice-dollar"></i> <span>Laporan</span>
    </a>
    
    <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="../../keluar.php" class="admin-nav-link" style="color: #ef4444;">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>

<script>
function toggleDataMaster() {
    const menu = document.getElementById('dm-menu');
    const icon = document.getElementById('dm-icon');
    if (menu.style.display === 'none') {
        menu.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
    } else {
        menu.style.display = 'none';
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
    }
}
</script>
