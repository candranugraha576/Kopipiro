<?php
require_once '../config.php';
require_once '../auth.php';
require_role('owner');

$id = (int)($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($res);

if (!$product) {
    die("Product not found.");
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $rating = (float)$_POST['rating'];
    $description = trim($_POST['description']);
    $image_name = $product['image'];

    // Handle Image Replacement
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

    $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, category_id=?, price=?, rating=?, description=?, image=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sidsssi", $name, $category_id, $price, $rating, $description, $image_name, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit();
    } else {
        $msg = "Error updating product: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .form-box { max-width: 550px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ddd; }
        input, select, textarea { width: 100%; padding: 10px; margin: 6px 0 16px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #2980b9; color: white; padding: 12px; border: none; border-radius: 4px; font-weight: bold; width: 100%; cursor: pointer; }
        button:hover { background: #1f618d; }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="form-box">
    <h2>Edit Product #<?= $product['id'] ?></h2>
    <?php if ($msg): ?><p style="color:red;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Category:</label>
        <select name="category_id" required>
            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Price (Rp):</label>
        <input type="number" step="500" name="price" value="<?= $product['price'] ?>" required>

        <label>Rating (1.0 - 5.0):</label>
        <input type="number" step="0.1" min="1.0" max="5.0" name="rating" value="<?= $product['rating'] ?>">

        <label>Description:</label>
        <textarea name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>

        <label>Current Image:</label><br>
        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" width="60" height="60" style="object-fit:cover; margin-bottom:10px; border-radius:4px;" onerror="this.src='https://placehold.co/60x60?text=No+Img';"><br>

        <label>Replace Image (Optional):</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Update Product</button>
    </form>
    <p style="margin-top:15px; text-align:center;"><a href="index.php">Cancel</a></p>
</div>
</body>
</html>
