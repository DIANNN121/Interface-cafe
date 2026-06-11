<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../Login.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $username = $conn->real_escape_string($_POST['username']);
            $password = $_POST['password'];
            $full_name = $conn->real_escape_string($_POST['full_name']);
            $role = $_POST['role'];

            // Check if username exists
            $check = $conn->query("SELECT id FROM users WHERE username='$username'");
            if ($check->num_rows > 0) {
                $message = "Username sudah digunakan!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$hashed_password', '$full_name', '$role')";
                if ($conn->query($sql)) {
                    $message = "User berhasil ditambahkan!";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            if ($id != $_SESSION['user_id']) { // Check prevent self-delete
                $conn->query("DELETE FROM users WHERE id=$id");
                $message = "User berhasil dihapus!";
            } else {
                $message = "Tidak bisa menghapus akun sendiri!";
            }
        }
    }
}

$users_list = $conn->query("SELECT * FROM users ORDER BY role, username");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Staff - Kafe Nimet</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .role-admin {
            background: #ffebee;
            color: #c62828;
        }

        .role-kasir {
            background: #e3f2fd;
            color: #1565c0;
        }

        .role-barista {
            background: #fff3e0;
            color: #ef6c00;
        }

        .role-koki {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .role-pelayan {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .role-stok {
            background: #eceff1;
            color: #546e7a;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">Kafe Nimet</div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Laporan</a>
                <a href="manage_menu.php"><i class="fas fa-utensils"></i> Kelola Menu</a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> Kelola Staff</a>
                <a href="../logout.php" style="margin-top: 3rem; color: #ff8a80;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Kelola Staff</h2>
                <button onclick="document.getElementById('addModal').style.display='block'" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah User</button>
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
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users_list->fetch_assoc()):
                            $role_class = 'role-' . $row['role'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><span class="badge <?= $role_class ?>"><?= ucfirst(str_replace('_', ' ', $row['role'])) ?></span></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" onsubmit="return confirm('Hapus user ini?')" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" style="color:red;border:none;background:none;cursor:pointer;"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#ccc; font-size:0.8rem;">(Akun Anda)</span>
                                    <?php endif; ?>
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
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <h3>Tambah User Staff</h3><br>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="kasir">Kasir</option>
                        <option value="barista">Barista</option>
                        <option value="koki">Koki</option>
                        <option value="pelayan">Pelayan</option>
                        <option value="petugas_stok">Petugas Stok</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Simpan</button>
            </form>
        </div>
    </div>
</body>

</html>