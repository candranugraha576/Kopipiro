<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$user_id =$_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 900px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f4f6f7; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .pending { background: #f39c12; color: #fff; }
        .validated { background: #27ae60; color: #fff; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main">
    <h2>My Order History</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Date & Time</th>
            <th>Total Amount</th>
            <th>Discount Received</th>
            <th>Status</th>
        </tr>
        <?php $has_orders = false; while($o = mysqli_fetch_assoc($orders)):$has_orders = true; ?>
        <tr>
            <td><strong>#<?= $o['order_id'] ?></strong></td>
            <td><?= $o['created_at'] ?></td>
            <td>Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
            <td style="color: green;"><?= $o['discount_amount'] > 0 ? '- Rp ' . number_format($o['discount_amount'], 0, ',', '.') : '-' ?></td>
            <td>
                <span class="badge <?= $o['status'] === 'validated' ? 'validated' : 'pending' ?>">
                    <?= ucfirst($o['status']) ?>
                </span>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php if (!$has_orders): ?>
        <p style="margin-top: 30px; color: #7f8c8d; text-align: center;">You have not placed any orders yet. <a href="index.php">Browse Menu</a></p>
    <?php endif; ?>
</div>
</body>
</html>