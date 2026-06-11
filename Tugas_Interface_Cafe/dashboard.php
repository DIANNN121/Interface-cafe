<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

// Simple stats
$count_menu = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'];
$count_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'new'")->fetch_assoc()['total'];
$count_stock = $conn->query("SELECT COUNT(*) as total FROM stock_items WHERE quantity <= threshold")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kafe Nimet</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .stat-info p {
            margin: 0;
            color: #666;
            font-weight: 500;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            color: var(--accent-color);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                Kafe Nimet
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_menu.php"><i class="fas fa-utensils"></i> Menu</a>
                <a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a>
                <a href="stock.php"><i class="fas fa-boxes"></i> Stok</a>
                <a href="logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
                <div style="color: #666;">
                    <?= date('l, d F Y') ?>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $count_menu ?></h3>
                        <p>Total Menu</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $count_orders ?></h3>
                        <p>Pesanan Baru</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $count_stock ?></h3>
                        <p>Stok Menipis</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>

            <!-- Quick Actions or Recent Activity could go here -->
            <div class="stat-card" style="display: block;">
                <h4 style="border-bottom: 1px solid #eee; padding-bottom: 1rem;">Petunjuk Penggunaan</h4>
                <p style="margin-top: 1rem; color: #555;">
                    Gunakan sidebar di sebelah kiri untuk mengelola data kafe. <br>
                    - <strong>Menu:</strong> Tambah, ubah, atau hapus item menu. <br>
                    - <strong>Pesanan:</strong> Lihat daftar pesanan masuk (Kasir). <br>
                    - <strong>Stok:</strong> Pantau stok bahan baku.
                </p>
            </div>
        </main>
    </div>
</body>

</html>