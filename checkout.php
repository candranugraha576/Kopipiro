<?php
require_once 'config.php';
require_once 'auth.php';

$user_id =$_SESSION['user_id'] ?? null;
$session_id = session_id();$is_registered_customer = is_logged_in() && get_user_role() === 'customer';

// Fetch user's cart items
if ($user_id) {
    $stmt = mysqli_prepare($conn, "SELECT ct.product_id, ct.quantity, p.name, p.price, c.name as category_name 
                                  FROM carts ct 
                                  JOIN products p ON ct.product_id = p.id 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE ct.user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
} else {
    $stmt = mysqli_prepare($conn, "SELECT ct.product_id, ct.quantity, p.name, p.price, c.name as category_name 
                                  FROM carts ct 
                                  JOIN products p ON ct.product_id = p.id 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE ct.session_id = ? AND ct.user_id IS NULL");
    mysqli_stmt_bind_param($stmt, "s", $session_id);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];$total_gross = 0;
$total_discount = 0;
$total_net = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $line_gross =$row['price'] * $row['quantity'];$line_disc = 0;

    // 10% discount applies ONLY to Coffee AND registered/logged-in customers
    if ($is_registered_customer && strtolower($row['category_name']) === 'coffee') {
        $line_disc =$line_gross * 0.10;
    }

    $line_net = $line_gross -$line_disc;

    $total_gross +=$line_gross;
    $total_discount +=$line_disc;
    $total_net +=$line_net;

    $row['line_gross'] =$line_gross;
    $row['discount'] =$line_disc;
    $row['line_net'] =$line_net;
    $items[] =$row;
}

if (empty($items)) {
    header("Location: cart.php");
    exit();
}

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name =$is_registered_customer ? $_SESSION['username'] : trim($_POST['guest_name']);
    if (empty($customer_name))$customer_name = 'Guest Buyer';

    // 1. Insert into orders table
    $order_sql = "INSERT INTO orders (user_id, customer_name, total_amount, discount_amount, status) VALUES (?, ?, ?, ?, 'pending')";
    $ins = mysqli_prepare($conn,$order_sql);
    mysqli_stmt_bind_param($ins, "isdd", $user_id,$customer_name, $total_net,$total_discount);
    mysqli_stmt_execute($ins);
    $order_id = mysqli_insert_id($conn);

    // 2. Insert into order_details table
    $item_sql = "INSERT INTO order_details (order_id, product_id, quantity, price, discount, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
    $detail_stmt = mysqli_prepare($conn,$item_sql);
    foreach ($items as$it) {
        mysqli_stmt_bind_param($detail_stmt, "iiiddd", $order_id,$it['product_id'], $it['quantity'],$it['price'], $it['discount'],$it['line_net']);
        mysqli_stmt_execute($detail_stmt);
    }

    // 3. Clear Cart
    if ($user_id) {
        $clear = mysqli_prepare($conn, "DELETE FROM carts WHERE user_id = ?");
        mysqli_stmt_bind_param($clear, "i", $user_id);
    } else {
        $clear = mysqli_prepare($conn, "DELETE FROM carts WHERE session_id = ?");
        mysqli_stmt_bind_param($clear, "s", $session_id);
    }
    mysqli_stmt_execute($clear);

    header("Location: confirmation_order.php?order_id=" . $order_id);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f6f7; }
        .box { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-top: 20px; }
        .btn-order { background: #e67e22; color: #fff; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-order:hover { background: #d35400; }
        input[type="text"] { width: 100%; padding: 10px; margin: 8px 0 16px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .discount-text { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main">
    <h2>Checkout Summary</h2>

    <table>
        <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Discount</th>
            <th>Line Total</th>
        </tr>
        <?php foreach ($items as$it): ?>
        <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td><?= htmlspecialchars($it['category_name']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td>Rp <?= number_format($it['price'], 0, ',', '.') ?></td>
            <td class="discount-text"><?= $it['discount'] > 0 ? '- Rp ' . number_format($it['discount'], 0, ',', '.') . ' (10%)' : '-' ?></td>
            <td>Rp <?= number_format($it['line_net'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="box">
        <p style="font-size: 16px; margin: 5px 0;"><strong>Subtotal:</strong> Rp <?= number_format($total_gross, 0, ',', '.') ?></p>
        <p style="font-size: 16px; margin: 5px 0;" class="discount-text">
            <strong>Total Discount:</strong> - Rp <?= number_format($total_discount, 0, ',', '.') ?>
            <?php if (!$is_registered_customer): ?>
                <span style="font-size:12px; color:#7f8c8d; font-weight:normal;">(Guests do not receive the 10% member discount on coffee)</span>
            <?php endif; ?>
        </p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
        <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Total to Pay: Rp <?= number_format($total_net, 0, ',', '.') ?></h2>

        <form method="POST">
            <?php if (!$is_registered_customer): ?>
                <label><strong>Name for Order Pickup:</strong></label>
                <input type="text" name="guest_name" required placeholder="e.g. Budi Santoso">
            <?php endif; ?>
            <button type="submit" name="place_order" class="btn-order">Place Order</button>
        </form>
    </div>
</div>
</body>
</html>