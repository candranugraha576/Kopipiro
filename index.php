<?php
require_once 'config.php';

$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if ($cat_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $cat_id);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} else {
    $products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id");
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kopipiro - Menu</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .filter-bar a { margin-right: 12px; color: #2980b9; text-decoration: none; font-weight: bold; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .card { width: 220px; border: 1px solid #e0e0e0; background: #fff; border-radius: 8px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; }
        .card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .btn-add { background: #27ae60; color: #fff; border: none; padding: 8px 12px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-add:hover { background: #219150; }
        .badge { background: #eee; padding: 3px 8px; border-radius: 4px; font-size: 12px; color: #555; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main">
    <h2>Our Menu</h2>
    
    <div class="filter-bar">
        <span>Categories:</span>
        <a href="index.php">All</a>
        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="index.php?cat=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
        <?php endwhile; ?>
    </div>

    <div class="grid">
        <?php while($p = mysqli_fetch_assoc($products)): ?>
            <div class="card">
                <div>
                    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" onerror="this.src='https://placehold.co/220x140?text=Kopipiro';">
                    <h3 style="margin: 10px 0 5px;"><?= htmlspecialchars($p['name']) ?></h3>
                    <span class="badge"><?= htmlspecialchars($p['category_name']) ?></span>
                    <p style="font-size: 13px; color: #666; margin: 8px 0;"><?= htmlspecialchars($p['description']) ?></p>
                </div>
                <div>
                    <p style="font-size: 12px; color: #f39c12; margin: 4px 0;">⭐ <?= $p['rating'] ?>/5.0</p>
                    <p style="font-size: 16px; font-weight: bold; margin: 6px 0 12px;">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-add">+ Add to Cart</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>