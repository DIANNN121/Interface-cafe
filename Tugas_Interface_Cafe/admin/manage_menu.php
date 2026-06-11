<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../Login.php");
    exit;
}

$message = '';

// Create uploads directory if not exists
$target_dir = "../uploads/menu/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            $category = $conn->real_escape_string($_POST['category']);
            $price = $_POST['price'];
            $description = $conn->real_escape_string($_POST['description']);

            // Image Upload Logic
            $image_path = NULL;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $new_filename = uniqid() . "." . $ext;
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = "uploads/menu/" . $new_filename; // Store relative path for web
                    } else {
                        $message = "Gagal mengupload gambar.";
                    }
                } else {
                    $message = "Format gambar tidak valid. Gunakan JPG, PNG, atau GIF.";
                }
            }

            if (empty($message)) { // Only proceed if no upload error
                $sql = "INSERT INTO menu (name, category, price, description, image) VALUES ('$name', '$category', '$price', '$description', '$image_path')";

                if ($conn->query($sql)) {
                    $message = "Menu berhasil ditambahkan!";
                } else {
                    $message = "Gagal menambah menu: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];

            // Get image path before deleting
            $result = $conn->query("SELECT image FROM menu WHERE id=$id");
            if ($row = $result->fetch_assoc()) {
                $image_path = $row['image'];

                // Delete physical file if exists (for both soft and hard delete)
                if ($image_path && file_exists("../" . $image_path)) {
                    unlink("../" . $image_path);
                }

                // Check if menu has been ordered before
                $check_orders = $conn->query("SELECT COUNT(*) as count FROM order_items WHERE menu_id=$id");
                $order_count = $check_orders->fetch_assoc()['count'];

                if ($order_count > 0) {
                    // Menu has order history - SOFT DELETE
                    $conn->query("UPDATE menu SET is_active = 0 WHERE id=$id");
                    $message = "Menu dinonaktifkan (sudah pernah dipesan $order_count kali). Foto berhasil dihapus.";
                } else {
                    // Menu never ordered - HARD DELETE
                    $conn->query("DELETE FROM menu WHERE id=$id");
                    $message = "Menu dan foto berhasil dihapus dari database!";
                }
            }
        }
    }
}
$menu_list = $conn->query("SELECT * FROM menu WHERE is_active = 1 ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Menu - Kafe Nimet</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Inline styles for admin layout similar to dashboard -->
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--accent-color);
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
        }

        .sidebar-brand {
            padding: 0 1.5rem 2rem;
            font-size: 1.5rem;
            font-family: 'Playfair Display', serif;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            margin-top: 1rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }

        .sidebar-menu i {
            width: 30px;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            background-color: #f4f6f8;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
            position: relative;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .menu-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">Kafe Nimet</div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Laporan</a>
                <a href="manage_menu.php" class="active"><i class="fas fa-utensils"></i> Kelola Menu</a>
                <a href="users.php"><i class="fas fa-users"></i> Kelola Staff</a>
                <a href="../logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Kelola Menu</h2>
                <button onclick="document.getElementById('addModal').style.display='block'" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Menu</button>
            </header>

            <?php if ($message): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $menu_list->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (isset($row['image']) && $row['image']): ?>
                                        <img src="../<?= htmlspecialchars($row['image']) ?>" class="menu-thumb" alt="Foto">
                                    <?php else: ?>
                                        <span style="color:#ccc; font-size:0.8rem;">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td>Rp <?= number_format($row['price'], 0) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Hapus?')" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" style="color:red;border:none;background:none;cursor:pointer;"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <h3>Tambah Menu</h3><br>
                <div class="form-group"><label>Nama</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Kategori</label><select name="category" class="form-control">
                        <option>Minuman</option>
                        <option>Makanan</option>
                        <option>Camilan</option>
                    </select></div>
                <div class="form-group"><label>Harga</label><input type="number" name="price" class="form-control" required></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control"></textarea></div>
                <div class="form-group">
                    <label>Foto Menu (Optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Simpan</button>
            </form>
        </div>
    </div>
</body>

</html>