<?php
require_once __DIR__ . '/penjaga.php';
$page_title = 'Kelola Jadwal';
$base_url   = '../../';

// Get dates for this week
$monday_ts = strtotime('monday this week');
$today     = date('Y-m-d');
$days_data = [];
$days_map  = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];

for ($i = 0; $i < 7; $i++) {
    $current_date = date('Y-m-d', strtotime("+$i days", $monday_ts));
    
    // Jika hari ini sudah lewat, geser ke minggu depan
    if ($current_date < $today) {
        $current_date = date('Y-m-d', strtotime('+7 days', strtotime($current_date)));
    }
    
    $day_name_en  = date('l', strtotime($current_date));
    $day_name_id  = $days_map[$day_name_en];
    
    $slots_res = mysqli_query($conn, "SELECT * FROM jadwal_lapangan WHERE tanggal = '$current_date' ORDER BY jam_mulai ASC");
    $slots = [];
    if ($slots_res) {
        $slots = mysqli_fetch_all($slots_res, MYSQLI_ASSOC);
    }
    
    $days_data[] = [
        'date' => $current_date,
        'day_id' => $day_name_id,
        'slots' => $slots
    ];
}

include '../../includes/header.php';
?>
<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-weight: 800; color: var(--dark);">Kelola Jadwal Mingguan</h1>
                <p style="color: var(--text-muted);">Atur ketersediaan slot waktu. Hari yang sudah lewat otomatis menampilkan jadwal minggu depan.</p>
            </div>
            <a href="../../update_status_jadwal.php" class="btn btn-primary">Generate Slot (2 Minggu)</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($days_data as $day): ?>
            <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); padding: 1.5rem;">
                <div style="font-weight: 800; font-size: 1.1rem; border-bottom: 2px solid var(--primary); display: inline-block; margin-bottom: 1.5rem;">
                    <?= $day['day_id'] ?>, <?= date('d M', strtotime($day['date'])) ?>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php if (empty($day['slots'])): ?>
                        <div style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 1rem;">Slot belum digenerate.</div>
                    <?php else: ?>
                        <?php foreach ($day['slots'] as $s): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid var(--border);">
                            <div style="font-size: 0.85rem; font-weight: 600;">
                                <?= substr($s['jam_mulai'],0,5) ?> - <?= substr($s['jam_selesai'],0,5) ?>
                            </div>
                            <div>
                                <?php if ($s['status'] === 'tersedia'): ?>
                                    <a href="../../actions/jadwal_admin.php?id_jadwal=<?= $s['id_jadwal'] ?>&status=tidak tersedia" class="badge badge-available" style="text-decoration: none; font-size: 0.7rem;">Aktif</a>
                                <?php else: ?>
                                    <a href="../../actions/jadwal_admin.php?id_jadwal=<?= $s['id_jadwal'] ?>&status=tersedia" class="badge badge-taken" style="text-decoration: none; font-size: 0.7rem;">Non-aktif/Terisi</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
