<?php
require_once 'config.php';

// Accounts to seed
$users_to_seed = [
    [
        'role_id'  => 1, // Owner
        'username' => 'owner_kopipiro',
        'email'    => 'owner@kopipiro.com',
        'password' => 'password123'
    ],
    [
        'role_id'  => 2, // Cashier
        'username' => 'cashier_budi',
        'email'    => 'budi@kopipiro.com',
        'password' => 'password123'
    ],
    [
        'role_id'  => 3, // Customer
        'username' => 'customer_demo',
        'email'    => 'customer@gmail.com',
        'password' => 'password123'
    ]
];

echo "<h3>Seeding Accounts into kopipiro_db...</h3>";

foreach ($users_to_seed as $u) {
    // Generate fresh bcrypt hash
    $hashed_pass = password_hash($u['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (role_id, username, email, password) 
                                  VALUES (?, ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE password = VALUES(password), role_id = VALUES(role_id)");
    mysqli_stmt_bind_param($stmt, "isss", $u['role_id'], $u['username'], $u['email'], $hashed_pass);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green;'>✓ Added/Updated: <strong>{$u['username']}</strong> ({$u['email']}) - Password: <code>{$u['password']}</code></p>";
    } else {
        echo "<p style='color:red;'>✗ Failed for {$u['username']}: " . mysqli_error($conn) . "</p>";
    }
}

echo "<hr><p>Seeding complete. <a href='login.php'>Go to Login</a></p>";
?>