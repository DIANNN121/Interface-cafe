<?php
require_once 'config.php';

// Get table number from URL parameter
$table_number = isset($_GET['table']) ? htmlspecialchars($_GET['table']) : '';
$table_name = '';

// Get table info if table number is provided
if ($table_number) {
    $stmt = $conn->prepare("SELECT table_name FROM table_qr_codes WHERE table_number = ? AND is_active = 1");
    $stmt->bind_param("s", $table_number);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $table_name = $row['table_name'];
    }
    $stmt->close();
}

// Get all active menu items
$menu_items = $conn->query("SELECT * FROM menu WHERE is_active = 1 ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Kafe Ndalem - Pesan Sekarang!</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 1.8rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .table-info {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .menu-section {
            margin-bottom: 2rem;
        }

        .category-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 2rem 0 1rem 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
        }

        .menu-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .menu-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-image i {
            font-size: 4rem;
            color: #c3cfe2;
        }

        .menu-info {
            padding: 1.5rem;
        }

        .menu-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .menu-description {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .menu-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-price {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .add-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Cart Floating Button */
        .cart-float {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 999;
        }

        .cart-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
            transition: all 0.3s ease;
            position: relative;
        }

        .cart-btn:hover {
            transform: scale(1.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border: 3px solid white;
        }

        /* Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .cart-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 30px 30px 0 0;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .cart-header {
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .cart-header h2 {
            font-size: 1.5rem;
            color: #333;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .cart-items {
            padding: 1rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .cart-item-price {
            color: #666;
            font-size: 0.9rem;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: #667eea;
            color: white;
        }

        .qty-number {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }

        .cart-footer {
            padding: 1.5rem;
            background: #f9f9f9;
            border-top: 2px solid #f0f0f0;
            position: sticky;
            bottom: 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .cart-float {
                bottom: 1rem;
                right: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><i class="fas fa-coffee"></i> Kafe Ndalem</h1>
        <?php if ($table_number): ?>
            <span class="table-info">
                <i class="fas fa-chair"></i> <?= $table_name ? $table_name : 'Meja ' . $table_number ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="container">
        <?php
        $categories = [];
        $menu_items->data_seek(0);
        while ($item = $menu_items->fetch_assoc()) {
            $cat = $item['category'] ?: 'Lainnya';
            $categories[$cat][] = $item;
        }

        foreach ($categories as $category => $items):
        ?>
            <div class="menu-section">
                <h2 class="category-title"><i class="fas fa-utensils"></i> <?= htmlspecialchars($category) ?></h2>
                <div class="menu-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="menu-card">
                            <div class="menu-image">
                                <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-coffee"></i>
                                <?php endif; ?>
                            </div>
                            <div class="menu-info">
                                <div class="menu-name"><?= htmlspecialchars($item['name']) ?></div>
                                <?php if ($item['description']): ?>
                                    <div class="menu-description"><?= htmlspecialchars($item['description']) ?></div>
                                <?php endif; ?>
                                <div class="menu-footer">
                                    <div class="menu-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                                    <button class="add-btn" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', <?= $item['price'] ?>)">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Floating Cart Button -->
    <div class="cart-float">
        <button class="cart-btn" onclick="toggleCart()">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
        </button>
    </div>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cart-modal" onclick="closeCartIfOutside(event)">
        <div class="cart-content">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart"></i> Keranjang</h2>
                <button class="close-btn" onclick="toggleCart()"><i class="fas fa-times"></i></button>
            </div>
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Keranjang Anda masih kosong</p>
                </div>
            </div>
            <div class="cart-footer" id="cart-footer" style="display: none;">
                <div class="total-row">
                    <span>Total:</span>
                    <span id="total-price">Rp 0</span>
                </div>
                <button class="checkout-btn" onclick="processOrder()">
                    <i class="fas fa-check-circle"></i> Pesan Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
        let cart = {};
        const tableNumber = '<?= $table_number ?>';

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

            // Show animation
            const badge = document.getElementById('cart-badge');
            badge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                badge.style.transform = 'scale(1)';
            }, 200);
        }

        function updateQty(id, change) {
            if (cart[id]) {
                cart[id].qty += change;
                if (cart[id].qty <= 0) {
                    delete cart[id];
                }
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            const badge = document.getElementById('cart-badge');
            const footer = document.getElementById('cart-footer');

            let totalItems = 0;
            let totalPrice = 0;
            let html = '';

            for (const [id, item] of Object.entries(cart)) {
                totalItems += item.qty;
                totalPrice += item.price * item.qty;

                html += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">Rp ${item.price.toLocaleString('id-ID')} × ${item.qty}</div>
                        </div>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateQty(${id}, -1)">−</button>
                            <span class="qty-number">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${id}, 1)">+</button>
                        </div>
                    </div>
                `;
            }

            if (totalItems > 0) {
                container.innerHTML = html;
                badge.textContent = totalItems;
                badge.style.display = 'flex';
                footer.style.display = 'block';
                document.getElementById('total-price').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
            } else {
                container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Keranjang Anda masih kosong</p>
                    </div>
                `;
                badge.style.display = 'none';
                footer.style.display = 'none';
            }
        }

        function toggleCart() {
            const modal = document.getElementById('cart-modal');
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            } else {
                modal.style.display = 'block';
            }
        }

        function closeCartIfOutside(event) {
            if (event.target.id === 'cart-modal') {
                toggleCart();
            }
        }

        function processOrder() {
            if (Object.keys(cart).length === 0) {
                alert('Keranjang Anda masih kosong!');
                return;
            }

            const customerName = prompt('Silakan masukkan nama Anda:', '');
            if (!customerName || customerName.trim() === '') {
                alert('Nama harus diisi!');
                return;
            }

            const data = {
                name: customerName.trim(),
                table: tableNumber || 'Guest',
                items: cart
            };

            fetch('process_guest_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Pesanan berhasil!\n\nNomor Order: ' + data.order_number + '\n\nTerima kasih! Pesanan Anda sedang diproses.');
                        cart = {};
                        renderCart();
                        toggleCart();
                    } else {
                        alert('❌ Gagal memproses pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Terjadi kesalahan. Silakan coba lagi.');
                    console.error('Error:', error);
                });
        }

        // Add smooth transition to cart badge
        document.getElementById('cart-badge').style.transition = 'transform 0.2s ease';
    </script>
</body>

</html>