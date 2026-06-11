<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$customer_name = $data['name'] ?? 'Guest';
$table_number = $data['table'] ?? 'Online';
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Generate unique order number
$order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

// Calculate total
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['qty'];
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, table_number, customer_name, total, status) VALUES (?, ?, ?, ?, 'new')");
    $stmt->bind_param("sssd", $order_number, $table_number, $customer_name, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $menu_id => $item) {
        $stmt->bind_param("iiid", $order_id, $menu_id, $item['qty'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process order: ' . $e->getMessage()
    ]);
}

$conn->close();
