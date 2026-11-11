<?php
require_once '../config.php';
require_once '../auth.php';
require_role('owner');

$categories = mysqli_query($conn, "SELECT * FROM categories");
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $rating = (float)$_POST['rating'];
    $description = trim($_POST['description']);

    // Handle Image Upload
    $image_name = 'default.png';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO products (name, category_id, price, rating, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sidsss", $name, $category_id, $price, $rating, $description, $image_name);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit();
    } else {
        $msg = "Error creating product: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .form-box { max-width: 550px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ddd; }
        input, select, textarea { width: 100%; padding: 10px; margin: 6px 0 16px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 12px; border: none; border-radius: 4px; font-weight: bold; width: 100%; cursor: pointer; }
        button:hover { background: #219150; }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="form-box">
    <h2>Add New Product</h2>
    <?php if ($msg): ?><p style="color:red;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" required placeholder="e.g. Vanilla Latte">

        <label>Category:</label>
        <select name="category_id" required>
            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Price (Rp):</label>
        <input type="number" step="500" name="price" required placeholder="18000">

        <label>Rating (1.0 - 5.0):</label>
        <input type="number" step="0.1" min="1.0" max="5.0" name="rating" value="4.8">

        <label>Description:</label>
        <textarea name="description" rows="3" placeholder="Taste notes, ingredients, etc."></textarea>

        <label>Upload Image:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Save Product</button>
    </form>
    <p style="margin-top:15px; text-align:center;"><a href="index.php">Cancel & Return</a></p>
</div>
</body>
</html>
