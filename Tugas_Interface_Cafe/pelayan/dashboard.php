<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelayan') {
    header("Location: ../Login.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE orders SET status = 'served' WHERE id = $id");
    header("Location: dashboard.php");
    exit;
}

// Fetch ALL active orders (not just ready)
$orders = $conn->query("SELECT * FROM orders WHERE status IN ('new', 'processing', 'ready') ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelayan - Kafe Nimet</title>
    <link rel="stylesheet" href="../style.css">
    <meta http-equiv="refresh" content="10">
    <style>
        .container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
            border-left: 5px solid #ccc;
        }

        .status-new {
            border-color: #2196f3;
        }

        .status-processing {
            border-color: #ff9800;
        }

        .status-ready {
            border-color: #4caf50;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }

        .bg-new {
            background-color: #2196f3;
        }

        .bg-processing {
            background-color: #ff9800;
        }

        .bg-ready {
            background-color: #4caf50;
        }

        .item-list {
            margin: 1rem 0;
            padding-left: 1.5rem;
            color: #555;
        }
    </style>
</head>

<body style="background: #f4f6f8;">
    <div style="background: #fff; padding: 1rem; border-bottom: 1px solid #ddd; text-align: center;">
        <h2 style="color: var(--primary-color);">Antrean Pesanan</h2>
        <div style="margin-top: 1rem;">
            <a href="create_order.php" class="btn btn-primary" style="margin-right: 1rem;">+ Buat Pesanan Baru</a>
            <a href="../logout.php" class="btn btn-outline" style="font-size: 0.9rem;">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($orders->num_rows == 0): ?>
            <p style="text-align: center; color: #999; margin-top: 3rem;">Belum ada pesanan aktif.</p>
        <?php endif; ?>

        <?php while ($row = $orders->fetch_assoc()): ?>
            <?php
            // Fetch Items
            $oid = $row['id'];
            $items = $conn->query("SELECT oi.*, m.name FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = $oid");
            ?>
            <div class="order-card status-<?= $row['status'] ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h3 style="margin: 0; margin-bottom: 0.5rem;">Meja <?= htmlspecialchars($row['table_number']) ?></h3>
                        <span style="color: #666;">#<?= $row['order_number'] ?> - <?= htmlspecialchars($row['customer_name']) ?></span>
                    </div>
                    <div>
                        <span class="badge bg-<?= $row['status'] ?>">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </div>
                </div>

                <ul class="item-list">
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <li><?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?></li>
                    <?php endwhile; ?>
                </ul>

                <div style="text-align: right;">
                    <?php if ($row['status'] == 'ready'): ?>
                        <a href="?action=serve&id=<?= $row['id'] ?>" class="btn btn-primary">Antar Pesanan</a>
                    <?php else: ?>
                        <span style="color: #999; font-size: 0.9rem;">
                            <?php if ($row['status'] == 'new') echo "Menunggu konfirmasi dapur..."; ?>
                            <?php if ($row['status'] == 'processing') echo "Sedang disiapkan..."; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>