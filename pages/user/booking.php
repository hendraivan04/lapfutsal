<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../../login.php'); exit;
}

$page_title = 'Booking Lapangan';
$base_url   = '../../';
$lid        = 1; // Only 1 field for now
$monday_ts  = strtotime('monday this week');
$today      = date('Y-m-d');
$days_data  = [];
$days_map   = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];

// Fetch lapangan info (to get price if needed, though we use time-based logic)
$lap_res = mysqli_query($conn, "SELECT * FROM lapangan WHERE id_lapangan = $lid");
$lapangan = mysqli_fetch_assoc($lap_res);

for ($i = 0; $i < 7; $i++) {
    $current_date = date('Y-m-d', strtotime("+$i days", $monday_ts));
    
    // Jika hari ini sudah lewat, geser ke minggu depan
    if ($current_date < $today) {
        $current_date = date('Y-m-d', strtotime('+7 days', strtotime($current_date)));
    }
    
    $day_name_en  = date('l', strtotime($current_date));
    $day_name_id  = $days_map[$day_name_en];
    
    // Fetch slots
    $slots_res = mysqli_query($conn, "SELECT * FROM jadwal_lapangan WHERE id_lapangan = $lid AND tanggal = '$current_date' ORDER BY jam_mulai ASC");
    $slots = [];
    if ($slots_res) {
        $slots = mysqli_fetch_all($slots_res, MYSQLI_ASSOC);
    }
    
    $days_data[] = [
        'date' => $current_date,
        'day_id' => $day_name_id,
        'day_num' => $i + 1,
        'slots' => $slots
    ];
}

include '../../includes/header.php';
include '../../includes/navigasi_user.php';
?>

<div class="container" style="padding: 2rem 1rem;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-weight: 800; color: var(--dark); font-size: 1.5rem;">Pilih Jadwal Bermain</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Klik pada jam yang bertanda "Tersedia" untuk melakukan booking.</p>
    </div>

    <div class="schedule-grid">
        <?php foreach ($days_data as $day): ?>
        <div class="day-card">
            <div class="day-header">
                <div class="day-number"><?= $day['day_num'] ?></div>
                <div class="day-name"><?= $day['day_id'] ?></div>
                <div style="margin-left: auto; font-size: 0.85rem; color: var(--text-muted);"><?= date('d M', strtotime($day['date'])) ?></div>
            </div>
            
            <div class="slot-list">
                <?php if (empty($day['slots'])): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 1rem 0;">Jadwal belum diatur.</p>
                <?php else: ?>
                    <?php foreach ($day['slots'] as $s): 
                        // Check if slot is in the past
                        $slot_time = strtotime($day['date'] . ' ' . $s['jam_mulai']);
                        $is_past = ($slot_time < time());
                        // Price logic: 18:00 onwards is 120k, else 100k
                        $hour = (int)substr($s['jam_mulai'], 0, 2);
                        $display_price = ($hour >= 18) ? '120k' : '100k';
                    ?>
                    <div class="slot-item" style="<?= $is_past ? 'opacity: 0.6;' : '' ?>">
                        <div class="slot-time" style="font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                            <div><?= substr($s['jam_mulai'], 0, 5) ?> - <?= substr($s['jam_selesai'], 0, 5) ?></div>
                            <div style="font-weight: 700; color: var(--primary); font-size: 0.8rem;">
                                Rp <?= $display_price ?>
                            </div>
                        </div>
                        <?php if ($is_past): ?>
                            <span class="badge" style="background: #94a3b8; cursor: not-allowed;">Lewat</span>
                        <?php elseif ($s['status'] === 'tersedia'): ?>
                            <a href="javascript:void(0)" 
                               onclick="confirmBooking(<?= $s['id_jadwal'] ?>, '<?= $day['day_id'] ?>', '<?= date('d M Y', strtotime($day['date'])) ?>', '<?= substr($s['jam_mulai'], 0, 5) ?>')"
                               class="badge badge-available" 
                               style="text-decoration: none; cursor: pointer; border: none;">
                                Tersedia
                            </a>
                        <?php else: ?>
                            <span class="badge badge-taken">Terisi</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function confirmBooking(jadwalId, day, date, time) {
    Swal.fire({
        title: 'Konfirmasi Booking',
        html: `Apakah Anda ingin memesan lapangan pada:<br><br><b>${day}, ${date}</b><br>Jam <b>${time}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Booking Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to checkout
            window.location.href = `checkout.php?id_jadwal=${jadwalId}`;
        }
    })
}
</script>

<?php include '../../includes/footer.php'; ?>
