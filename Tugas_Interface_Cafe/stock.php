<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$stocks = $conn->query("SELECT * FROM stock_items");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Bahan - Kafe Nimet</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--accent-color);
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
        }

        .sidebar-brand {
            padding: 0 1.5rem 2rem;
            font-size: 1.5rem;
            font-family: 'Playfair Display', serif;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            margin-top: 1rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }

        .sidebar-menu i {
            width: 30px;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            background-color: #f4f6f8;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">Kafe Nimet</div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_menu.php"><i class="fas fa-utensils"></i> Menu</a>
                <a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a>
                <a href="stock.php" class="active"><i class="fas fa-boxes"></i> Stok</a>
                <a href="logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        <main class="main-content">
            <h2>Stok Bahan Baku</h2>
            <br>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Stok Saat Ini</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stocks->fetch_assoc()): ?>
                            <?php
                            $status_color = ($row['quantity'] <= $row['threshold']) ? '#ffebee' : 'transparent';
                            $status_text = ($row['quantity'] <= $row['threshold']) ? '<span style="color:red; font-size:0.8rem; font-weight:bold;">Restock!</span>' : '<span style="color:green; font-size:0.8rem;">Aman</span>';
                            ?>
                            <tr style="background-color: <?= $status_color ?>">
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><strong><?= $row['quantity'] ?></strong></td>
                                <td><?= $row['unit'] ?></td>
                                <td><?= $row['location'] ?></td>
                                <td><?= $status_text ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>