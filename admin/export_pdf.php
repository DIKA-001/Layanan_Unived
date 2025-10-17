<?php
require_once 'koneksi.php';
require_once 'vendor/autoload.php'; // Pastikan Anda telah menginstall dompdf

use Dompdf\Dompdf;

// Ambil parameter filter
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$jenisLayanan = isset($_GET['jenis_layanan']) ? $_GET['jenis_layanan'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query data dengan filter
$query = "
    SELECT 
        MONTHNAME(st.created_at) as bulan_nama,
        MONTH(st.created_at) as bulan,
        YEAR(st.created_at) as tahun,
        s.category as jenis_layanan,
        COUNT(*) as jumlah_tiket,
        AVG(CASE WHEN st.completed_at IS NOT NULL THEN TIMESTAMPDIFF(DAY, st.created_at, st.completed_at) ELSE NULL END) as rata_waktu_penyelesaian,
        st.status
    FROM service_tickets st
    LEFT JOIN services s ON st.service_id = s.id
    WHERE YEAR(st.created_at) = :tahun
";

$params = [':tahun' => $tahun];

if (!empty($bulan)) {
    $query .= " AND MONTH(st.created_at) = :bulan";
    $params[':bulan'] = $bulan;
}

if (!empty($jenisLayanan)) {
    $query .= " AND s.category = :jenis_layanan";
    $params[':jenis_layanan'] = $jenisLayanan;
}

if (!empty($status)) {
    $query .= " AND st.status = :status";
    $params[':status'] = $status;
}

$query .= " GROUP BY YEAR(st.created_at), MONTH(st.created_at), s.category, st.status
            ORDER BY tahun, bulan, s.category";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Buat HTML untuk PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #1e4359; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1e4359; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Laporan Penggunaan Layanan</h1>
    <p>Tahun: ' . $tahun . ($bulan ? ' | Bulan: ' . $bulan : '') . '</p>
    
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Jenis Layanan</th>
                <th>Jumlah Tiket</th>
                <th>Rata Waktu Penyelesaian</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

foreach ($data as $row) {
    $html .= '
            <tr>
                <td>' . $row['bulan_nama'] . ' ' . $row['tahun'] . '</td>
                <td>' . $row['jenis_layanan'] . '</td>
                <td>' . $row['jumlah_tiket'] . '</td>
                <td>' . ($row['rata_waktu_penyelesaian'] ? round($row['rata_waktu_penyelesaian'], 1) . ' hari' : 'Belum selesai') . '</td>
                <td>' . $row['status'] . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Buat PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('laporan_penggunaan_layanan.pdf', ['Attachment' => true]);
?>