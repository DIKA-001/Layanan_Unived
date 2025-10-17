<?php
require_once 'koneksi.php';

// Proses form tambah layanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, category, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $category, $status]);
        
        $success = "Layanan berhasil ditambahkan!";
    } catch (PDOException $e) {
        $error = "Gagal menambahkan layanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Layanan - Puskom UNIVED</title>
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
            padding-top: 70px; /* Memberi ruang untuk topbar yang fixed */
        }
        
        /* Sidebar tetap (fixed) */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 16.666667%; /* Lebar sesuai dengan col-md-3 col-lg-2 */
            background-color: var(--university-color);
            color: white;
            z-index: 1000;
            overflow-y: auto; /* Memungkinkan scroll jika konten terlalu panjang */
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
        
        /* Topbar tetap (fixed) */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 16.666667%; /* Sama dengan lebar sidebar */
            z-index: 999;
            background-color: #f8f9fa;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        
        /* Konten utama */
        .main-content {
            margin-left: 16.666667%; /* Sama dengan lebar sidebar */
            padding: 20px;
            width: calc(100% - 16.666667%); /* Lebar total dikurangi sidebar */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Fixed) -->
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
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> Manajemen User
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

            <!-- Topbar (Fixed) -->
            <nav class="navbar navbar-expand-lg navbar-light topbar">
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

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Form Tambah Layanan -->
                <div id="tambah-layanan">
                    <h2 class="mb-4">Tambah Layanan Baru</h2>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Layanan</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategori</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Jaringan">Jaringan</option>
                                        <option value="Perangkat Keras">Perangkat Keras</option>
                                        <option value="Perangkat Lunak">Perangkat Lunak</option>
                                        <option value="Email">Email</option>
                                        <option value="Sistem Informasi">Sistem Informasi</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Non-Aktif">Non-Aktif</option>
                                        <option value="Maintenance">Maintenance</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan Layanan</button>
                                <a href="layanan.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>