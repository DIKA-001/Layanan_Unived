<?php
// config.php - File konfigurasi koneksi database
$host = "localhost";
$user = "root"; // Default user XAMPP
$pass = ""; // Default password XAMPP (kosong)
$db   = "it_puskom";

// Membuat koneksi
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>