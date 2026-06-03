<?php
require_once __DIR__ . '/config/database.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak. Silakan login sebagai admin.");
}

// 10:00 to 23:00
$start_h = 10;
$end_h   = 23;
$lid     = 1; // Lapangan Utama

// Get Monday of this week
$monday = date('Y-m-d', strtotime('monday this week'));
$days_map = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

echo "<h3>Proses Jadwal Lapangan Futsal</h3>";

// Generate untuk 2 minggu (minggu ini + minggu depan)
$weeks = [
    ['label' => 'Minggu Ini', 'start' => $monday],
    ['label' => 'Minggu Depan', 'start' => date('Y-m-d', strtotime("$monday +7 days"))]
];

foreach ($weeks as $week) {
    echo "<h4>{$week['label']}</h4>";
    echo "<ul>";
    
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("{$week['start']} +$i days"));
        $day_name = $days_map[$i];

        for ($h = $start_h; $h < $end_h; $h++) {
            $start_time = sprintf('%02d:00:00', $h);
            $end_time   = sprintf('%02d:00:00', $h + 1);
            
            $check = mysqli_query($conn, "SELECT id_jadwal FROM jadwal_lapangan WHERE id_lapangan=$lid AND tanggal='$date' AND jam_mulai='$start_time'");
            if (mysqli_num_rows($check) == 0) {
                $q = "INSERT INTO jadwal_lapangan (id_lapangan, tanggal, hari, jam_mulai, jam_selesai, status) 
                      VALUES ($lid, '$date', '$day_name', '$start_time', '$end_time', 'tersedia')";
                mysqli_query($conn, $q);
                echo "<li><strong>Berhasil:</strong> Slot $start_time - $end_time untuk $date ($day_name) ditambahkan.</li>";
            } else {
                echo "<li><em>Sudah Ada:</em> Slot $start_time pada $date sudah tersedia di database.</li>";
            }
        }
    }
    echo "</ul>";
}

echo "<h4>Selesai! <a href='pages/admin/jadwal.php'>Kembali ke Kelola Jadwal</a></h4>";
?>
