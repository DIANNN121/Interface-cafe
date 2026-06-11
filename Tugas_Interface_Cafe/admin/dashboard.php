<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../Login.php");
    exit;
}

// Stats basically match Owner view
$sales_today = $conn->query("SELECT SUM(total) as total FROM orders WHERE date(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE date(created_at) = CURDATE()")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Pemilik - Kafe Nimet</title>
    <link rel="stylesheet" href="../style.css">
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

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">Kafe Nimet</div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Laporan (Pemilik)</a>
                <a href="manage_menu.php"><i class="fas fa-utensils"></i> Kelola Menu</a>
                <a href="users.php"><i class="fas fa-users"></i> Kelola Staff</a>
                <a href="../logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <main class="main-content">
            <h2>Laporan Harian</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                <div class="stat-card">
                    <h3>Rp <?= number_format($sales_today, 0, ',', '.') ?></h3>
                    <p>Pendapatan Hari Ini</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total_orders ?></h3>
                    <p>Total Pesanan Hari Ini</p>
                </div>
            </div>

            <div class="stat-card" style="margin-top: 2rem;">
                <h4>Laporan Penjualan (Simulasi Grafik)</h4>
                <div style="height: 200px; background-color: #eee; margin-top: 1rem; display: flex; align-items: center; justify-content: center; color: #888;">
                    [Grafik Penjualan Bulanan akan muncul disini]
                </div>
            </div>
        </main>
    </div>
</body>

</html>