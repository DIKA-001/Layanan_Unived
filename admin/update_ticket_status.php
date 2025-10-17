<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tiket_id = $_POST['tiket_id'] ?? null;
    $status = $_POST['status'] ?? '';
    
    if ($tiket_id && in_array($status, ['Ditugaskan', 'Proses', 'Selesai', 'Tertunda'])) {
        try {
            // Update status tiket
            $stmt = $pdo->prepare("UPDATE service_tickets SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $tiket_id]);
            
            // Jika status diubah menjadi Selesai, set completed_at
            if ($status === 'Selesai') {
                $stmt = $pdo->prepare("UPDATE service_tickets SET completed_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $tiket_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>