<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

// Handle Form Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        /* ADD NEW MENU */
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            $category = $conn->real_escape_string($_POST['category']);
            $price = $_POST['price'];
            $description = $conn->real_escape_string($_POST['description']);
            
            $sql = "INSERT INTO menu (name, category, price, description) VALUES ('$name', '$category', '$price', '$description')";
            if ($conn->query($sql)) $message = "Menu berhasil ditambahkan!";
            else $message = "Error: " . $conn->error;
        } 
        /* DELETE MENU */
        elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            $conn->query("DELETE FROM menu WHERE id=$id");
            $message = "Menu berhasil dihapus!";
        }
    }
}

$menu_list = $conn->query("SELECT * FROM menu ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Kafe Ndalem</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: var(--accent-color); color: white; padding: 2rem 0; position: fixed; height: 100%; top:0; left:0;}
        .sidebar-brand { padding: 0 1.5rem 2rem; font-size: 1.5rem; font-family: 'Playfair Display', serif; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { margin-top: 1rem; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--primary-color); }
        .sidebar-menu i { width: 30px; }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; background-color: #f4f6f8; }
        
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #eee; }
        th { background-color: #f9f9f9; color: var(--accent-color); }
        
        .modal { display: none; position: fixed; z-index: 1002; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 2rem; border-radius: 8px; width: 50%; max-width: 500px; position: relative; }
        .close { float: right; font-size: 1.5rem; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">Kafe Ndalem</div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_menu.php" class="active"><i class="fas fa-utensils"></i> Menu</a>
                <a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a>
                <a href="stock.php"><i class="fas fa-boxes"></i> Stok</a>
                <a href="logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Kelola Menu</h2>
                <button onclick="openModal()" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Menu</button>
            </header>

            <?php if($message): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $menu_list->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><span style="background: #eee; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem;"><?= htmlspecialchars($row['category']) ?></span></td>
                            <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                            <td style="color: #666; font-size: 0.9rem;"><?= htmlspecialchars($row['description']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Hapus menu ini?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" style="color: #c62828; background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 style="margin-bottom: 1.5rem;">Tambah Menu Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="Minuman">Minuman</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Camilan">Camilan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Menu</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('addModal').style.display = 'block'; }
        function closeModal() { document.getElementById('addModal').style.display = 'none'; }
        window.onclick = function(event) {
            if (event.target == document.getElementById('addModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
