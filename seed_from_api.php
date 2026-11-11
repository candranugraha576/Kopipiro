<?php
// seed_from_api.php - Consumes a public coffee API using cURL and seeds products
require_once 'config.php';

echo "<h2>Consuming Public Coffee API...</h2>";

// 1. Initialize cURL to fetch coffee products from public endpoint
$apiUrl = 'https://api.sampleapis.com/coffee/hot';
$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    CURLOPT_SSL_VERIFYPEER => false // prevents SSL cert issues on local XAMPP
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    die("<p style='color:red;'>Failed to fetch from public API. HTTP Code: {$httpCode}. Error: {$curlError}</p>");
}

// 2. Decode the incoming JSON
$coffeeList = json_decode($response, true);

if (!is_array($coffeeList)) {
    die("<p style='color:red;'>Invalid JSON response received from API.</p>");
}

// 3. Ensure 'Coffee' category exists and get its ID
$catRes = mysqli_query($conn, "SELECT id FROM categories WHERE LOWER(name) = 'coffee' LIMIT 1");
if ($catRow = mysqli_fetch_assoc($catRes)) {
    $coffeeCatId = $catRow['id'];
} else {
    mysqli_query($conn, "INSERT INTO categories (name) VALUES ('Coffee')");
    $coffeeCatId = mysqli_insert_id($conn);
}

// 4. Prepared statements to prevent duplicates and insert new items
$checkStmt = mysqli_prepare($conn, "SELECT id FROM products WHERE name = ? LIMIT 1");
$insertStmt = mysqli_prepare($conn, "INSERT INTO products (category_id, name, description, price, rating, image) VALUES (?, ?, ?, ?, ?, ?)");

$insertedCount = 0;
$skippedCount = 0;

foreach ($coffeeList as $item) {
    $title = trim($item['title'] ?? '');
    if (empty($title)) continue;

    // Check if product already exists
    mysqli_stmt_bind_param($checkStmt, "s", $title);
    mysqli_stmt_execute($checkStmt);
    $res = mysqli_stmt_get_result($checkStmt);

    if (mysqli_fetch_assoc($res)) {
        $skippedCount++;
        continue;
    }

    // Build description from ingredients & description
    $desc = ($item['description'] ?? '');
    if (!empty($item['ingredients'])) {
        $desc .= " (Ingredients: " . implode(', ', $item['ingredients']) . ")";
    }

    // Realistic price formula (between Rp 18,000 and Rp 32,000)
    $price = 20000 + (($item['id'] ?? 1) % 5) * 3000;
    $rating = 4.5 + (($item['id'] ?? 1) % 5) * 0.1;
    $image = !empty($item['image']) ? $item['image'] : 'default.png';

    mysqli_stmt_bind_param($insertStmt, "isddss", $coffeeCatId, $title, $desc, $price, $rating, $image);
    mysqli_stmt_execute($insertStmt);
    $insertedCount++;
}

echo "<p style='color:green;'>✓ Successfully imported <strong>{$insertedCount}</strong> new coffee items from public API!</p>";
if ($skippedCount > 0) {
    echo "<p style='color:#666;'>ℹ Skipped {$skippedCount} existing items.</p>";
}
echo "<hr><a href='index.php'>Go to Menu Storefront</a>";
?>
