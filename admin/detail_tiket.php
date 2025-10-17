<?php
require_once 'koneksi.php';

// Ambil ID tiket dari parameter URL
$tiket_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$tiket_id) {
    header('Location: penggunaan-layanan.php');
    exit;
}

// Query untuk mendapatkan detail tiket
try {
    $stmt = $pdo->prepare("
        SELECT 
            st.*,
            s.name as service_name,
            s.category,
            u.fullname as user_name,
            u.email as user_email,
            u.phone as user_phone,
            u.department as user_department,
            a.fullname as assigned_name
        FROM service_tickets st
        LEFT JOIN services s ON st.service_id = s.id
        LEFT JOIN users u ON st.user_id = u.id
        LEFT JOIN users a ON st.assigned_to = a.id
        WHERE st.id = :id
    ");
    $stmt->bindParam(':id', $tiket_id, PDO::PARAM_INT);
    $stmt->execute();
    $tiket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tiket) {
        header('Location: penggunaan-layanan.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Proses update status jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $update_stmt = $pdo->prepare("
            UPDATE service_tickets 
            SET status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':id', $tiket_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            $success_message = "Status berhasil diperbarui";
            // Refresh data
            $stmt->execute();
            $tiket = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Gagal memperbarui status";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket - Puskom Universitas Dehasen Bengkulu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Detail Tiket Layanan</h4>
                        <a href="penggunaan-layanan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Informasi Tiket</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">ID Tiket</th>
                                        <td><?php echo $tiket['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Judul</th>
                                        <td><?php echo htmlspecialchars($tiket['title']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td><?php echo nl2br(htmlspecialchars($tiket['description'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Layanan</th>
                                        <td><?php echo $tiket['service_name'] . ' (' . $tiket['category'] . ')'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Prioritas</th>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                switch($tiket['priority']) {
                                                    case 'Tinggi': echo 'bg-danger'; break;
                                                    case 'Sedang': echo 'bg-warning'; break;
                                                    case 'Rendah': echo 'bg-info'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                                ?>
                                            ">
                                                <?php echo $tiket['priority']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Status & Penugasan</h5>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="Ditugaskan" <?php echo $tiket['status'] == 'Ditugaskan' ? 'selected' : ''; ?>>Ditugaskan</option>
                                            <option value="Proses" <?php echo $tiket['status'] == 'Proses' ? 'selected' : ''; ?>>Proses</option>
                                            <option value="Selesai" <?php echo $tiket['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="Tertunda" <?php echo $tiket['status'] == 'Tertunda' ? 'selected' : ''; ?>>Tertunda</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ditugaskan Kepada</label>
                                        <input type="text" class="form-control" value="<?php echo $tiket['assigned_name'] ? htmlspecialchars($tiket['assigned_name']) : 'Belum ditugaskan'; ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dibuat Pada</label>
                                        <input type="text" class="form-control" value="<?php echo date('d M Y H:i', strtotime($tiket['created_at'])); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Diperbarui Pada</label>
                                        <input type="text" class="form-control" value="<?php echo date('d M Y H:i', strtotime($tiket['updated_at'])); ?>" readonly>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary">Perbarui Status</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h5>Informasi Pelapor</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="20%">Nama</th>
                                        <td><?php echo htmlspecialchars($tiket['user_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($tiket['user_email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td><?php echo htmlspecialchars($tiket['user_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Departemen</th>
                                        <td><?php echo htmlspecialchars($tiket['user_department']); ?></td>
                                    </tr>
                                </table>
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