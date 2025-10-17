<?php
// Mulai session di awal
session_start();

// Koneksi ke database
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

// Query untuk mendapatkan data user (menggunakan prepared statement)
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

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

// Query untuk mendapatkan semua layanan aktif
$services_query = "SELECT * FROM services WHERE status = 'Aktif' ORDER BY category, name";
$services_result = $conn->query($services_query);

// Kelompokkan layanan berdasarkan kategori
$services_by_category = array(
    'Jaringan & Internet' => array(),
    'Software' => array(),
    'Hardware' => array(),
    'Akun' => array(),
    'Data' => array(),
    'Lainnya' => array()
);

if ($services_result) {
    while ($service = $services_result->fetch_assoc()) {
        $services_by_category[$service['category']][] = $service;
    }
}

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
        /* CSS yang sama seperti di dashboard */
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
        
        /* Service Detail */
        .service-detail-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
        }
        
        .service-detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
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
        }
        
        /* Category Section */
        .category-section {
            padding: 3rem 0;
        }
        
        .category-header {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .category-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--university-color);
            border-radius: 3px;
        }
        
        /* Service List */
        .service-list-item {
            border-left: 4px solid var(--university-color);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .service-list-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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
                        <a class="nav-link" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="services.php">Layanan</a>
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
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
                <span class="hero-badge animate__animated animate__fadeIn animate__delay-1s"><i class="fas fa-concierge-bell me-2"></i>Layanan Tersedia</span>
                <h1 class="display-4 fw-bold mb-3">Layanan IT Puskom</h1>
                <p class="lead mb-4">Temukan berbagai layanan teknologi informasi yang kami sediakan untuk mendukung aktivitas akademik dan administrasi di Universitas Dehasen Bengkulu</p>
                <a href="request.php" class="btn btn-light btn-lg"><i class="fas fa-plus-circle me-2"></i>Ajukan Permintaan</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container mt-5">
        <!-- Services by Category -->
        <?php foreach ($services_by_category as $category => $services): ?>
            <?php if (!empty($services)): ?>
                <section id="<?php echo strtolower(str_replace(' ', '-', $category)); ?>" class="category-section">
                    <div class="category-header">
                        <h2 class="fw-bold"><?php echo htmlspecialchars($category); ?></h2>
                        <p class="text-muted">Layanan terkait <?php echo htmlspecialchars($category); ?> yang tersedia</p>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($services as $service): ?>
                            <div class="col-md-6 mb-4">
                                <div class="service-detail-card card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($service['name']); ?></h5>
                                            <span class="badge bg-success status-badge">Aktif</span>
                                        </div>
                                        <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                        <div class="service-meta mt-4">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted"><i class="fas fa-user me-1"></i> Penanggung Jawab:</small>
                                                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($service['responsible_person']); ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted"><i class="fas fa-clock me-1"></i> Perkiraan Waktu:</small>
                                                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($service['completion_estimate']); ?> hari</p>
                                                </div>
                                            </div>
                                            <?php if ($service['requires_approval']): ?>
                                                <div class="mt-3">
                                                    <span class="badge bg-info"><i class="fas fa-check-circle me-1"></i> Memerlukan Persetujuan</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-4">
                                            <a href="request.php?service_id=<?php echo $service['id']; ?>" class="btn btn-primary">Ajukan Layanan Ini</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi scroll untuk navigasi kategori
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll untuk anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Animasi scroll untuk elemen layanan
            const serviceCards = document.querySelectorAll('.service-detail-card');
            const elementInView = (el, dividend = 1) => {
                const elementTop = el.getBoundingClientRect().top;
                return (
                    elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend
                );
            };
            
            const displayScrollElement = (element) => {
                element.classList.add('animate__animated', 'animate__fadeInUp');
            };
            
            const handleScrollAnimation = () => {
                serviceCards.forEach((el) => {
                    if (elementInView(el, 1.2)) {
                        displayScrollElement(el);
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