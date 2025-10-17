<?php
require_once 'koneksi.php';

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

// Header untuk file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="laporan_penggunaan_layanan.xls"');
header('Cache-Control: max-age=0');

// Output data
echo "Laporan Penggunaan Layanan\n";
echo "Tahun: $tahun" . ($bulan ? " Bulan: $bulan" : "") . "\n\n";

echo "Bulan\tJenis Layanan\tJumlah Tiket\tRata Waktu Penyelesaian\tStatus\n";

foreach ($data as $row) {
    echo $row['bulan_nama'] . ' ' . $row['tahun'] . "\t";
    echo $row['jenis_layanan'] . "\t";
    echo $row['jumlah_tiket'] . "\t";
    echo ($row['rata_waktu_penyelesaian'] ? round($row['rata_waktu_penyelesaian'], 1) . ' hari' : 'Belum selesai') . "\t";
    echo $row['status'] . "\n";
}
?>