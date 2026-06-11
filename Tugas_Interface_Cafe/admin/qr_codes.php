<?php
session_start();
require_once '../config.php';
require_once '../phpqrcode/qrlib.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../Login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $table_number = $_POST['table_number'];
            $table_name = $_POST['table_name'];

            // Generate QR code
            $qr_dir = '../qrcodes/';
            if (!file_exists($qr_dir)) {
                mkdir($qr_dir, 0777, true);
            }

            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/menu.php?table=' . urlencode($table_number);
            $qr_filename = 'qr_table_' . $table_number . '.png';
            $qr_path = $qr_dir . $qr_filename;

            QRcode::png($base_url, $qr_path, 'L', 10, 2);

            $stmt = $conn->prepare("INSERT INTO table_qr_codes (table_number, table_name, qr_code_path) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE table_name = ?, qr_code_path = ?");
            $stmt->bind_param("sssss", $table_number, $table_name, $qr_path, $table_name, $qr_path);
            $stmt->execute();
            $stmt->close();

            header('Location: qr_codes.php?success=added');
            exit;
        } elseif ($_POST['action'] == 'toggle') {
            $id = $_POST['id'];
            $conn->query("UPDATE table_qr_codes SET is_active = NOT is_active WHERE id = $id");
            header('Location: qr_codes.php?success=toggled');
            exit;
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];

            // Get QR code path to delete file
            $result = $conn->query("SELECT qr_code_path FROM table_qr_codes WHERE id = $id");
            if ($row = $result->fetch_assoc()) {
                if (file_exists($row['qr_code_path'])) {
                    unlink($row['qr_code_path']);
                }
            }

            $conn->query("DELETE FROM table_qr_codes WHERE id = $id");
            header('Location: qr_codes.php?success=deleted');
            exit;
        }
    }
}

// Get all QR codes
$qr_codes = $conn->query("SELECT * FROM table_qr_codes ORDER BY table_number");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola QR Code - Kafe Ndalem</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .qr-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .qr-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .qr-card.inactive {
            opacity: 0.5;
        }

        .qr-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 1rem;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-image img {
            max-width: 100%;
            max-height: 100%;
        }

        .qr-info {
            margin-bottom: 1rem;
        }

        .table-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .table-name {
            color: #666;
            font-size: 0.9rem;
        }

        .qr-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .qr-actions button,
        .qr-actions a {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .btn-view:hover {
            background: #2980b9;
        }

        .btn-toggle {
            background: #f39c12;
            color: white;
        }

        .btn-toggle:hover {
            background: #d68910;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .add-qr-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .qr-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1><i class="fas fa-qrcode"></i> Kelola QR Code Menu</h1>
                <p style="color: #666;">Generate dan kelola QR code untuk setiap meja</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] == 'added') echo 'QR Code berhasil dibuat!';
                elseif ($_GET['success'] == 'toggled') echo 'Status QR Code berhasil diubah!';
                elseif ($_GET['success'] == 'deleted') echo 'QR Code berhasil dihapus!';
                ?>
            </div>
        <?php endif; ?>

        <!-- Add QR Code Form -->
        <div class="add-qr-form">
            <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-plus-circle"></i> Tambah QR Code Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Meja *</label>
                        <input type="text" name="table_number" class="form-control" required placeholder="Contoh: 1, 2, VIP-1">
                    </div>
                    <div class="form-group">
                        <label>Nama Meja *</label>
                        <input type="text" name="table_name" class="form-control" required placeholder="Contoh: Meja 1, Meja VIP">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-qrcode"></i> Generate QR
                    </button>
                </div>
            </form>
        </div>

        <!-- QR Code Grid -->
        <?php if ($qr_codes->num_rows > 0): ?>
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-list"></i> Daftar QR Code (<?= $qr_codes->num_rows ?>)</h3>
            <div class="qr-grid">
                <?php while ($qr = $qr_codes->fetch_assoc()): ?>
                    <div class="qr-card <?= $qr['is_active'] ? '' : 'inactive' ?>">
                        <div class="qr-image">
                            <?php if ($qr['qr_code_path'] && file_exists($qr['qr_code_path'])): ?>
                                <img src="<?= htmlspecialchars($qr['qr_code_path']) ?>" alt="QR Code <?= htmlspecialchars($qr['table_number']) ?>">
                            <?php else: ?>
                                <i class="fas fa-qrcode fa-4x" style="color: #ddd;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="qr-info">
                            <div class="table-number"><?= htmlspecialchars($qr['table_number']) ?></div>
                            <div class="table-name"><?= htmlspecialchars($qr['table_name']) ?></div>
                            <div style="margin-top: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; background: <?= $qr['is_active'] ? '#27ae60' : '#95a5a6' ?>; color: white;">
                                    <?= $qr['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                        </div>
                        <div class="qr-actions">
                            <a href="../qr_display.php?id=<?= $qr['id'] ?>" target="_blank" class="btn-view">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $qr['id'] ?>">
                                <button type="submit" class="btn-toggle">
                                    <i class="fas fa-toggle-on"></i> <?= $qr['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus QR code ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $qr['id'] ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <i class="fas fa-qrcode fa-4x" style="color: #ddd; margin-bottom: 1rem;"></i>
                <p style="color: #999;">Belum ada QR Code. Silakan tambah QR Code baru di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>