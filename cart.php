<?php
require_once 'config.php';
require_once 'auth.php';

$user_id =$_SESSION['user_id'] ?? null;
$session_id = session_id();

// Handle Actions: Add, Remove, or Clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action =$_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = (int)$_POST['product_id'];

        if ($user_id) {
            $stmt = mysqli_prepare($conn, "SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $user_id,$product_id);
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, quantity FROM carts WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
            mysqli_stmt_bind_param($stmt, "si", $session_id,$product_id);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($res)) {
            $new_qty =$row['quantity'] + 1;
            $upd = mysqli_prepare($conn, "UPDATE carts SET quantity = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ii", $new_qty,$row['id']);
            mysqli_stmt_execute($upd);
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO carts (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, 1)");
            mysqli_stmt_bind_param($ins, "isi", $user_id, $session_id,$product_id);
            mysqli_stmt_execute($ins);
        }
        header("Location: cart.php");
        exit();
    }

    if ($action === 'remove') {
        $cart_id = (int)$_POST['cart_id'];
        $del = mysqli_prepare($conn, "DELETE FROM carts WHERE id = ?");
        mysqli_stmt_bind_param($del, "i", $cart_id);
        mysqli_stmt_execute($del);
        header("Location: cart.php");
        exit();
    }
}

// Fetch Cart Data
if ($user_id) {
    $q = mysqli_prepare($conn, "SELECT ct.id as cart_id, ct.quantity, p.name, p.price, c.name as category_name 
                               FROM carts ct 
                               JOIN products p ON ct.product_id = p.id 
                               JOIN categories c ON p.category_id = c.id 
                               WHERE ct.user_id = ?");
    mysqli_stmt_bind_param($q, "i", $user_id);
} else {
    $q = mysqli_prepare($conn, "SELECT ct.id as cart_id, ct.quantity, p.name, p.price, c.name as category_name 
                               FROM carts ct 
                               JOIN products p ON ct.product_id = p.id 
                               JOIN categories c ON p.category_id = c.id 
                               WHERE ct.session_id = ? AND ct.user_id IS NULL");
    mysqli_stmt_bind_param($q, "s", $session_id);
}
mysqli_stmt_execute($q);
$cart_items = mysqli_stmt_get_result($q);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cart - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f4f6f7; }
        .btn-remove { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-checkout { background: #27ae60; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-checkout:hover { background: #219150; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main">
    <h2>Your Cart</h2>

    <table>
        <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
        <?php 
        $has_items = false; 
        $cart_total = 0;
        while($item = mysqli_fetch_assoc($cart_items)):$has_items = true; 
            $sub = $item['price'] *$item['quantity'];
            $cart_total +=$sub;
        ?>
        <tr>
            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
            <td><?= htmlspecialchars($item['category_name']) ?></td>
            <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>Rp <?= number_format($sub, 0, ',', '.') ?></td>
            <td>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <button type="submit" class="btn-remove">Remove</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div style="margin-top: 24px; text-align: right;">
        <?php if ($has_items): ?>
            <h3>Subtotal: Rp <?= number_format($cart_total, 0, ',', '.') ?></h3>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>
        <?php else: ?>
            <p style="text-align: center; color: #7f8c8d; margin-top: 30px;">Your cart is empty. <a href="index.php">Go explore the menu</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>