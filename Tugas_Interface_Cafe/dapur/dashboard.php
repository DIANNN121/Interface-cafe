<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['barista', 'koki'])) {
    header("Location: ../Login.php");
    exit;
}

$role = $_SESSION['role'];
$filter_category = ($role == 'koki') ? "'Makanan', 'Camilan'" : "'Minuman'";

// Handle Status Updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['action'] == 'start' ? 'processing' : 'ready';
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $id");
    header("Location: dashboard.php");
    exit;
}

// Fetch relevant orders based on role (Simulation: In real app, we filter order_items, but here filtering orders is simpler for demo)
// We will show ALL orders that are new/processing, but highlight that they contain relevant items.
$sql = "SELECT * FROM orders WHERE status IN ('new', 'processing') ORDER BY created_at ASC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dapur (<?= ucfirst($role) ?>)</title>
    <link rel="stylesheet" href="../style.css">
    <meta http-equiv="refresh" content="30"> <!-- Auto refresh every 30s -->
    <style>
        .kitchen-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; padding: 2rem; }
        .order-ticket { background: white; border-top: 5px solid #ccc; box-shadow: var(--shadow); border-radius: 0 0 8px 8px; }
        .ticket-new { border-color: #2196f3; }
        .ticket-processing { border-color: #ff9800; }
        .ticket-header { padding: 1rem; border-bottom: 1px dashed #ddd; display: flex; justify-content: space-between; font-weight: bold; background: #fafafa; }
        .ticket-items { padding: 1rem; }
        .ticket-footer { padding: 1rem; display: flex; gap: 0.5rem; }
    </style>
</head>
<body style="background: #e0e0e0;">
    <div style="background: #333; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h2>Dapur: <?= ucfirst($role) ?></h2>
        <a href="../logout.php" style="color: #fff;">Logout</a>
    </div>

    <div class="kitchen-grid">
        <?php while($row = $orders->fetch_assoc()): ?>
            <?php
                // Fetch items for this order
                $oid = $row['id'];
                $items = $conn->query("SELECT oi.*, m.name, m.category FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = $oid");
                
                // Check if this order has items relevant to this role
                $relevant = false;
                $items_html = "";
                while($item = $items->fetch_assoc()) {
                    // Koki sees Makanan, Barista sees Minuman
                    $is_mine = false;
                    if ($role == 'koki' && in_array($item['category'], ['Makanan', 'Camilan'])) $is_mine = true;
                    if ($role == 'barista' && $item['category'] == 'Minuman') $is_mine = true;
                    
                    if ($is_mine) $relevant = true;
                    
                    $style = $is_mine ? "font-weight:bold; color:black;" : "color:#aaa;";
                    $items_html .= "<li style='$style'>".$item['quantity']."x ".$item['name']."</li>";
                }

                if (!$relevant) continue; // Skip if no items for this role
            ?>
            <div class="order-ticket <?= $row['status'] == 'new' ? 'ticket-new' : 'ticket-processing' ?>">
                <div class="ticket-header">
                    <span>#<?= $row['order_number'] ?></span>
                    <span>Meja <?= $row['table_number'] ?></span>
                </div>
                <div class="ticket-items">
                    <ul style="list-style-type: none; padding: 0;">
                        <?= $items_html ?>
                    </ul>
                    <?php if($row['customer_name']): ?>
                        <div style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                            Pelanggan: <?= $row['customer_name'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ticket-footer">
                    <?php if($row['status'] == 'new'): ?>
                        <a href="?action=start&id=<?= $row['id'] ?>" class="btn btn-primary" style="flex:1; text-align:center;">Proses</a>
                    <?php elseif($row['status'] == 'processing'): ?>
                        <a href="?action=finish&id=<?= $row['id'] ?>" class="btn btn-primary" style="flex:1; text-align:center; background-color: #4caf50;">Selesai</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
