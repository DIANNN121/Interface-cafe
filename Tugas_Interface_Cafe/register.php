<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = 'pelanggan';

    // 1. Check if username exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $error = "Username '$username' sudah terdaftar. Silakan pilih yang lain.";
    } else {
        // 2. Create User
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$hashed_password', '$full_name', '$role')";

        if ($conn->query($sql)) {
            // Redirect to Login with success message
            header("Location: Login.php?message=registered");
            exit;
        } else {
            $error = "Gagal mendaftar: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Kafe Nimet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            max-width: 400px;
        }
    </style>
</head>

<body class="login-body">
    <div class="login-card">
        <h2 style="margin-bottom: 2rem; color: var(--primary-color);">Kafe Nimet</h2>
        <h4 style="margin-bottom: 1.5rem; color: #666;">Daftar Pelanggan Baru</h4>

        <?php if ($error): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="Nama Anda">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Buat username untuk login">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Buat password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar Sekarang</button>
        </form>

        <p style="margin-top: 1.5rem; font-size: 0.9rem;">
            Sudah punya akun? <a href="Login.php" style="color: var(--primary-color); font-weight: bold;">Login disini</a>
        </p>
        <p style="margin-top: 0.5rem; font-size: 0.9rem;">
            <a href="index.php" style="color: #666;">Kembali ke Beranda</a>
        </p>
    </div>
</body>

</html>