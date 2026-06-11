<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas_stok') { header("Location: ../Login.php"); exit; }

$stocks = $conn->query("SELECT * FROM stock_items");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Gudang - Stok</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="padding-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Data Stok Gudang</h2>
            <div>
                <a href="opname.php" class="btn btn-primary">Input Opname (Fisik)</a>
                <a href="../logout.php" class="btn btn-outline" style="border: none; color: red;">Logout</a>
            </div>
        </div>

        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #eee;">
                    <tr>
                        <th style="padding:1rem; text-align:left;">Item</th>
                        <th style="padding:1rem; text-align:left;">Unit</th>
                        <th style="padding:1rem; text-align:left;">Sistem</th>
                        <th style="padding:1rem; text-align:left;">Lokasi</th>
                        <th style="padding:1rem; text-align:left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $stocks->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:1rem;"><?= htmlspecialchars($row['name']) ?></td>
                        <td style="padding:1rem;"><?= $row['unit'] ?></td>
                        <td style="padding:1rem; font-weight: bold;"><?= $row['quantity'] ?></td>
                        <td style="padding:1rem;"><?= $row['location'] ?></td>
                        <td style="padding:1rem;">
                            <?php if($row['quantity'] <= $row['threshold']): ?>
                                <span style="color: red; font-weight: bold;">Restock!</span>
                            <?php else: ?>
                                <span style="color: green;">Aman</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
