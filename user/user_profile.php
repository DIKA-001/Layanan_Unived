<?php
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
    $phone = $user['phone'];
    $gender = $user['gender'];
    $department = $user['department'];
    $created_at = $user['created_at'];
} else {
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

// Query untuk mendapatkan tiket terbaru user (maksimal 5)
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
    LIMIT 5
";
$recent_tickets_result = $conn->query($recent_tickets_query);

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Puskom UNIVED</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        
        /* Profile Section */
        .profile-section {
            margin-top: 6rem;
            margin-bottom: 3rem;
        }
        
        .profile-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            overflow: hidden;
        }
        
        .profile-header {
            background: var(--university-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 1rem;
        }
        
        .profile-body {
            padding: 2rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--university-color);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: #555;
        }
        
        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--university-color);
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 1rem;
            color: #666;
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
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
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <div class="container profile-section">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($fullname); ?>&background=ffffff&color=1e4359&size=120" class="profile-avatar">
                        <h4><?php echo htmlspecialchars($fullname); ?></h4>
                        <p class="mb-0"><?php echo htmlspecialchars($department); ?></p>
                    </div>
                    <div class="profile-body">
                        <div class="info-item">
                            <div class="info-label">Username</div>
                            <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Nomor Telepon</div>
                            <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Jenis Kelamin</div>
                            <div class="info-value"><?php echo ($gender == 'L') ? 'Laki-laki' : 'Perempuan'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Departemen</div>
                            <div class="info-value"><?php echo htmlspecialchars($department); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Bergabung Sejak</div>
                            <div class="info-value"><?php echo date('d M Y', strtotime($created_at)); ?></div>
                        </div>
                        <a href="user_settings.php" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-edit me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <h3 class="fw-bold mb-4">Statistik Tiket Layanan</h3>
                
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $ticket_stats['Selesai']; ?></div>
                            <div class="stats-label">Tiket Diselesaikan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $ticket_stats['Proses']; ?></div>
                            <div class="stats-label">Tiket Diproses</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $ticket_stats['Ditugaskan']; ?></div>
                            <div class="stats-label">Tiket Ditugaskan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo array_sum($ticket_stats); ?></div>
                            <div class="stats-label">Total Tiket</div>
                        </div>
                    </div>
                </div>
                
                <h4 class="fw-bold mb-3">Tiket Terbaru</h4>
                
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
                
                <div class="text-center mt-4">
                    <a href="tickets.php" class="btn btn-outline-primary">Lihat Semua Tiket</a>
                </div>
            </div>
        </div>
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
</body>
</html>