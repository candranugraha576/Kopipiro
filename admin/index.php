<?php
require_once '../config.php';
require_once '../auth.php';
require_role('owner');

$products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Products</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f6f7; }
        .btn-add { background: #27ae60; color: white; padding: 10px 18px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block; }
        .btn-add:hover { background: #219150; }
        .action-link { margin-right: 8px; text-decoration: none; font-weight: bold; }
        .edit { color: #2980b9; }
        .delete { color: #e74c3c; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Product Catalog Management</h2>
        <a href="create.php" class="btn-add">+ Add New Product</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Rating</th>
            <th>Actions</th>
        </tr>
        <?php while($p = mysqli_fetch_assoc($products)): ?>
        <tr>
            <td>#<?= $p['id'] ?></td>
            <td>
                <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="thumb" onerror="this.src='https://placehold.co/50x50?text=No+Img';">
            </td>
            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
            <td>⭐ <?= $p['rating'] ?></td>
            <td>
                <a href="edit.php?id=<?= $p['id'] ?>" class="action-link edit">Edit</a>
                <a href="delete.php?id=<?= $p['id'] ?>" class="action-link delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
