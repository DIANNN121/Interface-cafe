<?php
session_start();
require_once 'config.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'kasir':
            header("Location: kasir/dashboard.php");
            break;
        case 'barista':
        case 'koki':
            header("Location: dapur/dashboard.php");
            break;
        case 'pelayan':
            header("Location: pelayan/dashboard.php");
            break;
        case 'petugas_stok':
            header("Location: stok/dashboard.php");
            break;
        default:
            // Invalid role, destroy session and stay on login page
            session_destroy();
            $_SESSION = [];
            $error = "Role tidak valid. Silakan login ulang.";
            break;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        // This case should be unreachable if we handled all valid roles with redirects
        // But if we destroyed session above, we should reload to clear $_SESSION globals or just continue to show form
        if (!isset($_SESSION['user_id'])) {
            // Session destroyed, proceed to show login form
        } else {
            exit;
        }
    } else {
        // Session destroyed
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Handle Redirect if exists
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                header("Location: " . $_GET['redirect']);
                exit;
            }

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'kasir':
                    header("Location: kasir/dashboard.php");
                    break;
                case 'barista':
                case 'koki':
                    header("Location: dapur/dashboard.php");
                    break;
                case 'pelayan':
                    header("Location: pelayan/dashboard.php");
                    break;
                case 'petugas_stok':
                    header("Location: stok/dashboard.php");
                    break;
                case 'pelanggan':
                    header("Location: pelanggan/dashboard.php");
                    break;
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kafe Ndalem</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-body">
    <div class="login-card">
        <h2 style="margin-bottom: 2rem; color: var(--primary-color);">Kafe Ndalem</h2>
        <h4 style="margin-bottom: 1.5rem; color: #666;">Staff Login</h4>

        <?php if ($error): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message']) && $_GET['message'] == 'registered'): ?>
            <div style="background-color: #e8f5e9; color: #2e7d32; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                Pendaftaran berhasil! Silakan login.
            </div>
        <?php endif; ?>

        <form method="POST" action="?<?= isset($_SERVER['QUERY_STRING']) ? htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Masukan username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Masukan password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Masuk</button>
        </form>

        <p style="margin-top: 1.5rem; font-size: 0.9rem;">
            Belum punya akun? <a href="register.php" style="color: var(--primary-color); font-weight: bold;">Daftar disini</a>
        </p>

        <p style="margin-top: 0.5rem; font-size: 0.9rem;">
            <a href="index.php" style="color: #666;">Kembali ke Beranda</a>
        </p>
    </div>
</body>

</html>