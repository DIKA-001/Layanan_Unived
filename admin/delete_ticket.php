<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tiket_id'])) {
    try {
        $tiket_id = $_POST['tiket_id'];
        
        // Hapus tiket dari database
        $stmt = $pdo->prepare("DELETE FROM service_tickets WHERE id = :id");
        $stmt->bindParam(':id', $tiket_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tiket berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus tiket']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
}
?>