<?php
// Include file konfigurasi database
require_once 'koneksi.php';

// Query untuk statistik dashboard
try {
    // Hitung jumlah layanan
    $stmt = $pdo->query("SELECT COUNT(*) as total_services FROM services");
    $total_services = $stmt->fetch()['total_services'];
    
    // Hitung jumlah pengguna
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];
    
    // Hitung tiket aktif
    $stmt = $pdo->query("SELECT COUNT(*) as active_tickets FROM service_tickets WHERE status IN ('Ditugaskan', 'Proses')");
    $active_tickets = $stmt->fetch()['active_tickets'];
    
    // Hitung permintaan baru
    $stmt = $pdo->query("SELECT COUNT(*) as new_requests FROM service_tickets WHERE status = 'Ditugaskan'");
    $new_requests = $stmt->fetch()['new_requests'];
    
    // Hitung tiket selesai bulan ini
    $current_month = date('Y-m');
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_this_month FROM service_tickets WHERE status = 'Selesai' AND DATE_FORMAT(completed_at, '%Y-%m') = ?");
    $stmt->execute([$current_month]);
    $completed_this_month = $stmt->fetch()['completed_this_month'];
    
    // Ambil data aktivitas terbaru
    $stmt = $pdo->query("
        SELECT st.title, u.fullname, st.created_at, st.status 
        FROM service_tickets st 
        JOIN users u ON st.user_id = u.id 
        ORDER BY st.created_at DESC 
        LIMIT 5
    ");
    $recent_activities = $stmt->fetchAll();
    
    // Ambil statistik kategori layanan
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM services 
        GROUP BY category
    ");
    $service_stats = $stmt->fetchAll();
    
    $total_services_by_category = 0;
    foreach ($service_stats as $stat) {
        $total_services_by_category += $stat['count'];
    }
    
    // Ambil data untuk chart status tiket
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM service_tickets 
        GROUP BY status
    ");
    $ticket_status_stats = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Jika terjadi error, set nilai default
    $total_services = 0;
    $total_users = 0;
    $active_tickets = 0;
    $new_requests = 0;
    $completed_this_month = 0;
    $recent_activities = array();
    $service_stats = array();
    $ticket_status_stats = array();
    $total_services_by_category = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan IT - Puskom Universitas Dehasen Bengkulu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            padding-top: 56px; /* Untuk mengkompensasi navbar fixed */
        }
        
        .sidebar {
            position: fixed;
            top: 56px; /* Sesuai dengan tinggi navbar */
            left: 0;
            height: calc(100vh - 56px); /* Tinggi viewport dikurangi tinggi navbar */
            width: 250px; /* Lebar sidebar */
            background-color: var(--university-color);
            color: white;
            transition: all 0.3s;
            overflow-y: auto; /* Biarkan sidebar bisa di-scroll jika kontennya panjang */
            z-index: 1000;
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
            z-index: 1030; /* Pastikan navbar di atas sidebar */
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--university-color);
        }
        
        .main-content {
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
            padding: 20px;
            padding-top: 70px; /* Beri ruang untuk navbar fixed */
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
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
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
        
        .progress {
            height: 8px;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        .stats-value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .mini-stat {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 20px;
            }
            
            body {
                padding-top: 0;
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="layanan.php" class="nav-link">
                        <i class="fas fa-concierge-bell"></i> Layanan
                    </a>
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                    <a href="laporan.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    <a href="penggunaan-layanan.php" class="nav-link">
                        <i class="fas fa-clipboard-list"></i> Penggunaan
                    </a>
                    <a href="/tes/login.php" class="nav-link mt-5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Dashboard Content -->
                <div id="dashboard">
                    <h2 class="mb-4">Dasbor Layanan IT</h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-primary text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-concierge-bell"></i>
                                    </div>
                                    <div class="stats-value"><?php echo $total_services; ?></div>
                                    <div class="stats-label">Layanan</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-success text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stats-value"><?php echo $total_users; ?></div>
                                    <div class="stats-label">Pengguna</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-warning text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="stats-value"><?php echo $active_tickets; ?></div>
                                    <div class="stats-label">Tiket Aktif</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-info text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="stats-value"><?php echo $new_requests; ?></div>
                                    <div class="stats-label">Permintaan Baru</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-secondary text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-value"><?php echo $completed_this_month; ?></div>
                                    <div class="stats-label">Selesai Bulan Ini</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="card card-dashboard bg-dark text-white">
                                <div class="card-body text-center">
                                    <div class="card-icon mb-2">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="stats-value">
                                        <?php 
                                        $total_tickets = $active_tickets + $new_requests + $completed_this_month;
                                        echo $total_tickets > 0 ? round(($completed_this_month / $total_tickets) * 100) : 0;
                                        ?>%
                                    </div>
                                    <div class="stats-label">Tingkat Penyelesaian</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Aktivitas Layanan Terbaru</h5>
                                    <a href="penggunaan-layanan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Layanan</th>
                                                    <th>Pengguna</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($recent_activities) > 0): ?>
                                                    <?php foreach ($recent_activities as $activity): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                                            <td><?php echo htmlspecialchars($activity['fullname']); ?></td>
                                                            <td><?php echo date('d M Y', strtotime($activity['created_at'])); ?></td>
                                                            <td>
                                                                <?php 
                                                                $badge_class = '';
                                                                switch ($activity['status']) {
                                                                    case 'Selesai':
                                                                        $badge_class = 'bg-success';
                                                                        break;
                                                                    case 'Proses':
                                                                        $badge_class = 'bg-warning text-dark';
                                                                        break;
                                                                    case 'Ditugaskan':
                                                                        $badge_class = 'bg-primary';
                                                                        break;
                                                                    case 'Tertunda':
                                                                        $badge_class = 'bg-danger';
                                                                        break;
                                                                    default:
                                                                        $badge_class = 'bg-secondary';
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $activity['status']; ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4">Tidak ada aktivitas terbaru</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white">
                                            <h5 class="card-title mb-0">Distribusi Kategori Layanan</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="categoryChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white">
                                            <h5 class="card-title mb-0">Status Tiket</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="statusChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Statistik Layanan per Kategori</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($service_stats) > 0): ?>
                                        <?php foreach ($service_stats as $stat): 
                                            $percentage = $total_services_by_category > 0 ? round(($stat['count'] / $total_services_by_category) * 100) : 0;
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo htmlspecialchars($stat['category']); ?></span>
                                                <span><?php echo $stat['count']; ?> (<?php echo $percentage; ?>%)</span>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-center">Tidak ada data layanan</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm mt-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Layanan Paling Populer</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    try {
                                        $stmt = $pdo->query("
                                            SELECT s.name, COUNT(st.id) as request_count 
                                            FROM services s 
                                            LEFT JOIN service_tickets st ON s.id = st.service_id 
                                            GROUP BY s.id 
                                            ORDER BY request_count DESC 
                                            LIMIT 5
                                        ");
                                        $popular_services = $stmt->fetchAll();
                                        
                                        if (count($popular_services) > 0):
                                            foreach ($popular_services as $service):
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                            <div><?php echo htmlspecialchars($service['name']); ?></div>
                                            <span class="badge bg-primary"><?php echo $service['request_count']; ?> permintaan</span>
                                        </div>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                        <p class="text-center">Tidak ada data layanan</p>
                                        <?php
                                        endif;
                                    } catch (PDOException $e) {
                                        echo "<p class='text-center'>Error: " . $e->getMessage() . "</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Category Chart
            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                const categoryChart = new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            <?php 
                            if (count($service_stats) > 0) {
                                foreach ($service_stats as $stat) {
                                    echo "'" . htmlspecialchars($stat['category']) . "',";
                                }
                            }
                            ?>
                        ],
                        datasets: [{
                            data: [
                                <?php 
                                if (count($service_stats) > 0) {
                                    foreach ($service_stats as $stat) {
                                        echo $stat['count'] . ",";
                                    }
                                }
                                ?>
                            ],
                            backgroundColor: [
                                'rgba(30, 67, 89, 0.7)',
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(220, 53, 69, 0.7)',
                                'rgba(108, 117, 125, 0.7)'
                            ],
                            borderColor: [
                                'rgba(30, 67, 89, 1)',
                                'rgba(13, 110, 253, 1)',
                                'rgba(25, 135, 84, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(220, 53, 69, 1)',
                                'rgba(108, 117, 125, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // Status Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                // Prepare data for status chart
                const statusLabels = ['Ditugaskan', 'Proses', 'Selesai', 'Tertunda'];
                const statusData = [0, 0, 0, 0];
                
                <?php 
                if (count($ticket_status_stats) > 0) {
                    foreach ($ticket_status_stats as $stat) {
                        $index = 0;
                        switch ($stat['status']) {
                            case 'Ditugaskan': $index = 0; break;
                            case 'Proses': $index = 1; break;
                            case 'Selesai': $index = 2; break;
                            case 'Tertunda': $index = 3; break;
                        }
                        echo "statusData[$index] = " . $stat['count'] . ";";
                    }
                }
                ?>
                
                const statusChart = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(220, 53, 69, 0.7)'
                            ],
                            borderColor: [
                                'rgba(13, 110, 253, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(25, 135, 84, 1)',
                                'rgba(220, 53, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>