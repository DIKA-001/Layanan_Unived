<?php
// register.php - File untuk memproses pendaftaran
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];
    
    // Lindungi dari SQL injection
    $fullname = $koneksi->real_escape_string($fullname);
    $email = $koneksi->real_escape_string($email);
    $username = $koneksi->real_escape_string($username);
    $phone = $koneksi->real_escape_string($phone);
    $gender = $koneksi->real_escape_string($gender);
    $department = $koneksi->real_escape_string($department);
    
    // Cek apakah username atau email sudah ada
    $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = $koneksi->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Username atau email sudah terdaftar
        header("Location: index.php?error=duplicate");
    } else {
        // Simpan data ke database
        $query = "INSERT INTO users (fullname, email, username, password, phone, gender, department) 
                  VALUES ('$fullname', '$email', '$username', '$password', '$phone', '$gender', '$department')";
        
$result = $koneksi->query($query);
if ($result) {
    // Query berhasil dijalankan
    echo "Registrasi berhasil!";
    header("Location: index.php");
    exit(); // Pastikan untuk menambahkan exit() setelah header redirect

} else {
    // Query gagal
    echo "Error: " . $koneksi->error;
}
    }
    
    $koneksi->close();
}
?>