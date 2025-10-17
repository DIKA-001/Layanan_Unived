<?php
require_once 'koneksi.php';

// Cek apakah parameter ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: layanan.php");
    exit();
}

$id = $_GET['id'];

// Ambil data layanan berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        header("Location: layanan.php?error=notfound");
        exit();
    }
} catch (PDOException $e) {
    header("Location: layanan.php?error=dberror");
    exit();
}

// Proses form update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $responsible_person = $_POST['responsible_person'];
    $status = $_POST['status'];
    $completion_estimate = $_POST['completion_estimate'];
    $cost = $_POST['cost'];
    $requires_approval = isset($_POST['requires_approval']) ? 1 : 0;
    
    try {
        $update_stmt = $pdo->prepare("
            UPDATE services 
            SET name = ?, category = ?, description = ?, responsible_person = ?, 
                status = ?, completion_estimate = ?, cost = ?, requires_approval = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $update_stmt->execute([
            $name, $category, $description, $responsible_person, 
            $status, $completion_estimate, $cost, $requires_approval, $id
        ]);
        
        header("Location: layanan.php?message=updated");
        exit();
    } catch (PDOException $e) {
        $error_message = "Terjadi kesalahan saat memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan - Puskom UNIVED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
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
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar px-0 d-md-block collapse" id="sidebarCollapse">
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

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto px-4 py-3">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 rounded shadow-sm">
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

                <!-- Alert Messages -->
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Layanan Content -->
                <div id="edit-layanan">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Edit Layanan</h2>
                        <a href="layanan.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    
                    <div class="form-container">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($service['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Pilih Kategori</option>
                                            <option value="Jaringan & Internet" <?php echo $service['category'] == 'Jaringan & Internet' ? 'selected' : ''; ?>>Jaringan & Internet</option>
                                            <option value="Software" <?php echo $service['category'] == 'Software' ? 'selected' : ''; ?>>Software</option>
                                            <option value="Hardware" <?php echo $service['category'] == 'Hardware' ? 'selected' : ''; ?>>Hardware</option>
                                            <option value="Akun" <?php echo $service['category'] == 'Akun' ? 'selected' : ''; ?>>Akun</option>
                                            <option value="Data" <?php echo $service['category'] == 'Data' ? 'selected' : ''; ?>>Data</option>
                                            <option value="Lainnya" <?php echo $service['category'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($service['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="responsible_person" class="form-label">Penanggung Jawab</label>
                                        <input type="text" class="form-control" id="responsible_person" name="responsible_person" 
                                               value="<?php echo htmlspecialchars($service['responsible_person']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Aktif" <?php echo $service['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="Non-Aktif" <?php echo $service['status'] == 'Non-Aktif' ? 'selected' : ''; ?>>Non-Aktif</option>
                                            <option value="Maintenance" <?php echo $service['status'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="completion_estimate" class="form-label">Estimasi Penyelesaian</label>
                                        <input type="text" class="form-control" id="completion_estimate" name="completion_estimate" 
                                               value="<?php echo htmlspecialchars($service['completion_estimate']); ?>" 
                                               placeholder="Contoh: 1-3 hari kerja">
                                    </div>
                          
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="requires_approval" name="requires_approval" 
                                       value="1" <?php echo $service['requires_approval'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requires_approval">Memerlukan Persetujuan</label>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <a href="layanan.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
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
            
            // Toggle sidebar on button click
            document.querySelector('[data-bs-target="#sidebarCollapse"]').addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickInsideToggle = event.target.closest('[data-bs-target="#sidebarCollapse"]');
                
                if (!isClickInsideSidebar && !isClickInsideToggle && window.innerWidth < 768 && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>