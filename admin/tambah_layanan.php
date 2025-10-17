<?php
// Include file konfigurasi database
require_once 'koneksi.php';

// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
   $namaLayanan = isset($_POST['namaLayanan']) ? $_POST['namaLayanan'] : '';
   $namaLayanan = isset($_POST['namaLayanan']) ? $_POST['namaLayanan'] : '';
   $kategoriLayanan = isset($_POST['kategoriLayanan']) ? $_POST['kategoriLayanan'] : '';
   $deskripsiLayanan = isset($_POST['deskripsiLayanan']) ? $_POST['deskripsiLayanan'] : '';
   $penanggungJawab = isset($_POST['penanggungJawab']) ? $_POST['penanggungJawab'] : '';
   $statusLayanan = isset($_POST['statusLayanan']) ? $_POST['statusLayanan'] : 'Aktif';
   $estimasiWaktu = isset($_POST['estimasiWaktu']) ? $_POST['estimasiWaktu'] : '';
   $biayaLayanan = isset($_POST['biayaLayanan']) ? $_POST['biayaLayanan'] : 0;
   $persetujuanKhusus = isset($_POST['persetujuanKhusus']) ? 1 : 0;

    // Validasi data
    $errors = [];
    
    if (empty($namaLayanan)) {
        $errors[] = "Nama layanan harus diisi";
    }
    
    if (empty($kategoriLayanan)) {
        $errors[] = "Kategori layanan harus dipilih";
    }
    
    if (empty($deskripsiLayanan)) {
        $errors[] = "Deskripsi layanan harus diisi";
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            // Query untuk menyimpan data layanan
            $sql = "INSERT INTO services (name, category, description, responsible_person, status, completion_estimate, cost, requires_approval) 
                    VALUES (:name, :category, :description, :responsible_person, :status, :completion_estimate, :cost, :requires_approval)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $namaLayanan,
                ':category' => $kategoriLayanan,
                ':description' => $deskripsiLayanan,
                ':responsible_person' => $penanggungJawab,
                ':status' => $statusLayanan,
                ':completion_estimate' => $estimasiWaktu,
                ':cost' => $biayaLayanan,
                ':requires_approval' => $persetujuanKhusus
            ]);
            
            // Redirect ke halaman daftar layanan dengan pesan sukses
            header("Location: index.php?section=layanan&message=success");
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
    
    // Jika ada error, simpan di session untuk ditampilkan
    session_start();
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: index.php?section=tambah-layanan");
    exit();
} else {
    // Jika bukan metode POST, redirect ke halaman tambah layanan
    header("Location: index.php?section=tambah-layanan");
    exit();
}
?>