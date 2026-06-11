<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../Login.php");
    exit;
}

$menu_items = $conn->query("SELECT * FROM menu WHERE is_active = 1");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kasir - Buat Pesanan</title>
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
    </style>
</head>

<body>
    <div class="pos-container">
        <!-- Menu Area -->
        <div class="menu-area">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Kasir - Pesanan Baru</h2>
                <a href="orders.php" class="btn btn-outline">Lihat Daftar Pesanan</a>
                <a href="../logout.php" style="color: red;">Logout</a>
            </div>

            <div class="menu-grid">
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                    <div class="pos-card" onclick="addToCart(<?= $item['id'] ?>, '<?= $item['name'] ?>', <?= $item['price'] ?>)">
                        <div style="height: 80px; background: #eee; margin-bottom: 0.5rem; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:4px;">
                            <?php if (!empty($item['image']) && file_exists("../" . $item['image'])): ?>
                                <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-coffee fa-2x" style="color:#ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <strong><?= $item['name'] ?></strong>
                        <div style="color: var(--primary-color);">Rp <?= number_format($item['price'], 0) ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Cart Area -->
        <div class="cart-area">
            <div style="padding: 1rem; border-bottom: 1px solid #ddd;">
                <h3>Keranjang</h3>
                <input type="text" id="customer_name" placeholder="Nama Pelanggan" class="form-control" style="margin-top: 0.5rem;">
                <input type="text" id="table_number" placeholder="Nomor Meja" class="form-control" style="margin-top: 0.5rem;">
            </div>

            <div class="cart-items" id="cart-items">
                <!-- Javascript will populate this -->
                <p style="text-align: center; color: #999; margin-top: 2rem;">Belum ada item</p>
            </div>

            <div class="cart-footer">
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold;">
                    <span>Total:</span>
                    <span id="total-price">Rp 0</span>
                </div>
                <button onclick="processOrder()" class="btn btn-primary" style="width: 100%;">Proses Pesanan</button>
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
                container.innerHTML = '<p style="text-align: center; color: #999; margin-top: 2rem;">Belum ada item</p>';
            }

            for (const [id, item] of Object.entries(cart)) {
                total += item.price * item.qty;
                container.innerHTML += `
                    <div class="cart-item">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>Rp ${item.price}</small>
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
            const table = document.getElementById('table_number').value;

            if (!name || Object.keys(cart).length === 0) {
                alert('Mohon isi nama pelanggan dan pilih menu!');
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
                        alert('Pesanan berhasil dibuat!');
                        cart = {};
                        renderCart();
                        document.getElementById('customer_name').value = '';
                        document.getElementById('table_number').value = '';
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>