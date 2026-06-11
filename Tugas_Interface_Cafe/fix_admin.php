<?php

/**
 * Fix Admin User - Create admin dengan password hash yang benar
 */
require_once 'config.php';

// Hash password 'admin123'
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Delete existing admin
$conn->query("DELETE FROM users WHERE username = 'admin'");

// Insert admin dengan password yang di-hash
$stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
$username = 'admin';
$full_name = 'Administrator';
$role = 'admin';

$stmt->bind_param("ssss", $username, $hashed, $full_name, $role);

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>✅ Admin user berhasil dibuat!</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Hash:</strong> " . htmlspecialchars($hashed) . "</p>";
    echo "<br><a href='Login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Sekarang</a>";

    // Verify the hash works
    echo "<br><br><p><strong>Verifikasi Hash:</strong> ";
    if (password_verify($password, $hashed)) {
        echo "<span style='color: green;'>✅ Hash verified successfully!</span>";
    } else {
        echo "<span style='color: red;'>❌ Hash verification failed!</span>";
    }
    echo "</p>";
} else {
    echo "<h2 style='color: red;'>❌ Gagal membuat admin user!</h2>";
    echo "<p>Error: " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
