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
                    <a href="#" class="nav-link active" data-target="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dasbor
                    </a>
                    <a href="#" class="nav-link" data-target="layanan">
                        <i class="fas fa-concierge-bell"></i> Layanan
                    </a>
                    <a href="#" class="nav-link" data-target="tambah-layanan">
                        <i class="fas fa-plus-circle"></i> Tambah Layanan
                    </a>
                    <a href="#" class="nav-link" data-target="users">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                    <a href="#" class="nav-link" data-target="tambah-user">
                        <i class="fas fa-user-plus"></i> Tambah User
                    </a>
                    <a href="#" class="nav-link" data-target="laporan">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    <a href="#" class="nav-link" data-target="penggunaan-layanan">
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

                <!-- Dashboard Content -->
                <div id="dashboard" class="content-section active">
                    <h2 class="mb-4">Dasbor Layanan IT</h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card card-dashboard bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Layanan</h5>
                                            <h2 class="card-text">15</h2>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-concierge-bell"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-dashboard bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Pengguna</h5>
                                            <h2 class="card-text">243</h2>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-dashboard bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Tiket Aktif</h5>
                                            <h2 class="card-text">8</h2>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-dashboard bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Permintaan</h5>
                                            <h2 class="card-text">12</h2>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Aktivitas Layanan Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Layanan</th>
                                                    <th>Pengguna</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Permintaan Email Institusi</td>
                                                    <td>Johan Setiawan</td>
                                                    <td>12 Okt 2023</td>
                                                    <td><span class="badge bg-success">Selesai</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Permintaan Akses WiFi</td>
                                                    <td>Dewi Anggraini</td>
                                                    <td>11 Okt 2023</td>
                                                    <td><span class="badge bg-warning text-dark">Proses</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Instalasi Software</td>
                                                    <td>Budi Santoso</td>
                                                    <td>10 Okt 2023</td>
                                                    <td><span class="badge bg-primary">Ditugaskan</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Permintaan Data</td>
                                                    <td>Rina Wijaya</td>
                                                    <td>9 Okt 2023</td>
                                                    <td><span class="badge bg-success">Selesai</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Perbaikan Hardware</td>
                                                    <td>Ahmad Fauzi</td>
                                                    <td>8 Okt 2023</td>
                                                    <td><span class="badge bg-danger">Tertunda</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Statistik Layanan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <p class="mb-1">Jaringan & Internet</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar" role="progressbar" style="width: 65%">65%</div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-1">Software</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 40%">40%</div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-1">Hardware</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 30%">30%</div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-1">Lainnya</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 25%">25%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Layanan Content -->
                <div id="layanan" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Daftar Layanan</h2>
                        <button class="btn btn-primary" data-target="tambah-layanan">
                            <i class="fas fa-plus me-1"></i> Tambah Layanan
                        </button>
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
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Permintaan Email Institusi</td>
                                            <td>Akun</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>12 Jan 2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Akses Jaringan WiFi</td>
                                            <td>Jaringan</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>15 Feb 2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Instalasi Software</td>
                                            <td>Software</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>20 Mar 2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Perbaikan Hardware</td>
                                            <td>Hardware</td>
                                            <td><span class="badge bg-warning text-dark">Maintenance</span></td>
                                            <td>5 Apr 2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Permintaan Data</td>
                                            <td>Data</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>18 Mei 2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tambah Layanan Content -->
                <div id="tambah-layanan" class="content-section">
                    <h2 class="mb-4">Tambah Layanan Baru</h2>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="namaLayanan" class="form-label">Nama Layanan</label>
                                            <input type="text" class="form-control" id="namaLayanan" placeholder="Masukkan nama layanan">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="kategoriLayanan" class="form-label">Kategori</label>
                                            <select class="form-select" id="kategoriLayanan">
                                                <option selected disabled>Pilih kategori</option>
                                                <option value="jaringan">Jaringan & Internet</option>
                                                <option value="software">Software</option>
                                                <option value="hardware">Hardware</option>
                                                <option value="akun">Akun</option>
                                                <option value="data">Data</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="deskripsiLayanan" class="form-label">Deskripsi Layanan</label>
                                            <textarea class="form-control" id="deskripsiLayanan" rows="3" placeholder="Jelaskan detail layanan yang ditawarkan"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="penanggungJawab" class="form-label">Penanggung Jawab</label>
                                            <select class="form-select" id="penanggungJawab">
                                                <option selected disabled>Pilih penanggung jawab</option>
                                                <option value="1">Ahmad Rizky</option>
                                                <option value="2">Dewi Lestari</option>
                                                <option value="3">Budi Santoso</option>
                                                <option value="4">Sari Indah</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="statusLayanan" class="form-label">Status</label>
                                            <select class="form-select" id="statusLayanan">
                                                <option value="aktif">Aktif</option>
                                                <option value="non-aktif">Non-Aktif</option>
                                                <option value="maintenance">Maintenance</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="estimasiWaktu" class="form-label">Estimasi Waktu Penyelesaian</label>
                                            <input type="text" class="form-control" id="estimasiWaktu" placeholder="Contoh: 3-5 hari kerja">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="biayaLayanan" class="form-label">Biaya (jika ada)</label>
                                            <input type="text" class="form-control" id="biayaLayanan" placeholder="Masukkan biaya layanan">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="persetujuanKhusus">
                                    <label class="form-check-label" for="persetujuanKhusus">
                                        Memerlukan persetujuan khusus
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan Layanan</button>
                                <button type="reset" class="btn btn-outline-secondary">Batal</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Users Content -->
                <div id="users" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manajemen Pengguna</h2>
                        <button class="btn btn-primary" data-target="tambah-user">
                            <i class="fas fa-user-plus me-1"></i> Tambah User
                        </button>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Departemen</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Johan Setiawan</td>
                                            <td>johan@unived.ac.id</td>
                                            <td>Dosen</td>
                                            <td>Teknik Informatika</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Dewi Anggraini</td>
                                            <td>dewi@unived.ac.id</td>
                                            <td>Staff</td>
                                            <td>Administrasi</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Budi Santoso</td>
                                            <td>budi@unived.ac.id</td>
                                            <td>Mahasiswa</td>
                                            <td>Teknik Elektro</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Rina Wijaya</td>
                                            <td>rina@unived.ac.id</td>
                                            <td>Dosen</td>
                                            <td>Ekonomi</td>
                                            <td><span class="badge bg-warning text-dark">Non-aktif</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Ahmad Fauzi</td>
                                            <td>ahmad@unived.ac.id</td>
                                            <td>Staff</td>
                                            <td>Perpustakaan</td>
                                            <td><span class="badge bg-success">Aktif</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tambah User Content -->
                <div id="tambah-user" class="content-section">
                    <h2 class="mb-4">Tambah Pengguna Baru</h2>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="namaUser" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="namaUser" placeholder="Masukkan nama lengkap">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="emailUser" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="emailUser" placeholder="Masukkan alamat email">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="roleUser" class="form-label">Role</label>
                                            <select class="form-select" id="roleUser">
                                                <option selected disabled>Pilih role</option>
                                                <option value="dosen">Dosen</option>
                                                <option value="staff">Staff</option>
                                                <option value="mahasiswa">Mahasiswa</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="departemenUser" class="form-label">Departemen/Fakultas</label>
                                            <select class="form-select" id="departemenUser">
                                                <option selected disabled>Pilih departemen</option>
                                                <option value="tik">Teknik Informatika</option>
                                                <option value="te">Teknik Elektro</option>
                                                <option value="ekonomi">Ekonomi</option>
                                                <option value="administrasi">Administrasi</option>
                                                <option value="perpustakaan">Perpustakaan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="passwordUser" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="passwordUser" placeholder="Masukkan password">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="konfirmasiPassword" class="form-label">Konfirmasi Password</label>
                                            <input type="password" class="form-control" id="konfirmasiPassword" placeholder="Konfirmasi password">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="statusUser" class="form-label">Status</label>
                                            <select class="form-select" id="statusUser">
                                                <option value="aktif">Aktif</option>
                                                <option value="non-aktif">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="teleponUser" class="form-label">Nomor Telepon</label>
                                            <input type="tel" class="form-control" id="teleponUser" placeholder="Masukkan nomor telepon">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="notifikasiEmail">
                                    <label class="form-check-label" for="notifikasiEmail">
                                        Kirim notifikasi via email
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                                <button type="reset" class="btn btn-outline-secondary">Batal</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Laporan Content -->
                <div id="laporan" class="content-section">
                    <h2 class="mb-4">Laporan Layanan IT</h2>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="periodeAwal" class="form-label">Periode Awal</label>
                                        <input type="date" class="form-control" id="periodeAwal">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="periodeAkhir" class="form-label">Periode Akhir</label>
                                        <input type="date" class="form-control" id="periodeAkhir">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="kategoriLaporan" class="form-label">Kategori Layanan</label>
                                        <select class="form-select" id="kategoriLaporan">
                                            <option value="">Semua Kategori</option>
                                            <option value="jaringan">Jaringan & Internet</option>
                                            <option value="software">Software</option>
                                            <option value="hardware">Hardware</option>
                                            <option value="akun">Akun</option>
                                            <option value="data">Data</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="statusLaporan" class="form-label">Status</label>
                                        <select class="form-select" id="statusLaporan">
                                            <option value="">Semua Status</option>
                                            <option value="selesai">Selesai</option>
                                            <option value="proses">Proses</option>
                                            <option value="ditugaskan">Ditugaskan</option>
                                            <option value="tertunda">Tertunda</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary">Generate Laporan</button>
                            <button class="btn btn-outline-success">
                                <i class="fas fa-download me-1"></i> Export Excel
                            </button>
                            <button class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Statistik Layanan</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="layananChart" height="250"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <canvas id="statusChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penggunaan Layanan Content -->
                <div id="penggunaan-layanan" class="content-section">
                    <h2 class="mb-4">Laporan Penggunaan Layanan</h2>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tahunLaporan" class="form-label">Tahun</label>
                                        <select class="form-select" id="tahunLaporan">
                                            <option value="2023" selected>2023</option>
                                            <option value="2022">2022</option>
                                            <option value="2021">2021</option>
                                            <option value="2020">2020</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bulanLaporan" class="form-label">Bulan</label>
                                        <select class="form-select" id="bulanLaporan">
                                            <option value="">Semua Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="jenisLayanan" class="form-label">Jenis Layanan</label>
                                        <select class="form-select" id="jenisLayanan">
                                            <option value="">Semua Layanan</option>
                                            <option value="email">Email Institusi</option>
                                            <option value="wifi">Akses WiFi</option>
                                            <option value="software">Instalasi Software</option>
                                            <option value="hardware">Perbaikan Hardware</option>
                                            <option value="data">Permintaan Data</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="unitLaporan" class="form-label">Unit/Fakultas</label>
                                        <select class="form-select" id="unitLaporan">
                                            <option value="">Semua Unit</option>
                                            <option value="tik">Teknik Informatika</option>
                                            <option value="te">Teknik Elektro</option>
                                            <option value="ekonomi">Ekonomi</option>
                                            <option value="administrasi">Administrasi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary">Tampilkan Data</button>
                            <button class="btn btn-outline-success">
                                <i class="fas fa-download me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Data Penggunaan Layanan</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Jenis Layanan</th>
                                            <th>Jumlah Pengguna</th>
                                            <th>Rata-rata Waktu Penyelesaian</th>
                                            <th>Tingkat Kepuasan</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Januari 2023</td>
                                            <td>Email Institusi</td>
                                            <td>45</td>
                                            <td>2 hari</td>
                                            <td>4.5/5</td>
                                            <td><i class="fas fa-arrow-up text-success"></i></td>
                                        </tr>
                                        <tr>
                                            <td>Februari 2023</td>
                                            <td>Akses WiFi</td>
                                            <td>78</td>
                                            <td>1 hari</td>
                                            <td>4.2/5</td>
                                            <td><i class="fas fa-arrow-up text-success"></i></td>
                                        </tr>
                                        <tr>
                                            <td>Maret 2023</td>
                                            <td>Instalasi Software</td>
                                            <td>32</td>
                                            <td>3 hari</td>
                                            <td>4.7/5</td>
                                            <td><i class="fas fa-arrow-down text-danger"></i></td>
                                        </tr>
                                        <tr>
                                            <td>April 2023</td>
                                            <td>Perbaikan Hardware</td>
                                            <td>15</td>
                                            <td>5 hari</td>
                                            <td>4.0/5</td>
                                            <td><i class="fas fa-arrow-up text-success"></i></td>
                                        </tr>
                                        <tr>
                                            <td>Mei 2023</td>
                                            <td>Permintaan Data</td>
                                            <td>28</td>
                                            <td>2 hari</td>
                                            <td>4.8/5</td>
                                            <td><i class="fas fa-arrow-up text-success"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle navigation clicks
            const navLinks = document.querySelectorAll('.nav-link[data-target]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Hide all content sections
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show the target section
                    const targetId = this.getAttribute('data-target');
                    document.getElementById(targetId).classList.add('active');
                });
            });
            
            // Handle button navigation
            const buttons = document.querySelectorAll('button[data-target]');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    
                    // Hide all content sections
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show the target section
                    document.getElementById(targetId).classList.add('active');
                    
                    // Update active nav link
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('data-target') === targetId) {
                            link.classList.add('active');
                        }
                    });
                });
            });
            
            // Initialize charts
            const layananCtx = document.getElementById('layananChart').getContext('2d');
            const layananChart = new Chart(layananCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [{
                        label: 'Jumlah Layanan',
                        data: [65, 59, 80, 81, 56, 55],
                        backgroundColor: 'rgba(30, 67, 89, 0.7)',
                        borderColor: 'rgba(30, 67, 89, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Penggunaan Layanan per Bulan'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Selesai', 'Proses', 'Ditugaskan', 'Tertunda'],
                    datasets: [{
                        data: [45, 20, 15, 10],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(220, 53, 69, 0.7)'
                        ],
                        borderColor: [
                            'rgba(25, 135, 84, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(13, 110, 253, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Status Layanan'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>