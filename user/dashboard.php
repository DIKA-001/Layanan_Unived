<?php
// Koneksi ke database
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "it_puskom";

$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika belum login
    header("Location: login.php");
    exit();
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];

// Query untuk mendapatkan data user
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $username = $user['username'];
    $fullname = $user['fullname'];
    $email = $user['email'];
    $department = $user['department'];
} else {
    // Jika user tidak ditemukan, redirect ke login
    session_destroy();
    header("Location: login.php");
    exit();
}

// Query untuk mendapatkan jumlah tiket berdasarkan status untuk user ini
$ticket_stats_query = "
    SELECT 
        status,
        COUNT(*) as count 
    FROM service_tickets 
    WHERE user_id = $user_id 
    GROUP BY status
";
$ticket_stats_result = $conn->query($ticket_stats_query);

$ticket_stats = array(
    'Ditugaskan' => 0,
    'Proses' => 0,
    'Selesai' => 0,
    'Tertunda' => 0
);

while ($row = $ticket_stats_result->fetch_assoc()) {
    $ticket_stats[$row['status']] = $row['count'];
}

// Query untuk mendapatkan tiket terbaru user (maksimal 3)
$recent_tickets_query = "
    SELECT 
        st.id, 
        st.title, 
        st.status, 
        st.created_at,
        s.name as service_name
    FROM service_tickets st
    LEFT JOIN services s ON st.service_id = s.id
    WHERE st.user_id = $user_id 
    ORDER BY st.created_at DESC 
    LIMIT 3
";
$recent_tickets_result = $conn->query($recent_tickets_query);

// Query untuk mendapatkan jumlah layanan aktif
$active_services_query = "SELECT COUNT(*) as count FROM services WHERE status = 'Aktif'";
$active_services_result = $conn->query($active_services_query);
$active_services = $active_services_result->fetch_assoc()['count'];

// Query untuk mendapatkan jumlah tiket yang diselesaikan (global)
$completed_tickets_query = "SELECT COUNT(*) as count FROM service_tickets WHERE status = 'Selesai'";
$completed_tickets_result = $conn->query($completed_tickets_query);
$completed_tickets = $completed_tickets_result->fetch_assoc()['count'];

// Query untuk mendapatkan jumlah user (global)
$total_users_query = "SELECT COUNT(*) as count FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['count'];

// Tutup koneksi
$conn->close();
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
            --university-light: #2a5d7a;
            --university-gradient: linear-gradient(135deg, #1e4359 0%, #2a5d7a 100%);
            --accent-color: #ff6b6b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar-custom {
            background: var(--university-gradient);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--university-gradient), url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 5rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 30px 30px;
        }
        
        .hero-content {
            max-width: 700px;
        }
        
        .hero-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }
        
        /* Cards */
        .service-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            height: 100%;
            overflow: hidden;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            font-size: 2.8rem;
            color: var(--university-color);
            margin-bottom: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .service-card:hover .card-icon {
            transform: scale(1.1);
            color: var(--university-light);
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--university-gradient);
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(30, 67, 89, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--university-color);
            color: var(--university-color);
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--university-gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(30, 67, 89, 0.2);
        }
        
        /* Status Badges */
        .status-badge {
            padding: 0.5em 1.2em;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        /* Footer */
        .footer {
            background: var(--university-gradient);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 5rem;
        }
        
        .footer-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
            margin-bottom: 12px;
        }
        
        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
        }
        
        /* Animations */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        /* Stats Section */
        .stats-section {
            background: var(--university-gradient);
            color: white;
            padding: 4rem 0;
            border-radius: 30px;
            margin: 4rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Testimonials */
        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin: 1rem 0.5rem;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            font-size: 5rem;
            color: var(--university-color);
            opacity: 0.1;
            position: absolute;
            top: 10px;
            left: 20px;
            line-height: 1;
        }
        
        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--university-color);
        }
        
        /* How it Works */
        .how-it-works {
            padding: 4rem 0;
        }
        
        .step-card {
            text-align: center;
            padding: 2rem 1.5rem;
            border-radius: 16px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: var(--university-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0 auto 1.5rem;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 0;
                text-align: center;
            }
            
            .hero-content {
                margin: 0 auto;
            }
            
            .stats-section {
                border-radius: 20px;
            }
            
            .stat-item {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-laptop-code me-2"></i>Puskom UNIVED
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tickets.php">Tiket Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="request.php">Ajukan Permintaan</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($fullname); ?>&background=ffffff&color=1e4359&size=36" class="user-avatar me-2">
                            <span><?php echo htmlspecialchars($fullname); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_profile.php"><i class="fas fa-user me-2"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item" href="user_settings.php"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/tes/login.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section mt-5 pt-5">
        <div class="container">
            <div class="hero-content animate__animated animate__fadeInUp">
                <span class="hero-badge animate__animated animate__fadeIn animate__delay-1s"><i class="fas fa-rocket me-2"></i>Layanan IT Terdepan</span>
                <h1 class="display-4 fw-bold mb-3">Selamat Datang, <?php echo htmlspecialchars($fullname); ?>!</h1>
                <p class="lead mb-4">Kami menyediakan layanan teknologi informasi terpercaya untuk mendukung aktivitas akademik dan administrasi di Universitas Dehasen Bengkulu</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="request.php" class="btn btn-light btn-lg"><i class="fas fa-plus-circle me-2"></i>Ajukan Permintaan</a>
                    <a href="services.php" class="btn btn-outline-light btn-lg"><i class="fas fa-concierge-bell me-2"></i>Lihat Layanan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container">
        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $ticket_stats['Selesai']; ?>">0</div>
                            <div class="stat-label">Tiket Diselesaikan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $ticket_stats['Proses']; ?>">0</div>
                            <div class="stat-label">Tiket Diproses</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $ticket_stats['Ditugaskan']; ?>">0</div>
                            <div class="stat-label">Tiket Ditugaskan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo array_sum($ticket_stats); ?>">0</div>
                            <div class="stat-label">Total Tiket</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="mb-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Layanan Tersedia</h2>
                <p class="lead text-muted">Pilih dari berbagai layanan teknologi informasi yang kami sediakan</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <h5 class="card-title fw-bold">Jaringan & Internet</h5>
                            <p class="card-text">Layanan akses jaringan internet, WiFi kampus, dan troubleshooting koneksi</p>
                            <a href="services.php#jaringan" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <h5 class="card-title fw-bold">Software</h5>
                            <p class="card-text">Instalasi, update, dan troubleshooting perangkat lunak akademik</p>
                            <a href="services.php#software" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <h5 class="card-title fw-bold">Hardware</h5>
                            <p class="card-text">Perbaikan dan pemeliharaan perangkat keras komputer dan printer</p>
                            <a href="services.php#hardware" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h5 class="card-title fw-bold">Akun</h5>
                            <p class="card-text">Manajemen akun email institusi, portal akademik, dan sistem informasi</p>
                            <a href="services.php#akun" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h5 class="card-title fw-bold">Data</h5>
                            <p class="card-text">Permintaan data akademik, backup data, dan konsultasi manajemen data</p>
                            <a href="services.php#data" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h5 class="card-title fw-bold">Lainnya</h5>
                            <p class="card-text">Layanan TI lainnya yang tidak termasuk dalam kategori di atas</p>
                            <a href="services.php#lainnya" class="btn btn-outline-primary mt-3">Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Cara Menggunakan Layanan</h2>
                <p class="lead text-muted">Hanya perlu 3 langkah mudah untuk mendapatkan bantuan IT</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h5 class="fw-bold">Pilih Layanan</h5>
                        <p>Pilih layanan yang Anda butuhkan dari katalog layanan kami</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h5 class="fw-bold">Ajukan Permintaan</h5>
                        <p>Isi formulir permintaan layanan dengan detail yang jelas</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h5 class="fw-bold">Lacak Status</h5>
                        <p>Pantau perkembangan tiket Anda melalui dashboard</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Tickets Section -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Tiket Terbaru Saya</h2>
                <a href="tickets.php" class="btn btn-outline-primary">Lihat Semua <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Tiket</th>
                                    <th>Layanan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($recent_tickets_result->num_rows > 0) {
                                    while($ticket = $recent_tickets_result->fetch_assoc()) {
                                        $ticket_id = $ticket['id'];
                                        $service_name = $ticket['service_name'];
                                        $ticket_title = $ticket['title'];
                                        $status = $ticket['status'];
                                        $created_at = date('d M Y', strtotime($ticket['created_at']));
                                        
                                        // Tentukan kelas badge berdasarkan status
                                        $badge_class = '';
                                        switch($status) {
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
                                                $badge_class = 'bg-secondary';
                                                break;
                                            default:
                                                $badge_class = 'bg-secondary';
                                        }
                                        
                                        echo "<tr>
                                            <td class='fw-bold'>#TK$ticket_id</td>
                                            <td>$ticket_title</td>
                                            <td>$created_at</td>
                                            <td><span class='badge $badge_class status-badge'>$status</span></td>
                                            <td class='text-end'><a href='ticket_detail.php?id=$ticket_id' class='btn btn-sm btn-outline-primary'>Detail</a></td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='5' class='text-center py-4'>Belum ada tiket layanan. <a href='request.php' class='text-primary'>Ajukan permintaan layanan</a></td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Puskom UNIVED</h5>
                    <p>Pusat Komputer Universitas Dehasen Bengkulu</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Jl. Meranti Raya No. 32, Bengkulu</p>
                    <p><i class="fas fa-envelope me-2"></i>puskom@unived.ac.id</p>
                    <p><i class="fas fa-phone me-2"></i>(0736) 123456</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h5 class="footer-title">Tautan</h5>
                    <a href="dashboard.php" class="footer-link">Beranda</a>
                    <a href="services.php" class="footer-link">Layanan</a>
                    <a href="tickets.php" class="footer-link">Tiket Saya</a>
                    <a href="request.php" class="footer-link">Ajukan Permintaan</a>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5 class="footer-title">Jam Operasional</h5>
                    <p>Senin - Kamis: 08.00 - 16.00 WIB</p>
                    <p>Jumat: 08.00 - 16.30 WIB</p>
                    <p>Sabtu: 08.00 - 14.00 WIB</p>
                    <p>Minggu & Hari Libur: Tutup</p>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5 class="footer-title">Ikuti Kami</h5>
                    <div class="d-flex mt-3">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                    <div class="mt-4">
                        <a href="login.php" class="btn btn-outline-light btn-sm">Login Admin</a>
                    </div>
                </div>
            </div>
            <hr class="bg-light my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2023 Puskom UNIVED. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap & Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi counter untuk statistik
        document.addEventListener('DOMContentLoaded', function() {
            // Counter animation
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200;
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
                
                function updateCount() {
                    const current = +counter.innerText;
                    if (current < target) {
                        counter.innerText = Math.ceil(current + increment);
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target;
                    }
                }
            });
            
            // Animasi scroll
            const scrollElements = document.querySelectorAll('.service-card, .step-card');
            const elementInView = (el, dividend = 1) => {
                const elementTop = el.getBoundingClientRect().top;
                return (
                    elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend
                );
            };
            
            const displayScrollElement = (element) => {
                element.classList.add('animate__animated', 'animate__fadeInUp');
            };
            
            const hideScrollElement = (element) => {
                element.classList.remove('animate__animated', 'animate__fadeInUp');
            };
            
            const handleScrollAnimation = () => {
                scrollElements.forEach((el) => {
                    if (elementInView(el, 1.2)) {
                        displayScrollElement(el);
                    } else {
                        hideScrollElement(el);
                    }
                });
            };
            
            window.addEventListener('scroll', () => {
                handleScrollAnimation();
            });
            
            // Inisialisasi animasi pertama kali
            handleScrollAnimation();
        });
    </script>
</body>
</html>