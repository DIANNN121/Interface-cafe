<?php
require_once 'config.php';

$qr_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($qr_id == 0) {
    die('Invalid QR Code ID');
}

// Get QR code info
$stmt = $conn->prepare("SELECT * FROM table_qr_codes WHERE id = ?");
$stmt->bind_param("i", $qr_id);
$stmt->execute();
$result = $stmt->get_result();
$qr = $result->fetch_assoc();
$stmt->close();

if (!$qr) {
    die('QR Code not found');
}

$menu_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/menu.php?table=' . urlencode($qr['table_number']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - <?= htmlspecialchars($qr['table_name']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .qr-container {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table-info {
            font-size: 1.5rem;
            color: #333;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .qr-code-image {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .qr-code-image img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .instructions {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .instructions h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .instructions ol {
            margin-left: 1.5rem;
            color: #666;
            line-height: 1.8;
        }

        .instructions li {
            margin-bottom: 0.5rem;
        }

        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .print-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .qr-container {
                box-shadow: none;
                max-width: 100%;
            }

            .print-btn,
            .back-btn {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .qr-container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .table-info {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="qr-container">
        <div class="logo">☕</div>
        <h1>Kafe Ndalem</h1>
        <div class="table-info"><?= htmlspecialchars($qr['table_name']) ?></div>

        <div class="qr-code-image">
            <?php if ($qr['qr_code_path'] && file_exists($qr['qr_code_path'])): ?>
                <img src="<?= htmlspecialchars($qr['qr_code_path']) ?>" alt="QR Code <?= htmlspecialchars($qr['table_name']) ?>">
            <?php else: ?>
                <i class="fas fa-qrcode fa-5x" style="color: #ddd;"></i>
                <p style="color: #999; margin-top: 1rem;">QR Code belum tersedia</p>
            <?php endif; ?>
        </div>

        <div class="instructions">
            <h3><i class="fas fa-mobile-alt"></i> Cara Pemesanan:</h3>
            <ol>
                <li>Scan QR Code di atas menggunakan kamera HP</li>
                <li>Pilih menu yang Anda inginkan</li>
                <li>Tambahkan ke keranjang</li>
                <li>Klik "Pesan Sekarang" untuk mengirim pesanan</li>
                <li>Tunggu pesanan Anda datang!</li>
            </ol>
        </div>

        <div style="margin-top: 2rem;">
            <button onclick="window.print()" class="print-btn">
                <i class="fas fa-print"></i> Cetak QR Code
            </button>
            <a href="admin/qr_codes.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-radius: 10px; font-size: 0.85rem; color: #856404;">
            <strong>Link Menu:</strong><br>
            <code style="word-break: break-all;"><?= htmlspecialchars($menu_url) ?></code>
        </div>
    </div>
</body>

</html>