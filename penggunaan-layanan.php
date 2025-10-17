<?php
// Include file konfigurasi database
require_once 'koneksi.php';

// Inisialisasi variabel filter
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$jenisLayanan = isset($_GET['jenis_layanan']) ? $_GET['jenis_layanan'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk statistik penggunaan layanan berdasarkan service_tickets
try {
    // Build query dengan filter - DIPERBAIKI
    $query = "
        SELECT 
            st.id as tiket_id,
            MONTHNAME(st.created_at) as bulan_nama,
            MONTH(st.created_at) as bulan,
            YEAR(st.created_at) as tahun,
            DATE_FORMAT(st.created_at, '%d %M %Y %H:%i') as tanggal_jam,
            s.category as jenis_layanan,
            u.fullname as nama_pelapor,
            st.status,
            (SELECT COUNT(*) 
             FROM service_tickets st2 
             WHERE YEAR(st2.created_at) = YEAR(st.created_at) 
             AND MONTH(st2.created_at) = MONTH(st.created_at) 
             AND st2.service_id = st.service_id 
             AND st2.status = st.status) as jumlah_tiket,
            (SELECT AVG(TIMESTAMPDIFF(DAY, st3.created_at, st3.completed_at))
             FROM service_tickets st3
             WHERE YEAR(st3.created_at) = YEAR(st.created_at) 
             AND MONTH(st3.created_at) = MONTH(st.created_at) 
             AND st3.service_id = st.service_id 
             AND st3.status = st.status
             AND st3.completed_at IS NOT NULL) as rata_waktu_penyelesaian
        FROM service_tickets st
        LEFT JOIN services s ON st.service_id = s.id
        LEFT JOIN users u ON st.user_id = u.id
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
    
    $query .= " ORDER BY tahun, bulan, s.category, st.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $usage_data = $stmt->fetchAll();
    
    // Format data untuk tabel
    $usage_table_data = [];
    foreach ($usage_data as $data) {
        $usage_table_data[] = [
            'tiket_id' => $data['tiket_id'],
            'bulan' => $data['bulan_nama'] . ' ' . $data['tahun'],
            'tanggal_jam' => $data['tanggal_jam'],
            'jenis_layanan' => $data['jenis_layanan'],
            'nama_pelapor' => $data['nama_pelapor'],
            'jumlah_tiket' => $data['jumlah_tiket'],
            'rata_waktu' => $data['rata_waktu_penyelesaian'] ? round($data['rata_waktu_penyelesaian'], 1) . ' hari' : 'Belum selesai',
            'status' => $data['status']
        ];
    }
    
    // Query untuk data grafik
    $chart_query = "
        SELECT 
            s.category as jenis_layanan,
            COUNT(*) as jumlah_tiket
        FROM service_tickets st
        LEFT JOIN services s ON st.service_id = s.id
        WHERE YEAR(st.created_at) = :tahun
    ";
    
    $chart_params = [':tahun' => $tahun];
    
    if (!empty($bulan)) {
        $chart_query .= " AND MONTH(st.created_at) = :bulan";
        $chart_params[':bulan'] = $bulan;
    }
    
    if (!empty($status)) {
        $chart_query .= " AND st.status = :status";
        $chart_params[':status'] = $status;
    }
    
    $chart_query .= " GROUP BY s.category ORDER BY jumlah_tiket DESC";
    
    $chart_stmt = $pdo->prepare($chart_query);
    $chart_stmt->execute($chart_params);
    $chart_data = $chart_stmt->fetchAll();
    
} catch (PDOException $e) {
    // Jika terjadi error, set nilai default
    $usage_table_data = [];
    $chart_data = [];
    $error_message = "Error loading data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggunaan Layanan - Puskom Universitas Dehasen Bengkulu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        /* CSS yang sama seperti sebelumnya */
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --university-color: #1e4359;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding-top: 56px;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--university-color);
            color: white;
            transition: all 0.3s;
            position: fixed;
            top: 56px;
            left: 0;
            width: 16.666667%;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.3rem;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            width: 100%;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--university-color);
        }
        
        .card-dashboard {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .table th {
            background-color: var(--university-color);
            color: white;
        }
        
        .btn-primary {
            background-color: var(--university-color);
            border-color: var(--university-color);
        }
        
        .btn-primary:hover {
            background-color: #153448;
            border-color: #153448;
        }
        
        .stat-card {
            border-left: 4px solid var(--university-color);
        }
        
        .logo-container {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .main-content {
            margin-left: 16.666667%;
            padding-top: 20px;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .table-print {
                width: 100%;
                border-collapse: collapse;
            }
            .table-print, .table-print th, .table-print td {
                border: 1px solid #000;
            }
            .table-print th {
                background-color: #f0f0f0;
            }
            
            body {
                padding-top: 0 !important;
            }
            .sidebar, .navbar {
                position: static !important;
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .sidebar .nav-link i {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            body {
                padding-top: 0;
            }
            
            .navbar {
                position: relative;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light rounded shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Admin Puskom
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                            <li><a class="dropdown-item" href="#">Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar px-0">
                <div class="logo-container text-center">
                    <h5>Puskom UNIVED</h5>
                    <p class="small">Layanan IT</p>
                </div>
                <nav class="nav flex-column p-3">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                    </a>
                    <a href="layanan.php" class="nav-link">
                        <i class="fas fa-concierge-bell"></i> <span>Layanan</span>
                    </a>
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> <span>Manajemen User</span>
                    </a>
                    <a href="laporan.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> <span>Laporan</span>
                    </a>
                    <a href="penggunaan-layanan.php" class="nav-link active">
                        <i class="fas fa-clipboard-list"></i> <span>Penggunaan</span>
                    </a>
                    <a href="logout.php" class="nav-link mt-5">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto px-4 py-3 main-content">
                <!-- Penggunaan Layanan Content -->
                <div class="content-section active">
                    <h2 class="mb-4">Laporan Penggunaan Layanan</h2>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tahunLaporan" class="form-label">Tahun</label>
                                            <select class="form-select" id="tahunLaporan" name="tahun">
                                                <?php
                                                $current_year = date('Y');
                                                for ($year = $current_year; $year >= 2020; $year--) {
                                                    $selected = ($year == $tahun) ? 'selected' : '';
                                                    echo "<option value='$year' $selected>$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="bulanLaporan" class="form-label">Bulan</label>
                                            <select class="form-select" id="bulanLaporan" name="bulan">
                                                <option value="">Semua Bulan</option>
                                                <?php
                                                $months = [
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ];
                                                foreach ($months as $num => $name) {
                                                    $selected = ($num == $bulan) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="jenisLayanan" class="form-label">Jenis Layanan</label>
                                            <select class="form-select" id="jenisLayanan" name="jenis_layanan">
                                                <option value="">Semua Layanan</option>
                                                <option value="Jaringan & Internet" <?= $jenisLayanan == 'Jaringan & Internet' ? 'selected' : '' ?>>Jaringan & Internet</option>
                                                <option value="Software" <?= $jenisLayanan == 'Software' ? 'selected' : '' ?>>Software</option>
                                                <option value="Hardware" <?= $jenisLayanan == 'Hardware' ? 'selected' : '' ?>>Hardware</option>
                                                <option value="Akun" <?= $jenisLayanan == 'Akun' ? 'selected' : '' ?>>Akun</option>
                                                <option value="Data" <?= $jenisLayanan == 'Data' ? 'selected' : '' ?>>Data</option>
                                                <option value="Lainnya" <?= $jenisLayanan == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="statusLaporan" class="form-label">Status</label>
                                            <select class="form-select" id="statusLaporan" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="Ditugaskan" <?= $status == 'Ditugaskan' ? 'selected' : '' ?>>Ditugaskan</option>
                                                <option value="Proses" <?= $status == 'Proses' ? 'selected' : '' ?>>Proses</option>
                                                <option value="Selesai" <?= $status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="Tertunda" <?= $status == 'Tertunda' ? 'selected' : '' ?>>Tertunda</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Tampilkan Data</button>
                                <button type="button" class="btn btn-outline-success" id="btnExportExcel">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="btnExportPDF">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Grafik -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Distribusi Layanan</h5>
                                    <div class="chart-container">
                                        <canvas id="serviceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Statistik Layanan per Bulan</h5>
                                    <div class="chart-container">
                                        <canvas id="monthlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Data Penggunaan Layanan</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="usageTable">
                                    <thead>
                                        <tr>
                                            <th>Tanggal & Waktu</th>
                                            <th>Jenis Layanan</th>
                                            <th>Nama Pelapor</th>
                                            <th>Jumlah Tiket</th>
                                            <th>Rata-rata Waktu Penyelesaian</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($usage_table_data)): ?>
                                            <?php foreach ($usage_table_data as $data): ?>
                                            <tr>
                                                <td><?php echo $data['tanggal_jam']; ?></td>
                                                <td><?php echo $data['jenis_layanan']; ?></td>
                                                <td><?php echo $data['nama_pelapor']; ?></td>
                                                <td><?php echo $data['jumlah_tiket']; ?></td>
                                                <td><?php echo $data['rata_waktu']; ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php 
                                                        switch($data['status']) {
                                                            case 'Selesai': echo 'bg-success'; break;
                                                            case 'Proses': echo 'bg-primary'; break;
                                                            case 'Ditugaskan': echo 'bg-info'; break;
                                                            case 'Tertunda': echo 'bg-warning'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                        ?>
                                                    ">
                                                        <?php echo $data['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog"></i> Ubah Status
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item btn-status" href="#" data-tiket-id="<?php echo $data['tiket_id']; ?>" data-status="Ditugaskan">
                                                                    <span class="badge bg-info">Ditugaskan</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item btn-status" href="#" data-tiket-id="<?php echo $data['tiket_id']; ?>" data-status="Proses">
                                                                    <span class="badge bg-primary">Proses</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item btn-status" href="#" data-tiket-id="<?php echo $data['tiket_id']; ?>" data-status="Selesai">
                                                                    <span class="badge bg-success">Selesai</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item btn-status" href="#" data-tiket-id="<?php echo $data['tiket_id']; ?>" data-status="Tertunda">
                                                                    <span class="badge bg-warning">Tertunda</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <?php echo isset($error_message) ? $error_message : "Tidak ada data penggunaan layanan"; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section untuk print PDF -->
                    <div id="printSection" class="print-section" style="display: none;">
                        <h2>Laporan Penggunaan Layanan</h2>
                        <p>Universitas Dehasen Bengkulu - Pusat Komputer</p>
                        <p>Periode: <?php echo $tahun . ($bulan ? ' - ' . $months[$bulan] : ''); ?></p>
                        
                        <table class="table-print">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Jenis Layanan</th>
                                    <th>Nama Pelapor</th>
                                    <th>Jumlah Tiket</th>
                                    <th>Rata-rata Waktu Penyelesaian</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usage_table_data)): ?>
                                    <?php foreach ($usage_table_data as $data): ?>
                                    <tr>
                                        <td><?php echo $data['tanggal_jam']; ?></td>
                                        <td><?php echo $data['jenis_layanan']; ?></td>
                                        <td><?php echo $data['nama_pelapor']; ?></td>
                                        <td><?php echo $data['jumlah_tiket']; ?></td>
                                        <td><?php echo $data['rata_waktu']; ?></td>
                                        <td><?php echo $data['status']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <?php echo isset($error_message) ? $error_message : "Tidak ada data penggunaan layanan"; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <p style="margin-top: 20px;">Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables & Buttons -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <!-- SheetJS (for Excel export) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <script>
       $(document).ready(function() {
    // Inisialisasi DataTable
    $('#usageTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        }
    });
    
    // Fungsi untuk menangani perubahan status
    $('.status-option').click(function(e) {
        e.preventDefault();
        
        const tiketId = $(this).data('tiket-id');
        const newStatus = $(this).data('status');
        const optionElement = $(this);
        
        if (confirm(`Apakah Anda yakin ingin mengubah status tiket menjadi "${newStatus}"?`)) {
            // Kirim permintaan AJAX untuk mengubah status
            $.ajax({
                url: 'update_ticket_status.php',
                method: 'POST',
                data: {
                    tiket_id: tiketId,
                    status: newStatus
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // Update tampilan
                        const row = optionElement.closest('tr');
                        const badge = row.find('.badge');
                        
                        // Update class badge berdasarkan status
                        badge.removeClass('bg-success bg-primary bg-info bg-warning bg-secondary');
                        switch(newStatus) {
                            case 'Selesai': badge.addClass('bg-success'); break;
                            case 'Proses': badge.addClass('bg-primary'); break;
                            case 'Ditugaskan': badge.addClass('bg-info'); break;
                            case 'Tertunda': badge.addClass('bg-warning'); break;
                            default: badge.addClass('bg-secondary');
                        }
                        
                        // Update teks status
                        badge.text(newStatus);
                        
                        alert('Status berhasil diubah');
                    } else {
                        alert('Gagal mengubah status: ' + result.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat mengubah status');
                }
            });
        }
    });
});