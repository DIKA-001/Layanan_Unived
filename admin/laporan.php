<?php
require_once 'koneksi.php';

// Query untuk data laporan
try {
    // Jumlah layanan per kategori
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM services GROUP BY category");
    $service_stats = $stmt->fetchAll();
    
    // Jumlah tiket per status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM service_tickets GROUP BY status");
    $ticket_stats = $stmt->fetchAll();
    
    // Jumlah pengguna per role - DIPERBAIKI: query yang benar untuk user_roles
    $stmt = $pdo->query("
        SELECT ur.role, COUNT(*) as count 
        FROM user_roles ur 
        JOIN users u ON ur.user_id = u.id 
        WHERE ur.is_active = 1 
        GROUP BY ur.role
    ");
    $user_stats = $stmt->fetchAll();
    
    // Tiket per bulan (6 bulan terakhir)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
        FROM service_tickets 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $monthly_stats = $stmt->fetchAll();
    
    // Data untuk cards statistik
    $total_services = $pdo->query("SELECT COUNT(*) as total FROM services")->fetch()['total'];
    $total_tickets = $pdo->query("SELECT COUNT(*) as total FROM service_tickets")->fetch()['total'];
    $total_users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'];
    $resolved_tickets = $pdo->query("SELECT COUNT(*) as total FROM service_tickets WHERE status = 'Selesai'")->fetch()['total'];
    
} catch (PDOException $e) {
    $error = "Gagal memuat data laporan: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Puskom UNIVED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e4359;
            --secondary-color: #f8f9fa;
            --accent-color: #3c8dbc;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 60px; /* Tambahkan padding untuk navbar fixed */
        }
        
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed; /* Membuat sidebar tetap */
            top: 0;
            left: 0;
            width: 16.666667%; /* Lebar sesuai col-md-3 col-lg-2 */
            z-index: 1000;
            overflow-y: auto; /* Tambahkan scroll jika konten terlalu panjang */
            height: 100vh;
            padding-top: 60px; /* Beri ruang untuk navbar */
        }
        
        /* Sidebar untuk mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                min-height: auto;
                position: relative;
                padding-top: 0;
            }
            
            body {
                padding-top: 0;
            }
        }
        
        .logo-container {
            background-color: #163145;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: fixed;
            top: 0;
            width: 16.666667%;
            z-index: 1001;
        }
        
        /* Logo container untuk mobile */
        @media (max-width: 768px) {
            .logo-container {
                width: 100%;
                position: relative;
            }
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* Navbar fixed */
        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 16.666667%; /* Sesuaikan dengan lebar sidebar */
            z-index: 1030;
            width: calc(100% - 16.666667%);
        }
        
        /* Navbar untuk mobile */
        @media (max-width: 768px) {
            .navbar {
                position: relative;
                width: 100%;
                left: 0;
            }
        }
        
        /* Konten utama */
        .main-content {
            margin-left: 16.666667%; /* Sesuaikan dengan lebar sidebar */
            width: calc(100% - 16.666667%);
        }
        
        /* Konten utama untuk mobile */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
        
        .card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card.services {
            border-left-color: var(--info-color);
        }
        
        .stat-card.tickets {
            border-left-color: var(--success-color);
        }
        
        .stat-card.users {
            border-left-color: var(--warning-color);
        }
        
        .stat-card.resolved {
            border-left-color: var(--danger-color);
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        .export-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .export-btn:hover {
            background-color: #163145;
            color: white;
        }
        
        .badge-status {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-ditugaskan {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .badge-proses {
            background-color: #fff8e1;
            color: #ff8f00;
        }
        
        .badge-selesai {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-tertunda {
            background-color: #fbe9e7;
            color: #d84315;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .chart-container {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar px-0">
                <div class="logo-container text-center">
                    <h5>Puskom UNIVED</h5>
                    <p class="small">Layanan IT</p>
                </div>
                <nav class="nav flex-column p-3 mt-5">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="layanan.php" class="nav-link">
                        <i class="fas fa-concierge-bell"></i> Layanan
                    </a>
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                    <a href="laporan.php" class="nav-link active">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    <a href="penggunaan-layanan.php" class="nav-link">
                        <i class="fas fa-clipboard-list"></i> Penggunaan
                    </a>
                    <a href="#" class="nav-link mt-5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content px-4 py-3">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 rounded shadow-sm">
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
                <!-- Laporan Content -->
                <div id="laporan">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-chart-bar me-2"></i>Laporan Statistik</h2>
                        <div>
                            <button class="export-btn me-2" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
                            </button>
                            <button class="export-btn" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> Ekspor Excel
                            </button>
                        </div>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Statistik Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card stat-card services h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-1 text-muted">Total Layanan</h6>
                                            <h3 class="card-title"><?php echo $total_services; ?></h3>
                                        </div>
                                        <div class="bg-info p-3 rounded-circle">
                                            <i class="fas fa-concierge-bell fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card stat-card tickets h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-1 text-muted">Total Tiket</h6>
                                            <h3 class="card-title"><?php echo $total_tickets; ?></h3>
                                        </div>
                                        <div class="bg-success p-3 rounded-circle">
                                            <i class="fas fa-ticket-alt fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card stat-card users h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-1 text-muted">Total Pengguna</h6>
                                            <h3 class="card-title"><?php echo $total_users; ?></h3>
                                        </div>
                                        <div class="bg-warning p-3 rounded-circle">
                                            <i class="fas fa-users fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card stat-card resolved h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-1 text-muted">Tiket Selesai</h6>
                                            <h3 class="card-title"><?php echo $resolved_tickets; ?></h3>
                                        </div>
                                        <div class="bg-danger p-3 rounded-circle">
                                            <i class="fas fa-check-circle fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Distribusi Layanan per Kategori</h5>
                                    <i class="fas fa-chart-pie text-info"></i>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="categoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Status Tiket Layanan</h5>
                                    <i class="fas fa-chart-doughnut text-success"></i>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Distribusi Pengguna per Role</h5>
                                    <i class="fas fa-chart-bar text-warning"></i>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="roleChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Tiket per Bulan (6 Bulan Terakhir)</h5>
                                    <i class="fas fa-chart-line text-danger"></i>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Data Statistik -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Data Statistik Detail</h5>
                                    <i class="fas fa-table text-primary"></i>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <h6 class="border-bottom pb-2">Layanan per Kategori</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Kategori</th>
                                                            <th class="text-end">Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($service_stats as $stat): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($stat['category']); ?></td>
                                                            <td class="text-end"><?php echo $stat['count']; ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($service_stats)): ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6 class="border-bottom pb-2">Tiket per Status</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Status</th>
                                                            <th class="text-end">Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($ticket_stats as $stat): 
                                                            $badgeClass = '';
                                                            switch($stat['status']) {
                                                                case 'Ditugaskan': $badgeClass = 'badge-ditugaskan'; break;
                                                                case 'Proses': $badgeClass = 'badge-proses'; break;
                                                                case 'Selesai': $badgeClass = 'badge-selesai'; break;
                                                                case 'Tertunda': $badgeClass = 'badge-tertunda'; break;
                                                                default: $badgeClass = '';
                                                            }
                                                        ?>
                                                        <tr>
                                                            <td><span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($stat['status']); ?></span></td>
                                                            <td class="text-end"><?php echo $stat['count']; ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($ticket_stats)): ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6 class="border-bottom pb-2">Pengguna per Role</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Role</th>
                                                            <th class="text-end">Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($user_stats as $stat): ?>
                                                        <tr>
                                                            <td><?php echo ucfirst(htmlspecialchars($stat['role'])); ?></td>
                                                            <td class="text-end"><?php echo $stat['count']; ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($user_stats)): ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        // Simpan data untuk ekspor
        const reportData = {
            serviceStats: <?php echo json_encode($service_stats); ?>,
            ticketStats: <?php echo json_encode($ticket_stats); ?>,
            userStats: <?php echo json_encode($user_stats); ?>,
            monthlyStats: <?php echo json_encode($monthly_stats); ?>,
            totals: {
                services: <?php echo $total_services; ?>,
                tickets: <?php echo $total_tickets; ?>,
                users: <?php echo $total_users; ?>,
                resolved: <?php echo $resolved_tickets; ?>
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Chart distribusi kategori layanan
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'pie',
                data: {
                    labels: reportData.serviceStats.map(item => item.category),
                    datasets: [{
                        data: reportData.serviceStats.map(item => item.count),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',   // Jaringan & Internet
                            'rgba(255, 99, 132, 0.7)',   // Software
                            'rgba(255, 206, 86, 0.7)',   // Hardware
                            'rgba(75, 192, 192, 0.7)',   // Akun
                            'rgba(153, 102, 255, 0.7)',  // Data
                            'rgba(255, 159, 64, 0.7)',   // Lainnya
                            'rgba(199, 199, 199, 0.7)'   // Default
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15
                            }
                        }
                    }
                }
            });
            
            // Chart status tiket
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: reportData.ticketStats.map(item => item.status),
                    datasets: [{
                        data: reportData.ticketStats.map(item => item.count),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',  // Ditugaskan - Blue
                            'rgba(255, 206, 86, 0.7)',   // Proses - Yellow
                            'rgba(75, 192, 192, 0.7)',   // Selesai - Teal
                            'rgba(255, 99, 132, 0.7)',   // Tertunda - Red
                            'rgba(153, 102, 255, 0.7)'   // Other - Purple
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15
                            }
                        }
                    }
                }
            });
            
            // Chart distribusi role pengguna
            const roleCtx = document.getElementById('roleChart').getContext('2d');
            const roleChart = new Chart(roleCtx, {
                type: 'bar',
                data: {
                    labels: reportData.userStats.map(item => item.role.charAt(0).toUpperCase() + item.role.slice(1)),
                    datasets: [{
                        label: 'Jumlah Pengguna',
                        data: reportData.userStats.map(item => item.count),
                        backgroundColor: 'rgba(30, 67, 89, 0.7)',
                        borderColor: 'rgba(30, 67, 89, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Chart tiket per bulan
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            
            // Format bulan untuk chart
            const monthlyLabels = reportData.monthlyStats.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            }).reverse();
            
            const monthlyData = reportData.monthlyStats.map(item => item.count).reverse();
            
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Jumlah Tiket',
                        data: monthlyData,
                        backgroundColor: 'rgba(60, 141, 188, 0.2)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(60, 141, 188, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
        
        // Fungsi untuk ekspor PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Judul laporan
            doc.setFontSize(18);
            doc.text('LAPORAN STATISTIK PUSKOM UNIVED', 105, 15, { align: 'center' });
            doc.setFontSize(12);
            doc.text('Tanggal: ' + new Date().toLocaleDateString('id-ID'), 105, 22, { align: 'center' });
            
            let yPosition = 35;
            
            // Statistik Utama
            doc.setFontSize(14);
            doc.text('STATISTIK UTAMA', 14, yPosition);
            yPosition += 10;
            
            doc.setFontSize(10);
            doc.text(`Total Layanan: ${reportData.totals.services}`, 20, yPosition);
            yPosition += 7;
            doc.text(`Total Tiket: ${reportData.totals.tickets}`, 20, yPosition);
            yPosition += 7;
            doc.text(`Total Pengguna: ${reportData.totals.users}`, 20, yPosition);
            yPosition += 7;
            doc.text(`Tiket Selesai: ${reportData.totals.resolved}`, 20, yPosition);
            yPosition += 15;
            
            // Layanan per Kategori
            if (reportData.serviceStats.length > 0) {
                doc.setFontSize(14);
                doc.text('LAYANAN PER KATEGORI', 14, yPosition);
                yPosition += 10;
                
                const serviceTableData = reportData.serviceStats.map(item => [
                    item.category, 
                    item.count.toString()
                ]);
                
                doc.autoTable({
                    startY: yPosition,
                    head: [['Kategori', 'Jumlah']],
                    body: serviceTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [30, 67, 89] }
                });
                
                yPosition = doc.lastAutoTable.finalY + 15;
            }
            
            // Tiket per Status
            if (reportData.ticketStats.length > 0) {
                doc.setFontSize(14);
                doc.text('TIKET PER STATUS', 14, yPosition);
                yPosition += 10;
                
                const ticketTableData = reportData.ticketStats.map(item => [
                    item.status, 
                    item.count.toString()
                ]);
                
                doc.autoTable({
                    startY: yPosition,
                    head: [['Status', 'Jumlah']],
                    body: ticketTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [30, 67, 89] }
                });
                
                yPosition = doc.lastAutoTable.finalY + 15;
            }
            
            // Pengguna per Role
            if (reportData.userStats.length > 0) {
                doc.setFontSize(14);
                doc.text('PENGGUNA PER ROLE', 14, yPosition);
                yPosition += 10;
                
                const userTableData = reportData.userStats.map(item => [
                    item.role, 
                    item.count.toString()
                ]);
                
                doc.autoTable({
                    startY: yPosition,
                    head: [['Role', 'Jumlah']],
                    body: userTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [30, 67, 89] }
                });
            }
            
            // Simpan PDF
            doc.save('Laporan_Puskom_UNIVED_' + new Date().toISOString().slice(0, 10) + '.pdf');
        }
        
        // Fungsi untuk ekspor Excel
        function exportToExcel() {
            // Buat workbook baru
            const wb = XLSX.utils.book_new();
            
            // Data statistik utama
            const summaryData = [
                ['STATISTIK UTAMA'],
                ['Total Layanan', reportData.totals.services],
                ['Total Tiket', reportData.totals.tickets],
                ['Total Pengguna', reportData.totals.users],
                ['Tiket Selesai', reportData.totals.resolved],
                [],
                ['LAPORAN DETAIL']
            ];
            
            // Data layanan per kategori
            if (reportData.serviceStats.length > 0) {
                summaryData.push(['LAYANAN PER KATEGORI']);
                summaryData.push(['Kategori', 'Jumlah']);
                reportData.serviceStats.forEach(item => {
                    summaryData.push([item.category, item.count]);
                });
                summaryData.push([]);
            }
            
            // Data tiket per status
            if (reportData.ticketStats.length > 0) {
                summaryData.push(['TIKET PER STATUS']);
                summaryData.push(['Status', 'Jumlah']);
                reportData.ticketStats.forEach(item => {
                    summaryData.push([item.status, item.count]);
                });
                summaryData.push([]);
            }
            
            // Data pengguna per role
            if (reportData.userStats.length > 0) {
                summaryData.push(['PENGGUNA PER ROLE']);
                summaryData.push(['Role', 'Jumlah']);
                reportData.userStats.forEach(item => {
                    summaryData.push([item.role, item.count]);
                });
            }
            
            // Buat worksheet
            const ws = XLSX.utils.aoa_to_sheet(summaryData);
            
            // Tambahkan worksheet ke workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Laporan Statistik');
            
            // Simpan file
            XLSX.writeFile(wb, 'Laporan_Puskom_UNIVED_' + new Date().toISOString().slice(0, 10) + '.xlsx');
        }
    </script>
</body>
</html>