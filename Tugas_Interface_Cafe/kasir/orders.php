<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../Login.php");
    exit;
}

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan - Kasir</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--accent-color);
            color: white;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
        }

        .bg-new {
            background: #e3f2fd;
            color: #1565c0;
        }

        .bg-processing {
            background: #fff3e0;
            color: #ef6c00;
        }

        .bg-ready {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .bg-completed {
            background: #f5f5f5;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
            <h2>Daftar Pesanan</h2>
            <a href="dashboard.php" class="btn btn-primary">Kembali ke POS</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_number'] ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['table_number']) ?></td>
                        <td>Rp <?= number_format($row['total'], 0) ?></td>
                        <td>
                            <?php
                            $status_class = 'bg-completed';
                            if ($row['status'] == 'new') $status_class = 'bg-new';
                            if ($row['status'] == 'processing') $status_class = 'bg-processing';
                            if ($row['status'] == 'ready') $status_class = 'bg-ready';
                            ?>
                            <span class="badge <?= $status_class ?>"><?= strtoupper($row['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] != 'completed'): ?>
                                <a href="payment.php?id=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Bayar</a>
                            <?php else: ?>
                                <span style="color: green;"><i class="fas fa-check"></i> Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>