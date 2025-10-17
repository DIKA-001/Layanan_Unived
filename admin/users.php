<?php
require_once 'koneksi.php';

// Query untuk mendapatkan data pengguna
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = array();
    $error = "Gagal memuat data pengguna: " . $e->getMessage();
}

// Proses hapus pengguna
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Pengguna berhasil dihapus!";
        header("Location: users.php?success=" . urlencode($success));
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menghapus pengguna: " . $e->getMessage();
    }
}

// Tampilkan pesan sukses jika ada
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Puskom UNIVED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--university-color);
            color: white;
            transition: all 0.3s;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
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
        
        .navbar-brand {
            font-weight: 700;
            color: var(--university-color);
        }
        
        .navbar {
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .main-content {
            padding-top: 0;
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
            vertical-align: middle;
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
            position: sticky;
            top: 0;
            background-color: var(--university-color);
            z-index: 1;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--university-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .badge-role {
            padding: 0.4em 0.6em;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-left: 40px;
            border-radius: 20px;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(30, 67, 89, 0.05);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--university-color);
            border-color: var(--university-color);
        }
        
        .pagination .page-link {
            color: var(--university-color);
        }
        
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
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
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="layanan.php" class="nav-link">
                        <i class="fas fa-concierge-bell"></i> Layanan
                    </a>
                    <a href="users.php" class="nav-link active">
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

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto main-content">
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

                <!-- Manajemen Pengguna Content -->
                <div id="users">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Manajemen Pengguna</h2>
                        <a href="tambah-user.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Tambah User
                        </a>
                    </div>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <i class="fas fa-search"></i>
                                        <input type="text" class="form-control" placeholder="Cari pengguna..." id="searchInput">
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <select class="form-select" id="filterDepartment">
                                        <option value="">Semua Departemen</option>
                                        <option value="Keuangan">Keuangan</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Operasional">Operasional</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="50px"></th>
                                            <th>Nama Lengkap</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Departemen</th>
                                            <th>Tanggal Dibuat</th>
                                            <th width="120px" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userTableBody">
                                        <?php if (count($users) > 0): ?>
                                            <?php $counter = 1; ?>
                                            <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="user-avatar">
                                                        <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                                    <small class="text-muted"><?php echo $user['gender'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-role bg-secondary"><?php echo htmlspecialchars($user['department']); ?></span>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                                <td class="action-buttons text-center">
                                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus <?php echo htmlspecialchars($user['fullname']); ?>? Tindakan ini tidak dapat dibatalkan.')" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state">
                                                        <i class="fas fa-users-slash"></i>
                                                        <h5>Belum ada pengguna</h5>
                                                        <p>Mulai dengan menambahkan pengguna baru</p>
                                                        <a href="tambah-user.php" class="btn btn-primary mt-2">
                                                            <i class="fas fa-user-plus me-1"></i> Tambah User Pertama
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (count($users) > 0): ?>
                        <div class="card-footer">
                            <nav aria-label="User pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi pencarian sederhana
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#userTableBody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const username = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                if (name.includes(searchValue) || username.includes(searchValue) || email.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Filter berdasarkan departemen
        document.getElementById('filterDepartment').addEventListener('change', function() {
            const filterValue = this.value;
            const rows = document.querySelectorAll('#userTableBody tr');
            
            rows.forEach(row => {
                const department = row.querySelector('td:nth-child(5)').textContent;
                
                if (!filterValue || department === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Mencegah navbar bergoyang saat discroll
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.navbar');
            const sidebar = document.querySelector('.sidebar');
            
            // Set tinggi sidebar sesuai dengan viewport
            sidebar.style.height = window.innerHeight + 'px';
            
            // Handler untuk resize window
            window.addEventListener('resize', function() {
                sidebar.style.height = window.innerHeight + 'px';
            });
        });
    </script>
</body>
</html>