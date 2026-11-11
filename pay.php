<?php
// pay.php - Handles online payment simulation / sandbox gateway
require_once 'config.php';
require_once 'auth.php';

$order_id = (int)($_GET['order_id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    die("Order not found.");
}

$msg = '';

// When user confirms payment in sandbox simulator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $method = $_POST['payment_method'] ?? 'Sandbox Payment';

    // Update order status from 'pending' to 'validated'
    $upd = mysqli_prepare($conn, "UPDATE orders SET status = 'validated' WHERE order_id = ?");
    mysqli_stmt_bind_param($upd, "i", $order_id);
    mysqli_stmt_execute($upd);

    header("Location: confirmation_order.php?order_id=" . $order_id . "&paid=1");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway Sandbox - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .box { max-width: 480px; margin: 40px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .summary { background: #fdf6e2; border: 1px solid #fae29c; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .btn-pay { background: #27ae60; color: white; border: none; padding: 12px; width: 100%; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-pay:hover { background: #219150; }
        select { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="box">
    <h2>💳 Payment Gateway (Sandbox)</h2>
    <p style="color: #666; font-size: 14px;">Simulated Online Payment (Midtrans / Xendit Sandbox)</p>

    <div class="summary">
        <p><strong>Order ID:</strong> #<?= $order['order_id'] ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <h3 style="margin: 5px 0; color: #2c3e50;">Total: Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></h3>
        <p style="margin: 0; font-size: 13px; color: #888;">Current Status: <strong><?= ucfirst($order['status']) ?></strong></p>
    </div>

    <?php if ($order['status'] === 'validated'): ?>
        <p style="color: green; font-weight: bold; text-align: center;">✓ This order has already been paid and validated!</p>
        <a href="index.php" style="display:block; text-align:center; margin-top:15px;">Back to Menu</a>
    <?php else: ?>
        <form method="POST">
            <label><strong>Select Payment Method:</strong></label>
            <select name="payment_method">
                <option value="qris">QRIS (GoPay / OVO / ShopeePay)</option>
                <option value="bca_va">BCA Virtual Account</option>
                <option value="mandiri_va">Mandiri Bill Payment</option>
                <option value="credit_card">Credit Card (Sandbox 4811-xxxx)</option>
            </select>

            <button type="submit" name="process_payment" class="btn-pay">
                Pay Rp <?= number_format($order['total_amount'], 0, ',', '.') ?> Now
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
