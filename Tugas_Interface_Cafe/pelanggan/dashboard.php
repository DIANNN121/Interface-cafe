<?php
session_start();
require_once '../config.php';

// Check Login. If not logged in, redirect to login page with return url
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelanggan') {
    $redirect = urlencode("pelanggan/dashboard.php");
    header("Location: ../Login.php?redirect=$redirect");
    exit;
}

$menu_items = $conn->query("SELECT * FROM menu WHERE is_active = 1");
$my_orders = $conn->query("SELECT * FROM orders WHERE customer_name = '" . $_SESSION['username'] . "' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafe Nimet - Pesan Menu</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .pos-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .menu-area {
            flex: 2;
            padding: 1rem;
            overflow-y: auto;
            background: #f4f6f8;
        }

        .cart-area {
            flex: 1;
            min-width: 300px;
            background: white;
            border-left: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .pos-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pos-card:hover {
            transform: translateY(-3px);
            border: 1px solid var(--primary-color);
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .cart-footer {
            padding: 1.5rem;
            background: #fafafa;
            border-top: 1px solid #ddd;
        }

        .qty-controls button {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 1px solid #ccc;
            cursor: pointer;
        }

        .order-history {
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .pos-container {
                flex-direction: column;
                height: auto;
                overflow: auto;
            }

            .cart-area {
                border-left: none;
                border-top: 1px solid #ddd;
                height: auto;
            }
        }
    </style>
</head>

<body>
    <div class="pos-container">
        <!-- Menu Area -->
        <div class="menu-area">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h2>Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                    <p style="color: #666;">Mau pesan apa hari ini?</p>
                </div>
                <div>
                    <a href="../logout.php" class="btn btn-outline" style="font-size: 0.8rem;">Logout</a>
                </div>
            </div>

            <!-- Active Orders -->
            <?php if ($my_orders->num_rows > 0): ?>
                <div class="order-history">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Pesanan Terakhir Anda</h3>
                    <?php while ($order = $my_orders->fetch_assoc()): ?>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 0.5rem 0;">
                            <div>
                                <strong>#<?= $order['order_number'] ?></strong>
                                <small class="status-badge" style="background: #eee;"><?= strtoupper($order['status']) ?></small>
                            </div>
                            <div>Rp <?= number_format($order['total'], 0) ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <h3 style="margin-bottom: 1rem;">Daftar Menu</h3>
            <div class="menu-grid">
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                    <div class="pos-card" onclick="addToCart(<?= $item['id'] ?>, '<?= $item['name'] ?>', <?= $item['price'] ?>)">
                        <div style="height: 100px; background: #eee; margin-bottom: 0.5rem; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:4px;">
                            <?php if (!empty($item['image']) && file_exists("../" . $item['image'])): ?>
                                <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-coffee fa-3x" style="color:#ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <strong><?= $item['name'] ?></strong>
                        <div style="color: var(--primary-color);">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Cart Area -->
        <div class="cart-area">
            <div style="padding: 1rem; border-bottom: 1px solid #ddd;">
                <h3>Keranjang</h3>
                <input type="text" id="table_number" placeholder="Nomor Meja (opsional)" class="form-control" style="margin-top: 0.5rem;">
                <!-- Customer Name is hidden as we use session username -->
                <input type="hidden" id="customer_name" value="<?= $_SESSION['username'] ?>">
            </div>

            <div class="cart-items" id="cart-items">
                <p style="text-align: center; color: #999; margin-top: 2rem;">Silakan pilih menu</p>
            </div>

            <div class="cart-footer">
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold;">
                    <span>Total:</span>
                    <span id="total-price">Rp 0</span>
                </div>
                <button onclick="processOrder()" class="btn btn-primary" style="width: 100%;">Pesan Sekarang</button>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

        function addToCart(id, name, price) {
            if (cart[id]) {
                cart[id].qty++;
            } else {
                cart[id] = {
                    name: name,
                    price: price,
                    qty: 1
                };
            }
            renderCart();
        }

        function updateQty(id, change) {
            if (cart[id]) {
                cart[id].qty += change;
                if (cart[id].qty <= 0) delete cart[id];
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            let total = 0;

            if (Object.keys(cart).length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999; margin-top: 2rem;">Silakan pilih menu</p>';
            }

            for (const [id, item] of Object.entries(cart)) {
                total += item.price * item.qty;
                container.innerHTML += `
                    <div class="cart-item">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>Rp ${item.price.toLocaleString('id-ID')}</small>
                        </div>
                        <div class="qty-controls">
                            <button onclick="updateQty(${id}, -1)">-</button>
                            ${item.qty}
                            <button onclick="updateQty(${id}, 1)">+</button>
                        </div>
                    </div>
                `;
            }
            document.getElementById('total-price').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        function processOrder() {
            const name = document.getElementById('customer_name').value;
            const table = document.getElementById('table_number').value || 'Take Away'; // Default to Take Away if empty

            if (Object.keys(cart).length === 0) {
                alert('Keranjang Anda kosong!');
                return;
            }

            const data = {
                name: name,
                table: table,
                items: cart
            };

            fetch('process_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibuat! Mohon tunggu pesanan Anda.');
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>