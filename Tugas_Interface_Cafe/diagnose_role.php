<?php
require_once 'config.php';

echo "=== Investigating Users Table Structure ===\n\n";

// Show table structure
$structure = $conn->query("DESCRIBE users");
echo "Table 'users' structure:\n";
while ($col = $structure->fetch_assoc()) {
    echo "- {$col['Field']}: {$col['Type']} | Null: {$col['Null']} | Default: {$col['Default']}\n";
}

echo "\n=== Current Users Data ===\n";
$users = $conn->query("SELECT id, username, role, LENGTH(role) as role_length FROM users");
while ($user = $users->fetch_assoc()) {
    echo "ID: {$user['id']} | Username: '{$user['username']}' | Role: '{$user['role']}' | Length: {$user['role_length']}\n";
}

echo "\n=== Attempting Direct Update ===\n";
// Try direct update with debugging
$test_update = "UPDATE users SET role = 'pelanggan' WHERE username = 'pelanggan'";
echo "SQL: $test_update\n";
if ($conn->query($test_update)) {
    echo "Query executed successfully. Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// Check again
echo "\n=== Verification ===\n";
$check = $conn->query("SELECT username, role, LENGTH(role) as len FROM users WHERE username = 'pelanggan'");
$row = $check->fetch_assoc();
echo "Username: '{$row['username']}' | Role: '{$row['role']}' | Length: {$row['len']}\n";
