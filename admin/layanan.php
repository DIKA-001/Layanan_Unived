<?php
require_once 'koneksi.php';

// Query untuk mendapatkan data layanan
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = array();
    $error_message = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $delete_stmt->execute([$delete_id]);
        header("Location: layanan.php?message=deleted");
        exit();
    } catch (PDOException $e) {
        $error_message = "Terjadi kesalahan saat menghapus data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan - Puskom UNIVED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding-top: 70px; /* Memberi ruang untuk fixed navbar */
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background-color: var(--university-color);
            color: white;
            transition: all 0.3s;
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
            right: 0;
            left: 250px; /* Sesuaikan dengan lebar sidebar */
            z-index: 1030;
            transition: left 0.3s;
        }
        
        .main-content {
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
            padding: 20px;
            transition: margin-left 0.3s;
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
        
        .btn-outline-primary {
            color: var(--university-color);
            border-color: var(--university-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--university-color);
            color: white;
        }
        
        .stat-card {
            border-left: 4px solid var(--university-color);
        }
        
        .logo-container {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .action-buttons {
            white-space: nowrap;
        }
        
        /* Ketika sidebar disembunyikan di mobile */
        .sidebar.collapsed {
            margin-left: -250px;
        }
        
        .sidebar.collapsed ~ .navbar {
            left: 0;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 56px; /* Kurangi padding untuk mobile */
            }
            
            .sidebar {
                margin-left: -250px;
                width: 250px;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .navbar {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .action-buttons .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebarCollapse">
        <div class="logo-container text-center">
            <h5>Puskom UNIVED</h5>
            <p class="small">Layanan IT</p>
        </div>
        <nav class="nav flex-column p-3">
             <a href="dashboard.php" class="nav-link">
             <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="layanan.php" class="nav-link active">
                <i class="fas fa-concierge-bell"></i> Layanan
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i> Manajemen
            </a>
            <a href="laporan.php" class="nav-link">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <a href="penggunaan-layanan.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i> Penggunaan Layanan
            </a>
            <a href="#" class="nav-link mt-5">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light rounded shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <button class="btn btn-sm btn-unived d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
                <i class="fas fa-bars"></i>
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Alert Messages -->
        <?php if (isset($_GET['message']) && $_GET['message'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Layanan berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Layanan Content -->
        <div id="layanan">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Daftar Layanan</h2>
                <a href="tambah-layanan.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Layanan
                </a>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Layanan</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($services) > 0): ?>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td><?php echo htmlspecialchars($service['category']); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            switch ($service['status']) {
                                                case 'Aktif':
                                                    $badge_class = 'bg-success';
                                                    break;
                                                case 'Non-Aktif':
                                                    $badge_class = 'bg-danger';
                                                    break;
                                                case 'Maintenance':
                                                    $badge_class = 'bg-warning text-dark';
                                                    break;
                                                default:
                                                    $badge_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $service['status']; ?></span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($service['created_at'])); ?></td>
                                        <td class="text-center action-buttons">
                                            <a href="edit-layanan.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $service['id']; ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                            
                                            <!-- Delete Confirmation Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $service['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus layanan "<?php echo htmlspecialchars($service['name']); ?>"?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <a href="layanan.php?delete_id=<?php echo $service['id']; ?>" class="btn btn-danger">Hapus</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada layanan tersedia</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk mengatur sidebar pada tampilan mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebarCollapse');
            const sidebarToggle = document.querySelector('[data-bs-target="#sidebarCollapse"]');
            
            // Toggle sidebar on button click
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickInsideToggle = event.target.closest('[data-bs-target="#sidebarCollapse"]');
                
                if (!isClickInsideSidebar && !isClickInsideToggle && window.innerWidth < 768 && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>