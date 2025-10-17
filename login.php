<?php
// login.php - File untuk memproses login
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Lindungi dari SQL injection
    $username = $koneksi->real_escape_string($username);
    
    // Query untuk mencari user - sekarang role ada di tabel users
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $koneksi->query($query);
    
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['role'] = $user['role']; // Role sekarang ada di tabel users
            
            // Jika request berasal dari AJAX, kembalikan JSON
            $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                       strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($is_ajax) {
                echo json_encode(array(
                    'success' => true,
                    'role' => $_SESSION['role'],
                    'message' => 'Login berhasil'
                ));
                exit();
            } else {
                // Redirect ke halaman dashboard berdasarkan role
                if ($_SESSION['role'] == 'Admin') {
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    header("Location: user/dashboard.php");
                    exit();
                }
            }
        } else {
            // Password salah
            $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                       strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($is_ajax) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Password salah'
                ));
                exit();
            } else {
                header("Location: index.php?error=password");
                exit();
            }
        }
    } else {
        // User tidak ditemukan
        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if ($is_ajax) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Username tidak ditemukan'
            ));
            exit();
        } else {
            header("Location: index.php?error=user");
            exit();
        }
    }
    
    $koneksi->close();
} else {
    // Jika bukan metode POST, redirect ke halaman login
    header("Location: index.php");
    exit();
}
?>