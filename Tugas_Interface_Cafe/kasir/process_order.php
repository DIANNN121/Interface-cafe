<?php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No data']);
    exit;
}

$name = $conn->real_escape_string($input['name']);
$table = $conn->real_escape_string($input['table']);
$order_number = 'ORD-' . time() . '-' . rand(100,999);
$total = 0;

// Calculate Total first
foreach ($input['items'] as $item) {
    $total += $item['price'] * $item['qty'];
}

// 1. Insert Order
$sql = "INSERT INTO orders (order_number, table_number, customer_name, total, status, created_at) VALUES ('$order_number', '$table', '$name', $total, 'new', NOW())";
if ($conn->query($sql)) {
    $order_id = $conn->insert_id;
    
    // 2. Insert Order Items
    foreach ($input['items'] as $id => $item) {
        $menu_id = (int)$id;
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];
        
        $item_sql = "INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES ($order_id, $menu_id, $qty, $price)";
        $conn->query($item_sql);
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>
