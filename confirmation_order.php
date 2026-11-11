<?php
require_once 'config.php';
$order_id = (int)($_GET['order_id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .box { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .badge { background: #f39c12; color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: bold; }
        .badge.paid { background: #27ae60; }
        .btn { display: inline-block; margin-top: 15px; background: #2c3e50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
        .btn-pay { background: #27ae60; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="box">
    <?php if (isset($_GET['paid'])): ?>
        <h2 style="color: #27ae60;">Payment Successful!</h2>
        <p>Your transaction has been validated via payment sandbox.</p>
    <?php else: ?>
        <h2 style="color: #27ae60;">Order Placed Successfully!</h2>
        <p>Thank you for ordering with Kopipiro.</p>
    <?php endif; ?>

    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p><strong>Order ID:</strong> #<?= $order['order_id'] ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
    <p><strong>Total Amount:</strong> Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></p>
    <p><strong>Status:</strong> 
        <span class="badge <?= $order['status'] === 'validated' ? 'paid' : '' ?>">
            <?= ucfirst($order['status']) ?>
        </span>
    </p>

    <?php if ($order['status'] === 'pending'): ?>
        <div style="margin-top: 20px;">
            <a href="pay.php?order_id=<?= $order['order_id'] ?>" class="btn btn-pay">💳 Pay Online (Sandbox)</a>
            <p style="color: #888; font-size: 13px; margin-top: 8px;">Or pay cash at the counter directly to the cashier.</p>
        </div>
    <?php else: ?>
        <a href="index.php" class="btn">Back to Menu</a>
    <?php endif; ?>
</div>
</body>
</html>
