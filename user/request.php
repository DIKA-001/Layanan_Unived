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

// Perbaikan struktur tabel - pastikan kolom assigned_to bisa NULL
// Query untuk memeriksa struktur tabel
$check_table_sql = "SHOW CREATE TABLE service_tickets";
$table_result = $conn->query($check_table_sql);

if ($table_result->num_rows > 0) {
    $table_row = $table_result->fetch_assoc();
    // Jika constraint masih ada, kita coba untuk memperbaikinya
    if (strpos($table_row['Create Table'], 'CONSTRAINT `service_tickets_ibfk_3`') !== false) {
        // Hapus constraint lama
        $conn->query("ALTER TABLE service_tickets DROP FOREIGN KEY service_tickets_ibfk_3");
        // Ubah kolom agar bisa NULL
        $conn->query("ALTER TABLE service_tickets MODIFY assigned_to INT(11) NULL");
        // Tambahkan constraint baru dengan opsi ON DELETE SET NULL
        $conn->query("ALTER TABLE service_tickets ADD CONSTRAINT service_tickets_ibfk_3 FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika belum login
    header("Location: /login.php");
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
    $phone = $user['phone'];
} else {
    // Jika user tidak ditemukan, redirect ke login
    session_destroy();
    header("Location: login.php");
    exit();
}

// Query untuk mendapatkan daftar layanan aktif
$services_query = "SELECT * FROM services WHERE status = 'Aktif' ORDER BY category, name";
$services_result = $conn->query($services_query);

// Query untuk mendapatkan daftar admin/staf IT (untuk penugasan)
$staff_query = "SELECT id, fullname FROM users WHERE role = 'Admin' ORDER BY fullname";
$staff_result = $conn->query($staff_query);

// Proses form submission
$form_errors = [];
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi dan proses data form
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : 'Sedang';
    $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;

    // Validasi
    if (empty($service_id)) {
        $form_errors['service_id'] = 'Pilih layanan yang diminta';
    }

    if (empty($title)) {
        $form_errors['title'] = 'Judul permintaan harus diisi';
    } elseif (strlen($title) < 5) {
        $form_errors['title'] = 'Judul permintaan minimal 5 karakter';
    }

    if (empty($description)) {
        $form_errors['description'] = 'Deskripsi masalah harus diisi';
    } elseif (strlen($description) < 10) {
        $form_errors['description'] = 'Deskripsi masalah minimal 10 karakter';
    }

    // Jika tidak ada error, simpan ke database
    if (empty($form_errors)) {
        if ($assigned_to === null) {
            $stmt = $conn->prepare("INSERT INTO service_tickets (service_id, user_id, title, description, priority, status) VALUES (?, ?, ?, ?, ?, 'Ditugaskan')");
            $stmt->bind_param("iisss", $service_id, $user_id, $title, $description, $priority);
        } else {
            $stmt = $conn->prepare("INSERT INTO service_tickets (service_id, user_id, title, description, priority, assigned_to, status) VALUES (?, ?, ?, ?, ?, ?, 'Ditugaskan')");
            $stmt->bind_param("iisssi", $service_id, $user_id, $title, $description, $priority, $assigned_to);
        }

        if ($stmt->execute()) {
            $form_success = true;
            
            // Reset form values
            $service_id = $title = $description = '';
            $priority = 'Sedang';
            $assigned_to = null;
        } else {
            $form_errors['database'] = 'Terjadi kesalahan saat menyimpan data: ' . $conn->error;
        }
        
        $stmt->close();
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
    <title>Ajukan Permintaan - Puskom UNIVED</title>
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
        
        /* Page Header */
        .page-header {
            background: var(--university-gradient), url('https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 30px 30px;
        }
        
        /* Form Styles */
        .request-form {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-bottom: 3rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--university-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--university-color);
            box-shadow: 0 0 0 0.25rem rgba(30, 67, 89, 0.25);
        }
        
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-section-title {
            color: var(--university-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .form-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 24px;
            background: var(--university-gradient);
            border-radius: 4px;
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--university-gradient);
            border: none;
            border-radius: 8px;
            padding: 12px 28px;
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
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--university-gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(30, 67, 89, 0.2);
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
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 3rem 0;
                text-align: center;
            }
            
            .request-form {
                padding: 1.5rem;
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
                        <a class="nav-link" href="tickets.php">Tiket Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="request.php">Ajukan Permintaan</a>
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

    <!-- Page Header -->
    <div class="page-header mt-5 pt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Ajukan Permintaan Layanan</h1>
                    <p class="lead mb-0">Isi formulir di bawah ini untuk mengajukan permintaan layanan IT</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="user_tickets.php" class="btn btn-light btn-lg"><i class="fas fa-list me-2"></i>Lihat Tiket Saya</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Alert Success -->
                <?php if ($form_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Berhasil!</strong> Permintaan layanan Anda telah berhasil dikirim. Tim IT akan segera menindaklanjuti.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Alert Error -->
                <?php if (isset($form_errors['database'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error!</strong> <?php echo $form_errors['database']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Request Form -->
                <form class="request-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <!-- User Information Section -->
                    <div class="form-section">
                        <h3 class="form-section-title">Informasi Pemohon</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($fullname); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Departemen</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($department); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Information Section -->
                    <div class="form-section">
                        <h3 class="form-section-title">Detail Layanan</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">Jenis Layanan <span class="text-danger">*</span></label>
                                <select class="form-select <?php echo isset($form_errors['service_id']) ? 'is-invalid' : ''; ?>" id="service_id" name="service_id" required>
                                    <option value="">Pilih Jenis Layanan</option>
                                    <?php
                                    if ($services_result && $services_result->num_rows > 0) {
                                        $current_category = '';
                                        while($service = $services_result->fetch_assoc()) {
                                            if ($service['category'] != $current_category) {
                                                if ($current_category != '') {
                                                    echo '</optgroup>';
                                                }
                                                $current_category = $service['category'];
                                                echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                                            }
                                            
                                            $selected = (isset($service_id) && $service_id == $service['id']) ? 'selected' : '';
                                            echo '<option value="' . $service['id'] . '" ' . $selected . '>' . htmlspecialchars($service['name']) . '</option>';
                                        }
                                        echo '</optgroup>';
                                    } else {
                                        echo '<option value="">Tidak ada layanan tersedia</option>';
                                    }
                                    ?>
                                </select>
                                <?php if (isset($form_errors['service_id'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $form_errors['service_id']; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="Rendah" <?php echo (isset($priority) && $priority == 'Rendah') ? 'selected' : ''; ?>>Rendah</option>
                                    <option value="Sedang" <?php echo (isset($priority) && $priority == 'Sedang') ? 'selected' : ''; echo !isset($priority) ? 'selected' : ''; ?>>Sedang</option>
                                    <option value="Tinggi" <?php echo (isset($priority) && $priority == 'Tinggi') ? 'selected' : ''; ?>>Tinggi</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="title" class="form-label">Judul Permintaan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($form_errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" placeholder="Contoh: Permintaan Perbaikan Jaringan WiFi" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                                <?php if (isset($form_errors['title'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $form_errors['title']; ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">Buat judul yang jelas dan deskriptif untuk permintaan Anda.</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Deskripsi Masalah <span class="text-danger">*</span></label>
                                <textarea class="form-control <?php echo isset($form_errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="5" placeholder="Jelaskan secara detail masalah atau permintaan Anda..." required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                <?php if (isset($form_errors['description'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $form_errors['description']; ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">Sertakan informasi seperti lokasi, perangkat yang terlibat, dan langkah-langkah yang sudah dicoba.</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <h3 class="form-section-title">Informasi Tambahan</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Ditugaskan Kepada (Opsional)</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Pilih Staf IT (Opsional)</option>
                                    <?php
                                    if ($staff_result && $staff_result->num_rows > 0) {
                                        while($staff = $staff_result->fetch_assoc()) {
                                            $selected = (isset($assigned_to) && $assigned_to == $staff['id']) ? 'selected' : '';
                                            echo '<option value="' . $staff['id'] . '" ' . $selected . '>' . htmlspecialchars($staff['fullname']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="form-text">Biarkan kosong untuk ditugaskan secara otomatis oleh sistem.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lampiran File (Opsional)</label>
                                <input type="file" class="form-control" id="attachment" name="attachment">
                                <div class="form-text">Unggah screenshot atau dokumen pendukung (maks. 5MB).</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Permintaan
                        </button>
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
    <script>
        // Script untuk validasi form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.request-form');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Validasi service
                const serviceSelect = document.getElementById('service_id');
                if (!serviceSelect.value) {
                    serviceSelect.classList.add('is-invalid');
                    valid = false;
                } else {
                    serviceSelect.classList.remove('is-invalid');
                }
                
                // Validasi judul
                const titleInput = document.getElementById('title');
                if (!titleInput.value || titleInput.value.length < 5) {
                    titleInput.classList.add('is-invalid');
                    valid = false;
                } else {
                    titleInput.classList.remove('is-invalid');
                }
                
                // Validasi deskripsi
                const descTextarea = document.getElementById('description');
                if (!descTextarea.value || descTextarea.value.length < 10) {
                    descTextarea.classList.add('is-invalid');
                    valid = false;
                } else {
                    descTextarea.classList.remove('is-invalid');
                }
                
                if (!valid) {
                    e.preventDefault();
                    
                    // Scroll ke field pertama yang error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Reset validation on input
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
    </script>
</body>
</html>