<?php
require_once 'koneksi.php';

// Cek apakah parameter ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php?error=ID pengguna tidak valid");
    exit();
}

$id = $_GET['id'];

// Ambil data pengguna berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: users.php?error=Pengguna tidak ditemukan");
        exit();
    }
} catch (PDOException $e) {
    $error = "Gagal memuat data pengguna: " . $e->getMessage();
}

// Proses update pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];
    
    // Cek apakah password diisi (opsional update)
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    try {
        if ($password) {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, gender = ?, department = ?, password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$fullname, $email, $username, $phone, $gender, $department, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, phone = ?, gender = ?, department = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$fullname, $email, $username, $phone, $gender, $department, $id]);
        }
        
        $success = "Pengguna berhasil diperbarui!";
        header("Location: users.php?success=" . urlencode($success));
        exit();
    } catch (PDOException $e) {
        $error = "Gagal memperbarui pengguna: " . $e->getMessage();
    }
}

// Tampilkan pesan error jika ada
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Puskom UNIVED</title>
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
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--university-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
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
                        <div class="collapse navbar-collapse" id="navbarContent">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a href="users.php" class="nav-link">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Pengguna
                                    </a>
                                </li>
                            </ul>
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

                <!-- Edit Pengguna Content -->
                <div id="edit-user">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Edit Pengguna</h2>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="form-container">
                                <div class="avatar-preview">
                                    <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                </div>
                                
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fullname" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                                   value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Nomor Telepon</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="gender" class="form-label">Jenis Kelamin</label>
                                            <select class="form-select" id="gender" name="gender" required>
                                                <option value="L" <?php echo $user['gender'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                                <option value="P" <?php echo $user['gender'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label">Departemen</label>
                                            <select class="form-select" id="department" name="department" required>
                                                <option value="Keuangan" <?php echo $user['department'] == 'Keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                                                <option value="Marketing" <?php echo $user['department'] == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                                <option value="Operasional" <?php echo $user['department'] == 'Operasional' ? 'selected' : ''; ?>>Operasional</option>
                                                <option value="Lainnya" <?php echo $user['department'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="form-text">Minimal 6 karakter</div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="users.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>