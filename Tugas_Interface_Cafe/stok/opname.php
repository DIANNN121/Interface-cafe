<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas_stok') { header("Location: ../Login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['item_id'];
    $actual = $_POST['actual'];
    
    // Get system qty
    $sys = $conn->query("SELECT quantity FROM stock_items WHERE id = $id")->fetch_assoc()['quantity'];
    $diff = $actual - $sys;
    
    // Save to opname
    $conn->query("INSERT INTO stock_opname (stock_item_id, quantity_system, quantity_physical, difference, opname_date) VALUES ($id, $sys, $actual, $diff, CURDATE())");
    
    // Update master stock? Usually opname updates master, or just logs it. Let's update master to match physical.
    $conn->query("UPDATE stock_items SET quantity = $actual WHERE id = $id");
    
    $message = "Stok berhasil diperbarui!";
}

$stocks = $conn->query("SELECT * FROM stock_items");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Stok Opname</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container" style="padding-top: 2rem; max-width: 600px;">
        <h2>Input Stok Fisik (Opname)</h2>
        <?php if(isset($message)) echo "<p style='color:green'>$message</p>"; ?>
        
        <form method="POST" style="background: white; padding: 2rem; margin-top: 1rem; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="form-group">
                <label>Pilih Item</label>
                <select name="item_id" class="form-control">
                    <?php while($row = $stocks->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?> (Sistem: <?= $row['quantity'] ?> <?= $row['unit'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jumlah Fisik (Aktual)</label>
                <input type="number" step="0.01" name="actual" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%">Simpan Perubahan</button>
            <a href="dashboard.php" style="display:block; text-align:center; margin-top:1rem;">Kembali</a>
        </form>
    </div>
</body>
</html>
