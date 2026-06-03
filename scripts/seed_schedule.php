<?php
/**
 * scripts/seed_schedule.php
 * Helper script to populate jadwal_lapangan for the current week
 */
require_once __DIR__ . '/../config/database.php';

// 10:00 to 23:00
$start_h = 10;
$end_h   = 23;

// Lapangan Utama ID = 1
$lid = 1;

// Monday to Sunday dates for this week
$today = date('Y-m-d');
$monday = date('Y-m-d', strtotime('monday this week'));

$days_map = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("$monday +$i days"));
    $day_name = $days_map[$i];

    for ($h = $start_h; $h < $end_h; $h++) {
        $start_time = sprintf('%02d:00:00', $h);
        $end_time   = sprintf('%02d:00:00', $h + 1);
        $label      = sprintf('%02d:00 - %02d:00', $h, $h + 1);

        // Check exists
        $check = mysqli_query($conn, "SELECT id_jadwal FROM jadwal_lapangan WHERE id_lapangan=$lid AND tanggal='$date' AND jam_mulai='$start_time'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO jadwal_lapangan (id_lapangan, tanggal, hari, jam_mulai, jam_selesai, status) 
                                 VALUES ($lid, '$date', '$day_name', '$start_time', '$end_time', 'tersedia')");
        }
    }
}

echo "Schedule seeded for Lapangan Utama (10:00 - 23:00) for this week.";
?>
