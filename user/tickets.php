<?php
// Koneksi ke database
session_start();
ob_start(); // Tambahkan ini untuk mengatasi masalah headers

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

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query untuk mendapatkan tiket user
$tickets_query = "
    SELECT 
        st.id, 
        st.title, 
        st.description,
        st.status, 
        st.priority,
        st.created_at,
        st.updated_at,
        st.completed_at,
        s.name as service_name,
        s.category as service_category,
        u_assigned.fullname as assigned_to_name
    FROM service_tickets st
    LEFT JOIN services s ON st.service_id = s.id
    LEFT JOIN users u_assigned ON st.assigned_to = u_assigned.id
    WHERE st.user_id = $user_id 
";

if ($status_filter != 'all') {
    $tickets_query .= " AND st.status = '$status_filter'";
}

$tickets_query .= " ORDER BY st.created_at DESC";

$tickets_result = $conn->query($tickets_query);

// Tutup koneksi
$conn->close();
ob_end_clean(); // Bersihkan output buffer sebelum mengirim HTML
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Puskom Universitas Dehasen Bengkulu</title>
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
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        /* Ticket Detail Modal */
        .ticket-detail-modal .modal-content {
            border-radius: 16px;
            border: none;
        }
        
        .ticket-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .ticket-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--university-color);
            border: 2px solid white;
            box-shadow: 0 0 0 2px var(--university-color);
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .filter-section .btn-group {
                width: 100%;
                margin-bottom: 1rem;
            }
            
            .filter-section .btn-group .btn {
                flex: 1;
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
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 fw-bold">Tiket Layanan Saya</h1>
                <p class="text-muted">Kelola dan pantau semua permintaan layanan IT Anda</p>
            </div>
            <a href="request.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Permintaan Baru
            </a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0">Filter Tiket</h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        <div class="btn-group" role="group">
                            <a href="tickets.php?status=all" class="btn btn-outline-primary <?php echo $status_filter == 'all' ? 'active' : ''; ?>">Semua</a>
                            <a href="tickets.php?status=Ditugaskan" class="btn btn-outline-primary <?php echo $status_filter == 'Ditugaskan' ? 'active' : ''; ?>">Ditugaskan</a>
                            <a href="tickets.php?status=Proses" class="btn btn-outline-primary <?php echo $status_filter == 'Proses' ? 'active' : ''; ?>">Proses</a>
                            <a href="tickets.php?status=Selesai" class="btn btn-outline-primary <?php echo $status_filter == 'Selesai' ? 'active' : ''; ?>">Selesai</a>
                            <a href="tickets.php?status=Tertunda" class="btn btn-outline-primary <?php echo $status_filter == 'Tertunda' ? 'active' : ''; ?>">Tertunda</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if ($tickets_result->num_rows > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID Tiket</th>
                                <th>Layanan</th>
                                <th>Prioritas</th>
                                <th>Tanggal Dibuat</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ticket = $tickets_result->fetch_assoc()) : 
                                $ticket_id = $ticket['id'];
                                $service_name = $ticket['service_name'];
                                $ticket_title = $ticket['title'];
                                $status = $ticket['status'];
                                $priority = $ticket['priority'];
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
                                
                                // Tentukan kelas badge prioritas
                                $priority_badge_class = '';
                                switch($priority) {
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
                            ?>
                            <tr>
                                <td class="fw-bold">#TK<?php echo $ticket_id; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($ticket_title); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($service_name); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge <?php echo $priority_badge_class; ?> status-badge"><?php echo $priority; ?></span></td>
                                <td><?php echo $created_at; ?></td>
                                <td><span class="badge <?php echo $badge_class; ?> status-badge"><?php echo $status; ?></span></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-ticket" data-bs-toggle="modal" data-bs-target="#ticketDetailModal" data-id="<?php echo $ticket_id; ?>">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="text-center py-5">
                    <div class="py-5">
                        <i class="fas fa-ticket-alt fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">Belum ada tiket</h4>
                        <p class="text-muted">Anda belum memiliki tiket layanan. Ajukan permintaan layanan baru.</p>
                        <a href="request.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus-circle me-2"></i>Ajukan Permintaan
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ticket Detail Modal -->
    <div class="modal fade ticket-detail-modal" id="ticketDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tiket #TK<span id="ticketId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Tiket</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Judul</th>
                                    <td id="ticketTitle"></td>
                                </tr>
                                <tr>
                                    <th>Layanan</th>
                                    <td id="ticketService"></td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td id="ticketCategory"></td>
                                </tr>
                                <tr>
                                    <th>Prioritas</th>
                                    <td><span id="ticketPriority" class="badge status-badge"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Status</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Status</th>
                                    <td><span id="ticketStatus" class="badge status-badge"></span></td>
                                </tr>
                                <tr>
                                    <th>Dibuat</th>
                                    <td id="ticketCreated"></td>
                                </tr>
                                <tr>
                                    <th>Diperbarui</th>
                                    <td id="ticketUpdated"></td>
                                </tr>
                                <tr>
                                    <th>Ditugaskan ke</th>
                                    <td id="ticketAssigned"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <h6>Deskripsi</h6>
                    <div class="card mb-4">
                        <div class="card-body">
                            <p id="ticketDescription" class="mb-0"></p>
                        </div>
                    </div>
                    
                    <h6>Riwayat Tiket</h6>
                    <div class="ticket-timeline" id="ticketTimeline">
                        <!-- Timeline akan diisi dengan JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
    <script>
        // Fungsi untuk menampilkan detail tiket
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-ticket');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const ticketId = this.getAttribute('data-id');
                    loadTicketDetails(ticketId);
                });
            });
            
            function loadTicketDetails(ticketId) {
                // Buat AJAX request untuk mengambil data tiket dari server
                fetch('get_ticket_details.php?id=' + ticketId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Isi modal dengan data dari server
                            document.getElementById('ticketId').textContent = data.ticket.id;
                            document.getElementById('ticketTitle').textContent = data.ticket.title;
                            document.getElementById('ticketService').textContent = data.ticket.service_name;
                            document.getElementById('ticketCategory').textContent = data.ticket.service_category;
                            
                            const priorityBadge = document.getElementById('ticketPriority');
                            priorityBadge.textContent = data.ticket.priority;
                            
                            // Set kelas badge berdasarkan prioritas
                            switch(data.ticket.priority) {
                                case 'Tinggi':
                                    priorityBadge.className = 'badge bg-danger status-badge';
                                    break;
                                case 'Sedang':
                                    priorityBadge.className = 'badge bg-warning text-dark status-badge';
                                    break;
                                case 'Rendah':
                                    priorityBadge.className = 'badge bg-info status-badge';
                                    break;
                                default:
                                    priorityBadge.className = 'badge bg-secondary status-badge';
                            }
                            
                            const statusBadge = document.getElementById('ticketStatus');
                            statusBadge.textContent = data.ticket.status;
                            
                            // Set kelas badge berdasarkan status
                            switch(data.ticket.status) {
                                case 'Selesai':
                                    statusBadge.className = 'badge bg-success status-badge';
                                    break;
                                case 'Proses':
                                    statusBadge.className = 'badge bg-warning text-dark status-badge';
                                    break;
                                case 'Ditugaskan':
                                    statusBadge.className = 'badge bg-primary status-badge';
                                    break;
                                case 'Tertunda':
                                    statusBadge.className = 'badge bg-secondary status-badge';
                                    break;
                                default:
                                    statusBadge.className = 'badge bg-secondary status-badge';
                            }
                            
                            document.getElementById('ticketCreated').textContent = new Date(data.ticket.created_at).toLocaleDateString('id-ID', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            
                            document.getElementById('ticketUpdated').textContent = new Date(data.ticket.updated_at).toLocaleDateString('id-ID', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            
                            document.getElementById('ticketAssigned').textContent = data.ticket.assigned_to_name || 'Belum ditugaskan';
                            document.getElementById('ticketDescription').textContent = data.ticket.description || 'Tidak ada deskripsi';
                            
                            // Isi timeline (dalam implementasi nyata, ini akan diambil dari tabel activity_logs)
                            const timeline = document.getElementById('ticketTimeline');
                            timeline.innerHTML = `
                                <div class="timeline-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Tiket dibuat</h6>
                                            <p class="card-text">Tiket telah dibuat dan sedang menunggu penugasan.</p>
                                            <div class="timeline-date">${new Date(data.ticket.created_at).toLocaleDateString('id-ID', { 
                                                year: 'numeric', 
                                                month: 'short', 
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            if (data.ticket.assigned_to_name) {
                                timeline.innerHTML += `
                                    <div class="timeline-item">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">Tiket ditugaskan</h6>
                                                <p class="card-text">Tiket telah ditugaskan kepada ${data.ticket.assigned_to_name}.</p>
                                                <div class="timeline-date">${new Date(data.ticket.updated_at).toLocaleDateString('id-ID', { 
                                                    year: 'numeric', 
                                                    month: 'short', 
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            alert('Gagal memuat detail tiket: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat detail tiket.');
                    });
            }
        });
    </script>
</body>
</html>