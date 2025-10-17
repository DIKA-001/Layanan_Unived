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
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ambil ID tiket dari parameter URL
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ticket_id == 0) {
    header("Location: tickets.php");
    exit();
}

// Query untuk mendapatkan detail tiket
$ticket_query = "
    SELECT 
        st.*,
        s.name as service_name,
        s.category as service_category,
        u_assigned.fullname as assigned_to_name,
        u_created.fullname as created_by_name
    FROM service_tickets st
    LEFT JOIN services s ON st.service_id = s.id
    LEFT JOIN users u_assigned ON st.assigned_to = u_assigned.id
    LEFT JOIN users u_created ON st.user_id = u_created.id
    WHERE st.id = $ticket_id AND st.user_id = $user_id
";

$ticket_result = $conn->query($ticket_query);

if ($ticket_result->num_rows == 0) {
    // Tiket tidak ditemukan atau user tidak memiliki akses
    header("Location: tickets.php");
    exit();
}

$ticket = $ticket_result->fetch_assoc();

// Format tanggal
$created_at = date('d M Y H:i', strtotime($ticket['created_at']));
$updated_at = date('d M Y H:i', strtotime($ticket['updated_at']));
$completed_at = $ticket['completed_at'] ? date('d M Y H:i', strtotime($ticket['completed_at'])) : '-';

// Tentukan kelas badge berdasarkan status
$badge_class = '';
switch($ticket['status']) {
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

// Tentukan kelas badge berdasarkan prioritas
$priority_badge_class = '';
switch($ticket['priority']) {
    case 'Tinggi':
        $priority_badge_class = 'bg-danger';
        break;
    case 'Sedang':
        $priority_badge_class = 'bg-warning text-dark';
        break;
    case 'Rendah':
        $priority_badge_class = 'bg-info';
        break;
    default:
        $priority_badge_class = 'bg-secondary';
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket - Puskom Universitas Dehasen Bengkulu</title>
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
        
        /* Breadcrumb */
        .breadcrumb-item a {
            text-decoration: none;
            color: var(--university-color);
        }
        
        /* Ticket Detail Card */
        .ticket-detail-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .ticket-header {
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }
        
        .ticket-body {
            padding: 1.5rem;
        }
        
        .ticket-info-item {
            margin-bottom: 1rem;
            display: flex;
        }
        
        .ticket-info-label {
            font-weight: 600;
            min-width: 150px;
            color: var(--university-color);
        }
        
        .ticket-description {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
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
        
        @media (max-width: 768px) {
            .ticket-info-item {
                flex-direction: column;
                margin-bottom: 1.5rem;
            }
            
            .ticket-info-label {
                margin-bottom: 0.5rem;
            }
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
                        <a class="nav-link active" href="tickets.php">Tiket Saya</a>
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

    <!-- Main Content Area -->
    <div class="container mt-5 pt-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="tickets.php">Tiket Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Tiket #TK<?php echo $ticket_id; ?></li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Detail Tiket Layanan</h2>
            <a href="tickets.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Ticket Detail Card -->
        <div class="card ticket-detail-card mb-5">
            <div class="ticket-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($ticket['title']); ?></h4>
                    <div>
                        <span class="badge <?php echo $badge_class; ?> status-badge me-2"><?php echo $ticket['status']; ?></span>
                        <span class="badge <?php echo $priority_badge_class; ?> status-badge"><?php echo $ticket['priority']; ?></span>
                    </div>
                </div>
                <p class="text-muted mb-0">ID: #TK<?php echo $ticket_id; ?></p>
            </div>
            
            <div class="ticket-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Layanan:</span>
                            <span><?php echo htmlspecialchars($ticket['service_name']); ?> (<?php echo $ticket['service_category']; ?>)</span>
                        </div>
                        
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Dibuat oleh:</span>
                            <span><?php echo htmlspecialchars($ticket['created_by_name']); ?></span>
                        </div>
                        
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Tanggal Dibuat:</span>
                            <span><?php echo $created_at; ?></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Ditugaskan kepada:</span>
                            <span><?php echo $ticket['assigned_to_name'] ? htmlspecialchars($ticket['assigned_to_name']) : 'Belum ditugaskan'; ?></span>
                        </div>
                        
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Terakhir Diupdate:</span>
                            <span><?php echo $updated_at; ?></span>
                        </div>
                        
                        <div class="ticket-info-item">
                            <span class="ticket-info-label">Tanggal Selesai:</span>
                            <span><?php echo $completed_at; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="ticket-description">
                    <h5 class="fw-bold mb-3">Deskripsi Permintaan</h5>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                </div>
                
                <?php if ($ticket['status'] !== 'Selesai'): ?>
                <div class="mt-4">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateTicketModal">
                        <i class="fas fa-edit me-2"></i>Update Tiket
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Update Ticket Modal -->
    <div class="modal fade" id="updateTicketModal" tabindex="-1" aria-labelledby="updateTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateTicketModalLabel">Update Tiket #TK<?php echo $ticket_id; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_ticket.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Ditugaskan" <?php echo $ticket['status'] == 'Ditugaskan' ? 'selected' : ''; ?>>Ditugaskan</option>
                                <option value="Proses" <?php echo $ticket['status'] == 'Proses' ? 'selected' : ''; ?>>Proses</option>
                                <option value="Tertunda" <?php echo $ticket['status'] == 'Tertunda' ? 'selected' : ''; ?>>Tertunda</option>
                                <option value="Selesai" <?php echo $ticket['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="Rendah" <?php echo $ticket['priority'] == 'Rendah' ? 'selected' : ''; ?>>Rendah</option>
                                <option value="Sedang" <?php echo $ticket['priority'] == 'Sedang' ? 'selected' : ''; ?>>Sedang</option>
                                <option value="Tinggi" <?php echo $ticket['priority'] == 'Tinggi' ? 'selected' : ''; ?>>Tinggi</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="update_description" class="form-label">Keterangan Update</label>
                            <textarea class="form-control" id="update_description" name="update_description" rows="3" placeholder="Tambahkan keterangan update (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
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