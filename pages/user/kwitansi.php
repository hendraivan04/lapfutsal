<?php
/**
 * pages/user/kwitansi.php
 * Printable receipt page for successful bookings.
 */
require_once __DIR__ . '/../../config/database.php';

// Authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php'); exit;
}

$id_pemesanan = (int)($_GET['id_pemesanan'] ?? 0);
$user_id      = (int)$_SESSION['user_id'];
$user_role    = $_SESSION['user_role'];

// Fetch booking details
$query = "
    SELECT p.*, l.nama as lapangan_nama, j.jam_mulai, j.jam_selesai, u.nama as user_nama
    FROM pemesanan p
    JOIN lapangan l ON p.id_lapangan = l.id_lapangan
    JOIN jadwal_lapangan j ON p.id_jadwal = j.id_jadwal
    JOIN pelanggan u ON p.id_pelanggan = u.id_pelanggan
    WHERE p.id_pemesanan = $id_pemesanan
";

$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

// Security Check: Only owner or admin can see the receipt
if (!$data || ($user_role !== 'admin' && (int)$data['id_pelanggan'] !== $user_id)) {
    die("Akses ditolak atau data tidak ditemukan.");
}

// Receipt is only valid for 'lunas' status
if ($data['status_pembayaran'] !== 'lunas') {
    die("Kwitansi hanya tersedia untuk pembayaran yang sudah lunas.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi <?= sprintf('%02d', $data['id_pemesanan']) ?> - FutsalPOMPemenang</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; background: #f0f0f0; }
        .receipt-card { background: white; max-width: 800px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: 800; color: #3b82f6; }
        .receipt-title { font-size: 20px; font-weight: 700; color: #666; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-box h4 { margin: 0 0 10px; color: #888; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        .info-box p { margin: 0; font-weight: 600; font-size: 16px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table th { background: #f9fafb; text-align: left; padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; color: #666; }
        .table td { padding: 15px 12px; border-bottom: 1px solid #eee; }
        .total-section { text-align: right; }
        .total-row { display: flex; justify-content: flex-end; gap: 20px; align-items: center; }
        .total-label { font-size: 16px; color: #666; }
        .total-amount { font-size: 24px; font-weight: 800; color: #3b82f6; }
        .footer-note { margin-top: 30px; text-align: center; border-top: 1px dashed #ccc; padding-top: 20px; color: #999; font-size: 13px; }
        .btn-print { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-bottom: 20px; }
        .status-stamp { border: 3px solid #059669; color: #059669; display: inline-block; padding: 5px 15px; font-weight: 900; transform: rotate(-15deg); font-size: 1.5rem; opacity: 0.8; margin-bottom: 10px; border-radius: 4px; }
        
        /* Signature section */
        .signature-section { display: flex; justify-content: space-between; margin-top: 60px; padding: 0 30px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-box .sig-title { font-size: 13px; color: #555; font-weight: 600; margin-bottom: 80px; }
        .signature-box .sig-line { border-bottom: 2px solid #333; margin-bottom: 6px; }
        .signature-box .sig-name { font-size: 14px; font-weight: 700; color: #333; }
        .signature-box .sig-role { font-size: 11px; color: #888; margin-top: 2px; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; max-width: 100%; padding: 0; }
            .btn-print { display: none; }
        }

        /* Responsiveness for Mobile */
        @media (max-width: 600px) {
            body { padding: 10px; }
            .receipt-card { padding: 20px; }
            .header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .info-grid { grid-template-columns: 1fr; gap: 20px; }
            .info-box.right-align { text-align: left !important; }
            .table { font-size: 13px; }
            .table th, .table td { padding: 10px 5px; }
            
            /* Bottom section wrapping */
            .bottom-summary { flex-direction: column !important; align-items: flex-start !important; gap: 20px; }
            .total-section { align-self: flex-start; margin-top: 10px; }
            .total-row { justify-content: flex-start; }
            
            .signature-section { justify-content: flex-start !important; }
        }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <button onclick="window.print()" class="btn-print">Cetak Kwitansi</button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div class="logo">FutsalPOMPemenang</div>
            <div class="receipt-title">KWITANSI PEMBAYARAN</div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4>DIBAYAR KEPADA</h4>
                <p>Pengelola FutsalPOMPemenang</p>
                <p style="font-weight: 400; font-size: 14px; color: #666;">Jl. kantor desa pemenang</p>
            </div>
            <div class="info-box right-align" style="text-align: right;">
                <h4>ID TRANSAKSI</h4>
                <p><?= sprintf('%02d', $data['id_pemesanan']) ?></p>
                <p style="font-weight: 400; font-size: 14px; color: #666;"><?= date('d F Y', strtotime($data['tanggal'])) ?></p>
            </div>
        </div>

        <div class="info-box" style="margin-bottom: 40px;">
            <h4>DITERIMA DARI</h4>
            <p><?= htmlspecialchars($data['nama_pemesan'] ?: $data['user_nama']) ?></p>
            <p style="font-weight: 400; font-size: 14px; color: #666;"><?= htmlspecialchars($data['no_wa']) ?></p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th>Jadwal</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 600;">Sewa Lapangan (<?= $data['lapangan_nama'] ?>)</div>
                        <div style="font-size: 12px; color: #888;">Durasi: <?= $data['durasi'] ?> Jam</div>
                    </td>
                    <td>
                        <?= date('d M Y', strtotime($data['tanggal'])) ?><br>
                        <span style="font-size: 13px; color: #666;"><?= substr($data['jam_mulai'],0,5) ?> - <?= substr($data['jam_selesai'],0,5) ?> WITA</span>
                    </td>
                    <td style="text-align: right; font-weight: 700;">
                        Rp <?= number_format($data['total_harga'], 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="bottom-summary" style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div style="text-align: center;">
                <div class="status-stamp">LUNAS</div>
                <div style="font-size: 12px; color: #666;">Dicetak otomatis oleh Sistem FutsalPOMPemenang</div>
            </div>
            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Total Pembayaran</span>
                    <span class="total-amount">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-section" style="justify-content: flex-end;">
            <div class="signature-box">
                <div class="sig-title">Admin</div>
                <div class="sig-line"></div>
                <div class="sig-name">Rizal</div>
                <div class="sig-role">Pengelola FutsalPOMPemenang</div>
            </div>
        </div>

        <div class="footer-note">
            Terima kasih telah berolahraga bersama kami. <br>
            Harap datang 15 menit sebelum waktu pemesanan dimulai.
        </div>
    </div>

    <script>
        // Optional: Auto print if asked via query
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('print')) {
            window.print();
        }
    </script>
</body>
</html>
