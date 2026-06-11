<?php
// Script to hash admin password
require_once 'config.php';

$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password = '$hashed_password' WHERE username = 'admin'");

echo "Password admin berhasil di-hash!<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "Hashed: $hashed_password<br>";
echo "<br><a href='Login.php'>Login Sekarang</a>";
