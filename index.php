<?php
require_once __DIR__ . '/config/database.php';
$page_title = 'Beranda';
$base_url   = '';

// Fetch the only field (Lapangan Utama)
$lapangan_res = mysqli_query($conn, "SELECT * FROM lapangan LIMIT 1");
$lapangan     = mysqli_fetch_assoc($lapangan_res);

// If no field exists, use default info
$lapangan_id   = $lapangan['id_lapangan'] ?? 1;
$lapangan_nama = $lapangan['nama'] ?? 'Lapangan Pom Pemenang';

// Get current week dates
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
    
    // Fetch slots for this day
    $slots_res = mysqli_query($conn, "SELECT * FROM jadwal_lapangan WHERE id_lapangan = $lapangan_id AND tanggal = '$current_date' ORDER BY jam_mulai ASC");
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

include 'includes/header.php';
include 'includes/navigasi_user.php';
?>

<!-- ── HERO SECTION ───────────────────────────────────────── -->
<section class="hero">
  <div class="container">
      <div style="max-width: 800px; margin: 0 auto;">
      <h1>Selamat Datang di <?= htmlspecialchars($lapangan_nama) ?></h1>
      <p>Booking Lapangan Futsal Nyaman, Strategis, dan Terjangkau di Pusat Kota</p>
      <a href="#status" class="btn btn-primary" style="padding: 0.8rem 2.5rem; font-size: 1rem;">
        Cari Jadwal Sekarang
      </a>
    </div>
  </div>
</section>

<?php
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

// Baca data fasilitas dari file JSON (tanpa database)
$json_file = __DIR__ . '/data/fasilitas.json';
$fasilitas_list = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];
?>

<!-- ── FASILITAS SECTION ──────────────────────────────────── -->
<section class="container" style="padding: 4rem 1rem 2rem;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <h2 class="section-title">Fasilitas Kami</h2>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0.5rem auto 0;">Nikmati berbagai fasilitas pendukung untuk kenyamanan bermain Anda</p>
    </div>
    
    <div class="facility-gallery">
        <?php foreach ($fasilitas_list as $f): ?>
        <div style="background: var(--card-bg, #fff); border-radius: 16px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: default; position: relative;"
             onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 32px rgba(0,0,0,0.12)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="position: relative; height: 170px; overflow: hidden;">
                <img src="<?= htmlspecialchars($f['gambar']) ?>" alt="<?= htmlspecialchars($f['nama']) ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                     onmouseover="this.style.transform='scale(1.08)';"
                     onmouseout="this.style.transform='scale(1)';">
            </div>
            <div style="padding: 1.2rem 1.3rem 1.4rem;">
                <h3 style="margin: 0 0 0.4rem; font-size: 1.05rem; font-weight: 700; color: var(--text, #1e293b);"><?= htmlspecialchars($f['nama']) ?></h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted, #64748b); line-height: 1.5;"><?= htmlspecialchars($f['deskripsi'] ?? '') ?></p>
            </div>

            <?php if ($is_admin): ?>
            <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 6px;">
                <button onclick='openEditFasilitas(<?= htmlspecialchars(json_encode($f, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)' 
                        style="width: 34px; height: 34px; border-radius: 8px; border: none; background: rgba(255,255,255,0.9); color: #6366f1; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: background 0.2s;"
                        onmouseover="this.style.background='#6366f1'; this.style.color='#fff';"
                        onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.color='#6366f1';"
                        title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <a href="actions/delete_fasilitas.php?id=<?= $f['id'] ?>" 
                   onclick="return confirm('Yakin hapus fasilitas ini?')"
                   style="width: 34px; height: 34px; border-radius: 8px; border: none; background: rgba(255,255,255,0.9); color: #ef4444; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); text-decoration: none; transition: background 0.2s;"
                   onmouseover="this.style.background='#ef4444'; this.style.color='#fff';"
                   onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.color='#ef4444';"
                   title="Hapus">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if ($is_admin && count($fasilitas_list) < 8): ?>
        <div onclick="openAddFasilitas()" 
             style="background: var(--card-bg, #fff); border-radius: 16px; overflow: hidden; border: 2px dashed var(--border, #e2e8f0); min-height: 260px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: border-color 0.3s, background 0.3s;"
             onmouseover="this.style.borderColor='var(--primary, #6366f1)'; this.style.background='rgba(99,102,241,0.04)';"
             onmouseout="this.style.borderColor='var(--border, #e2e8f0)'; this.style.background='var(--card-bg, #fff)';">
            <i class="fas fa-plus-circle" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary, #6366f1); opacity: 0.5;"></i>
            <span style="font-weight: 600; font-size: 0.95rem; color: var(--text-muted); opacity: 0.7;">Tambah Fasilitas</span>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($is_admin): ?>
<!-- Modal Tambah Fasilitas -->
<div id="modalAddFasilitas" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card" style="position: relative; max-width: 450px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <h3 style="margin: 0;">Tambah Fasilitas Baru</h3>
            <button onclick="closeAddFasilitas()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form action="actions/insert_fasilitas.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Nama Fasilitas</label>
                <input type="text" name="nama_fasilitas" class="form-input" required placeholder="Contoh: Mushola">
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Singkat</label>
                <input type="text" name="deskripsi_fasilitas" class="form-input" placeholder="Contoh: Tempat ibadah yang bersih dan nyaman">
            </div>
            <div class="form-group">
                <label class="form-label">Upload Gambar</label>
                <input type="file" name="gambar_fasilitas" class="form-input" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; margin-top: 1rem;">Simpan Fasilitas</button>
        </form>
    </div>
</div>

<!-- Modal Edit Fasilitas -->
<div id="modalEditFasilitas" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card" style="position: relative; max-width: 450px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <h3 style="margin: 0;">Edit Fasilitas</h3>
            <button onclick="closeEditFasilitas()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form action="actions/update_fasilitas.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_fasilitas" id="edit_fas_id">
            <input type="hidden" name="gambar_lama" id="edit_fas_gambar_lama">
            <div class="form-group">
                <label class="form-label">Nama Fasilitas</label>
                <input type="text" name="nama_fasilitas" id="edit_fas_nama" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Singkat</label>
                <input type="text" name="deskripsi_fasilitas" id="edit_fas_desk" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar_fasilitas" class="form-input" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; margin-top: 1rem;">Update Fasilitas</button>
        </form>
    </div>
</div>

<script>
function openAddFasilitas() { document.getElementById('modalAddFasilitas').style.display = 'flex'; }
function closeAddFasilitas() { document.getElementById('modalAddFasilitas').style.display = 'none'; }

function openEditFasilitas(data) {
    document.getElementById('edit_fas_id').value = data.id;
    document.getElementById('edit_fas_nama').value = data.nama;
    document.getElementById('edit_fas_desk').value = data.deskripsi || '';
    document.getElementById('edit_fas_gambar_lama').value = data.gambar;
    document.getElementById('modalEditFasilitas').style.display = 'flex';
}
function closeEditFasilitas() { document.getElementById('modalEditFasilitas').style.display = 'none'; }

// Tutup modal jika klik di luar
document.addEventListener('click', function(e) {
    if (e.target.id === 'modalAddFasilitas') closeAddFasilitas();
    if (e.target.id === 'modalEditFasilitas') closeEditFasilitas();
});
</script>
<?php endif; ?>


<!-- ── SCHEDULE SECTION ───────────────────────────────────── -->
<section id="status" class="container" style="padding: 2rem 1rem;">
  <h2 class="section-title">Status Ketersediaan Lapangan</h2>
  
  <div class="schedule-grid">
    <?php foreach ($days_data as $day): ?>
    <div class="day-card">
      <div class="day-header">
        <div class="day-number"><?= $day['day_num'] ?></div>
        <div class="day-name"><?= $day['day_id'] ?></div>
        <div style="margin-left: auto; font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m', strtotime($day['date'])) ?></div>
      </div>
      
      <div class="slot-list">
        <?php if (empty($day['slots'])): ?>
          <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Tidak ada jadwal.</p>
        <?php else: ?>
          <?php foreach ($day['slots'] as $slot): 
            $slot_time = strtotime($day['date'] . ' ' . $slot['jam_mulai']);
            $is_past = ($slot_time < time());
          ?>
          <div class="slot-item" style="<?= $is_past ? 'opacity: 0.6;' : '' ?>">
            <div class="slot-time" style="font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                <div><?= substr($slot['jam_mulai'], 0, 5) ?> - <?= substr($slot['jam_selesai'], 0, 5) ?></div>
                <div style="font-weight: 700; color: var(--primary); font-size: 0.8rem;">
                    Rp <?= ((int)substr($slot['jam_mulai'], 0, 2) >= 18) ? '120k' : '100k' ?>
                </div>
            </div>
            
            <?php if ($is_past): ?>
              <span class="badge" style="background: #94a3b8;">Lewat</span>
            <?php elseif ($slot['status'] === 'tersedia'): ?>
              <span class="badge badge-available">Tersedia</span>
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
</section>

<!-- WhatsApp Floating Button -->
<?php
  // Format nomor HP ke format WA (ganti awalan 0 menjadi 62)
  $wa_number = preg_replace('/[^0-9]/', '', $lapangan['no_handphone'] ?? '081234567890');
  if (substr($wa_number, 0, 1) === '0') {
      $wa_number = '62' . substr($wa_number, 1);
  } elseif (substr($wa_number, 0, 2) === '62') {
      // already starts with 62
  } else {
      $wa_number = '62' . $wa_number;
  }
?>
<a href="https://wa.me/<?= $wa_number ?>" class="whatsapp-float" target="_blank">
  <i class="fab fa-whatsapp"></i>
</a>

<?php include 'includes/footer.php'; ?>
