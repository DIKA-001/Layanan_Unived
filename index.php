<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan IT PUSKOM - Login & Pendaftaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #fca311;
            --danger: #e63946;
            --gray: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            min-height: 650px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        
        .left-panel h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .left-panel p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .features {
            margin-top: 30px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .feature i {
            margin-right: 10px;
            color: var(--accent);
            font-size: 1.2rem;
        }
        
        .right-panel {
            flex: 1.5;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--light);
            overflow-y: auto;
        }
        
        .form-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h2 {
            color: var(--primary);
            font-size: 2rem;
            margin-top: 10px;
            font-weight: 700;
        }
        
        .logo-icon {
            background: var(--primary);
            color: white;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            transition: all 0.3s;
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 42px;
            color: var(--gray);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn:hover {
            background: var(--secondary);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.5);
        }
        
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .register-form {
            display: none;
        }
        
        .form-toggle {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
        }
        
        .form-toggle a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .form-toggle a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .notification {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        
        .success {
            background: rgba(76, 201, 240, 0.2);
            color: #4cc9f0;
            border: 1px solid #4cc9f0;
        }
        
        .error {
            background: rgba(230, 57, 70, 0.2);
            color: #e63946;
            border: 1px solid #e63946;
        }
        
        .two-columns {
            display: flex;
            gap: 15px;
        }
        
        .two-columns .form-group {
            flex: 1;
        }
        
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            
            .left-panel {
                display: none;
            }
            
            .right-panel {
                padding: 30px 20px;
            }
            
            .two-columns {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1>Layanan IT PUSKOM</h1>
            <p>Selamat datang di portal layanan IT PUSKOM. Akses semua layanan teknologi informasi dan komunikasi untuk mendukung aktivitas akademik dan administratif Anda.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Keamanan terjamin dengan enkripsi tingkat tinggi</span>
                </div>
                <div class="feature">
                    <i class="fas fa-bolt"></i>
                    <span>Akses cepat dan stabil ke semua layanan</span>
                </div>
                <div class="feature">
                    <i class="fas fa-headset"></i>
                    <span>Dukungan teknis 24/7 untuk semua kebutuhan IT</span>
                </div>
                <div class="feature">
                    <i class="fas fa-cloud"></i>
                    <span>Layanan cloud dengan kapasitas penyimpanan besar</span>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="form-container">
                <div class="logo">
                    <div class="logo-icon">IT</div>
                    <h2>PUSKOM LOGIN</h2>
                </div>
                
                <div class="tabs">
                    <div class="tab active" id="loginTab">Login</div>
                    <div class="tab" id="registerTab">Daftar Pengguna</div>
                </div>
                
                <div class="notification success" id="successNotification">
                    <i class="fas fa-check-circle"></i> <span id="successMessage"></span>
                </div>
                
                <div class="notification error" id="errorNotification">
                    <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
                </div>
                
                <form id="loginForm" class="login-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                    
                    <div class="links">
                        <a href="#"><i class="fas fa-key"></i> Lupa Password?</a>
                        <a href="#" id="showRegister"><i class="fas fa-user-plus"></i> Daftar Akun Baru</a>
                    </div>
                </form>
                
                <form id="registerForm" class="register-form" action="register.php" method="POST">
                    <div class="two-columns">
                        <div class="form-group">
                            <label for="fullname">Nama Lengkap</label>
                            <i class="fas fa-user"></i>
                            <input type="text" id="fullname" name="fullname" placeholder="Masukkan nama lengkap" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
                        </div>
                    </div>
                    
                    <div class="two-columns">
                        <div class="form-group">
                            <label for="newUsername">Username</label>
                            <i class="fas fa-user-tag"></i>
                            <input type="text" id="newUsername" name="username" placeholder="Buat username unik" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">No. HP</label>
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="Contoh: 08123456789" required>
                        </div>
                    </div>
                    
                    <div class="two-columns">
                        <div class="form-group">
                            <label for="gender">Jenis Kelamin</label>
                            <i class="fas fa-venus-mars"></i>
                            <select id="gender" name="gender" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Departemen</label>
                            <i class="fas fa-building"></i>
                            <select id="department" name="department" required>
                                <option value="">Pilih Departemen</option>
                                <option value="IT">IT</option>
                                <option value="HRD">HRD</option>
                                <option value="Keuangan">Keuangan</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="two-columns">
                        <div class="form-group">
                            <label for="newPassword">Password</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="newPassword" name="password" placeholder="Buat password yang kuat" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Konfirmasi Password</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Ulangi password Anda" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Daftar</button>
                    
                    <div class="form-toggle">
                        Sudah punya akun? <a href="#" id="showLogin">Login di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const showRegister = document.getElementById('showRegister');
            const showLogin = document.getElementById('showLogin');
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const successNotification = document.getElementById('successNotification');
            const errorNotification = document.getElementById('errorNotification');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            // Fungsi untuk menampilkan notifikasi
            function showNotification(type, message) {
                if (type === 'success') {
                    successMessage.textContent = message;
                    successNotification.style.display = 'block';
                    errorNotification.style.display = 'none';
                } else {
                    errorMessage.textContent = message;
                    errorNotification.style.display = 'block';
                    successNotification.style.display = 'none';
                }
                
                // Sembunyikan notifikasi setelah 5 detik
                setTimeout(() => {
                    successNotification.style.display = 'none';
                    errorNotification.style.display = 'none';
                }, 5000);
            }
            
            // Fungsi untuk beralih antara form login dan register
            function showRegisterForm() {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                loginTab.classList.remove('active');
                registerTab.classList.add('active');
                successNotification.style.display = 'none';
                errorNotification.style.display = 'none';
            }
            
            function showLoginForm() {
                registerForm.style.display = 'none';
                loginForm.style.display = 'block';
                registerTab.classList.remove('active');
                loginTab.classList.add('active');
                successNotification.style.display = 'none';
                errorNotification.style.display = 'none';
            }
            
            // Event listeners untuk tab dan link
            showRegister.addEventListener('click', function(e) {
                e.preventDefault();
                showRegisterForm();
            });
            
            showLogin.addEventListener('click', function(e) {
                e.preventDefault();
                showLoginForm();
            });
            
            loginTab.addEventListener('click', function() {
                showLoginForm();
            });
            
            registerTab.addEventListener('click', function() {
                showRegisterForm();
            });
            
            // Validasi form register sebelum submit
            registerForm.addEventListener('submit', function(e) {
                const password = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    showNotification('error', 'Konfirmasi password tidak sesuai!');
                }
            });
        });
    </script>
</body>
</html>