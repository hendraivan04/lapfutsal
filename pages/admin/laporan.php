<?php
require_once __DIR__ . '/penjaga.php';
$page_title = 'Laporan Keuangan';
$base_url   = '../../';

// Filter Tanggal
$start_date = clean_input($_GET['start_date'] ?? date('Y-m-01'));
$end_date   = clean_input($_GET['end_date'] ?? date('Y-m-d'));

// Query Ringkasan
$q_summary = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END) as total_masuk,
        SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END) as total_keluar
    FROM laporan_keuangan 
    WHERE tanggal BETWEEN '$start_date' AND '$end_date'
");
$summary = mysqli_fetch_assoc($q_summary);
$net_income = ($summary['total_masuk'] ?? 0) - ($summary['total_keluar'] ?? 0);

// Query Detail Transaksi
$transactions = mysqli_fetch_all(mysqli_query($conn, "
    SELECT * FROM laporan_keuangan 
    WHERE tanggal BETWEEN '$start_date' AND '$end_date'
    ORDER BY id_laporan DESC
"), MYSQLI_ASSOC);

// Query Grafik (Harian)
$q_chart = mysqli_query($conn, "
    SELECT 
        tanggal, 
        SUM(CASE WHEN jenis='masuk' THEN jumlah ELSE 0 END) as masuk,
        SUM(CASE WHEN jenis='keluar' THEN jumlah ELSE 0 END) as keluar
    FROM laporan_keuangan 
    WHERE tanggal BETWEEN '$start_date' AND '$end_date'
    GROUP BY tanggal
    ORDER BY tanggal ASC
");
$chart_labels = [];
$chart_masuk  = [];
$chart_keluar = [];
while ($row = mysqli_fetch_assoc($q_chart)) {
    $chart_labels[] = date('d M', strtotime($row['tanggal']));
    $chart_masuk[]  = (float)$row['masuk'];
    $chart_keluar[] = (float)$row['keluar'];
}

include '../../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="admin-layout">
    <?php include '../../includes/navigasi_admin.php'; ?>
    
    <div style="flex: 1; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-weight: 800; color: var(--dark);">Laporan Keuangan</h1>
                <p style="color: var(--text-muted);">Kas masuk dan keluar operasional futsal.</p>
                <button onclick="openCreateModal()" class="btn btn-primary" style="margin-top: 1rem; border-radius: 0.75rem; padding: 0.6rem 1.25rem; font-size: 0.9rem;">
                    <i class="fas fa-plus" style="margin-right: 0.5rem;"></i> Tambah Transaksi
                </button>
                <button onclick="window.print()" class="btn btn-outline" style="margin-top: 1rem; margin-left: 0.5rem; border-radius: 0.75rem; padding: 0.6rem 1.25rem; font-size: 0.9rem; background: white; border: 1px solid var(--border);">
                    <i class="fas fa-print" style="margin-right: 0.5rem;"></i> Cetak Laporan
                </button>
                
                <style>
                @media print {
                    /* Menyembunyikan elemen yang tidak perlu dicetak */
                    body * { visibility: hidden; }
                    .admin-sidebar { display: none !important; }
                    .admin-layout > div:not(.admin-sidebar), .admin-layout > div:not(.admin-sidebar) * { visibility: visible; }
                    .admin-layout > div:not(.admin-sidebar) { position: absolute; left: 0; top: 0; width: 100%; padding: 0 !important; margin-left: 0 !important; }
                    button, form, .btn { display: none !important; }
                    
                    /* Menghilangkan kolom Aksi di tabel */
                    table th:last-child, table td:last-child { display: none !important; }
                    
                    /* Merapikan tampilan tabel dan kartu saat dicetak */
                    .admin-layout { background: white !important; }
                    div[style*="box-shadow"] { box-shadow: none !important; border: 1px solid #ddd !important; }
                }
                </style>
            </div>
            
            <form style="display: flex; gap: 0.5rem; align-items: center; background: white; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">Mulai</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" style="border: none; font-size: 0.85rem; outline: none; padding: 0;">
                </div>
                <div style="width: 1px; height: 24px; background: var(--border); margin: 0 0.5rem;"></div>
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">Sampai</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" style="border: none; font-size: 0.85rem; outline: none; padding: 0;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; border-radius: 0.5rem; margin-left: 0.5rem;">
                    <i class="fas fa-filter"></i>
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); position: relative; overflow: hidden;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Total Pemasukan</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--success);">Rp <?= number_format($summary['total_masuk'] ?? 0, 0, ',', '.') ?></div>
                <i class="fas fa-arrow-up" style="position: absolute; right: 1.5rem; bottom: 1.5rem; font-size: 2.5rem; color: rgba(16, 185, 129, 0.1);"></i>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); position: relative; overflow: hidden;">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Total Pengeluaran</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--danger);">Rp <?= number_format($summary['total_keluar'] ?? 0, 0, ',', '.') ?></div>
                <i class="fas fa-arrow-down" style="position: absolute; right: 1.5rem; bottom: 1.5rem; font-size: 2.5rem; color: rgba(239, 68, 68, 0.1);"></i>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); position: relative; overflow: hidden; background: linear-gradient(135deg, white 0%, #f0f7ff 100%);">
                <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Pendapatan Bersih</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);">Rp <?= number_format($net_income, 0, ',', '.') ?></div>
                <i class="fas fa-wallet" style="position: absolute; right: 1.5rem; bottom: 1.5rem; font-size: 2.5rem; color: rgba(59, 130, 246, 0.1);"></i>
            </div>
        </div>

        <!-- Chart Section -->
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); margin-bottom: 3rem;">
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-weight: 700; font-size: 1.1rem; color: var(--dark);">Performa Keuangan Harian</h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Periode: <?= date('d M', strtotime($start_date)) ?> - <?= date('d M', strtotime($end_date)) ?></div>
            </div>
            <div style="height: 350px;">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

        <!-- Transactions Table -->
        <div style="background: white; border-radius: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background: #f9fafb;">
                <h3 style="font-weight: 700; font-size: 1.1rem; color: var(--dark);">Riwayat Transaksi</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f9fafb; border-bottom: 1px solid var(--border);">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">Tanggal</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">Keterangan</th>
                            <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600;">Jenis</th>
                            <th style="padding: 1rem; text-align: right; font-size: 0.85rem; font-weight: 600;">Jumlah</th>
                            <th style="padding: 1rem; text-align: center; font-size: 0.85rem; font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" style="padding: 4rem; text-align: center; color: var(--text-muted);">Belum ada data transaksi di periode ini.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1.25rem;">
                                <div style="font-weight: 600;"><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= isset($t['created_at']) ? date('H:i', strtotime($t['created_at'])) : '--:--' ?> WITA</div>
                            </td>
                            <td style="padding: 1.25rem;">
                                <div style="font-size: 0.95rem; font-weight: 500; color: var(--dark);"><?= htmlspecialchars($t['keterangan']) ?></div>
                                <?php if ($t['id_pemesanan']): ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">ID Pesanan: <?= sprintf('%02d', $t['id_pemesanan']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1.25rem;">
                                <?php if ($t['jenis'] === 'masuk'): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                                        <i class="fas fa-plus"></i> Masuk
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 700; color: #dc2626; background: #fef2f2; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                                        <i class="fas fa-minus"></i> Keluar
                                    </span>
                                <?php endif; ?>
                            </td>
                             <td style="padding: 1.25rem; text-align: right;">
                                <div style="font-weight: 800; color: <?= $t['jenis'] === 'masuk' ? 'var(--dark)' : 'var(--danger)' ?>;">
                                    <?= $t['jenis'] === 'masuk' ? '+' : '-' ?> Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <button class="btn btn-outline" style="padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.8rem;" 
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($t)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../../actions/delete_riwayat_transaksi.php?id_laporan=<?= $t['id_laporan'] ?>" 
                                       class="btn btn-outline" style="padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.8rem; color: var(--danger); border-color: var(--danger);"
                                       onclick="return confirm('Hapus transaksi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Create Modal -->
    <div id="createModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 1rem; width: 450px; max-width: 90%; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-weight: 700;">Tambah Transaksi</h3>
                <button onclick="closeCreateModal()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form action="../../actions/insert_riwayat_transaksi.php" method="POST">
                
                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Pembelian bola, Tagihan Listrik" class="form-input" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-input" required>
                            <option value="keluar">Pengeluaran</option>
                            <option value="masuk">Pemasukan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="text" name="jumlah" placeholder="0" class="form-input" required>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">Gunakan angka saja (boleh pakai titik pemisah ribuan).</small>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Transaksi</button>
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline" style="flex: 1;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 1rem; width: 450px; max-width: 90%; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-weight: 700;">Edit Transaksi</h3>
                <button onclick="closeEditModal()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form action="../../actions/update_riwayat_transaksi.php" method="POST">
                <input type="hidden" name="id_laporan" id="edit_id">
                
                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" id="edit_tanggal" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" id="edit_keterangan" class="form-input" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" id="edit_jenis" class="form-input" required>
                            <option value="masuk">Pemasukan</option>
                            <option value="keluar">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="text" name="jumlah" id="edit_jumlah" placeholder="Contoh: 50.000" class="form-input" required>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">Gunakan angka saja (boleh pakai titik pemisah ribuan).</small>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Perubahan</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-outline" style="flex: 1;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
    }

    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
    }

    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id_laporan;
        document.getElementById('edit_tanggal').value = data.tanggal;
        document.getElementById('edit_keterangan').value = data.keterangan;
        document.getElementById('edit_jenis').value = data.jenis;
        document.getElementById('edit_jumlah').value = data.jumlah;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close on outside click
    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            closeEditModal();
        }
        if (event.target == document.getElementById('createModal')) {
            closeCreateModal();
        }
    }
    </script>
<script>
const ctx = document.getElementById('financialChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            {
                label: 'Pemasukan',
                data: <?= json_encode($chart_masuk) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.5,
                categoryPercentage: 0.5
            },
            {
                label: 'Pengeluaran',
                data: <?= json_encode($chart_keluar) ?>,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.5,
                categoryPercentage: 0.5
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true, padding: 20 }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { drawBorder: false },
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000) return 'Rp ' + (value/1000000) + 'jt';
                        if (value >= 1000) return 'Rp ' + (value/1000) + 'k';
                        return 'Rp ' + value;
                    }
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
<?php include '../../includes/footer.php'; ?>
