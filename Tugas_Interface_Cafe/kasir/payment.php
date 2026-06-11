<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../Login.php");
    exit;
}

$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Complete the payment
    $conn->query("UPDATE orders SET status = 'completed', payment_method = '" . $_POST['method'] . "' WHERE id = $id");

    // Redirect w/ success indicator? Or just back to orders
    echo "<script>alert('Pembayaran Berhasil!'); window.location.href='orders.php';</script>";
    exit;
}

$order = $conn->query("SELECT * FROM orders WHERE id = $id")->fetch_assoc();
$items = $conn->query("SELECT oi.*, m.name FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = $id");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Kafe Nimet</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .split-layout {
            display: flex;
            gap: 2rem;
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .receipt-card {
            flex: 1;
            background: white;
            padding: 2rem;
            border-radius: 2px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            /* Receipt look */
            background-image: linear-gradient(135deg, #fff 50%, transparent 50%), linear-gradient(45deg, #fff 50%, transparent 50%);
            background-position: bottom;
            background-size: 20px 20px;
            background-repeat: repeat-x;
            padding-bottom: 40px;
            border-top: 5px solid var(--primary-color);
        }

        .payment-card {
            flex: 1;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #ddd;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .receipt-total {
            border-top: 2px dashed #ddd;
            margin-top: 1rem;
            padding-top: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
        }

        @media print {

            .payment-card,
            .no-print {
                display: none;
            }

            .split-layout {
                display: block;
                margin: 0;
            }

            .receipt-card {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body style="background: #f4f6f8;">
    <div class="split-layout">
        <!-- Section Kiri: Struk -->
        <div class="receipt-card" id="receipt">
            <div class="receipt-header">
                <h3>Kafe Nimet</h3>
                <p>Jl. Pertanian III No.22</p>
                <p><small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small></p>
                <p>Order #<?= $order['order_number'] ?><br>Meja: <?= htmlspecialchars($order['table_number']) ?></p>
            </div>

            <div class="receipt-body">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="receipt-item">
                        <span><?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                        <span><?= number_format($item['price'] * $item['quantity'], 0) ?></span>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="receipt-total">
                <span>TOTAL</span>
                <span>Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>

            <p style="text-align: center; margin-top: 2rem; font-size: 0.9rem; color: #666;">Terima Kasih!</p>
        </div>

        <!-- Section Kanan: Form Pembayaran -->
        <div class="payment-card">
            <h2>Proses Pembayaran</h2>
            <form method="POST" style="margin-top: 1.5rem;" onsubmit="return validatePayment()">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="method" id="payment_method" class="form-control" onchange="toggleCashInput()">
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="e-wallet">E-Wallet</option>
                        <option value="debit">Kartu Debit</option>
                    </select>
                </div>

                <div id="cash-section">
                    <div class="form-group">
                        <label>Uang Diterima (Rp)</label>
                        <input type="number" id="cash_received" class="form-control" placeholder="0" oninput="calculateChange()">
                    </div>
                    <div class="form-group">
                        <label>Kembalian</label>
                        <input type="text" id="change_amount" class="form-control" readonly style="background: #eee; font-weight: bold; color: var(--primary-color);">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Bayar & Selesai</button>
                <button type="button" onclick="window.print()" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;"><i class="fas fa-print"></i> Cetak Struk</button>
                <a href="orders.php" style="display: block; margin-top: 1rem; color: #666; text-align: center;">Kembali</a>
            </form>
        </div>
    </div>

    <script>
        const totalAmount = <?= $order['total'] ?>;

        function toggleCashInput() {
            const method = document.getElementById('payment_method').value;
            const cashSection = document.getElementById('cash-section');
            cashSection.style.display = (method === 'cash') ? 'block' : 'none';
        }

        function calculateChange() {
            const received = document.getElementById('cash_received').value;
            const change = received - totalAmount;
            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });

            if (change >= 0) {
                document.getElementById('change_amount').value = formatter.format(change);
                document.getElementById('change_amount').style.color = 'var(--primary-color)';
            } else {
                document.getElementById('change_amount').value = 'Kurang: ' + formatter.format(Math.abs(change));
                document.getElementById('change_amount').style.color = 'red';
            }
        }

        function validatePayment() {
            const method = document.getElementById('payment_method').value;
            if (method === 'cash') {
                const received = document.getElementById('cash_received').value;
                if (received < totalAmount) {
                    alert('Uang diterima kurang!');
                    return false;
                }
            }
            // Trigger print before submitting? Or just submit.
            // Let's print automatically on success in real life, but here just alert.
            return confirm('Selesaikan pesanan ini?');
        }

        toggleCashInput();
    </script>
</body>

</html>