<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$role = get_user_role();
$user_id = $_SESSION['user_id'];

// Cashiers & Owners only
if ($role !== 'cashier' && $role !== 'owner') {
    http_response_code(403);
    die("Access denied: Staff only.");
}

// Cashier validating / taking an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    if ($_POST['action'] === 'validate' && $role === 'cashier') {
        $upd = mysqli_prepare($conn, "UPDATE orders SET cashier_id = ?, status = 'validated' WHERE order_id = ?");
        mysqli_stmt_bind_param($upd, "ii", $user_id, $order_id);
        mysqli_stmt_execute($upd);
    }
    header("Location: view_orders.php");
    exit();
}

// Orders query based on role
if ($role === 'cashier') {
    $q = "SELECT o.*, u.username as cashier_name 
          FROM orders o 
          LEFT JOIN users u ON o.cashier_id = u.id 
          WHERE o.cashier_id = $user_id OR (o.cashier_id IS NULL AND o.status = 'pending')
          ORDER BY o.created_at DESC";
} else {
    // Owner sees everything
    $q = "SELECT o.*, u.username as cashier_name 
          FROM orders o 
          LEFT JOIN users u ON o.cashier_id = u.id 
          ORDER BY o.created_at DESC";
}
$orders = mysqli_query($conn, $q);

// Owner Sales Chart Data (Recent daily revenue)
$chart_labels = [];
$chart_values = [];
if ($role === 'owner') {
    $chart_res = mysqli_query($conn, "SELECT DATE(created_at) as order_date, SUM(total_amount) as daily_total 
                                      FROM orders 
                                      WHERE status = 'validated' 
                                      GROUP BY DATE(created_at) 
                                      ORDER BY order_date ASC LIMIT 7");
    while($row = mysqli_fetch_assoc($chart_res)) {
        $chart_labels[] = $row['order_date'];
        $chart_values[] = (float)$row['daily_total'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Kopipiro</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .main { max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f6f7; }
        .chart-container { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px; }
        .btn-validate { background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-validate:hover { background: #219150; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .pending { background: #f39c12; color: #fff; }
        .validated { background: #2980b9; color: #fff; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main">
    <h2><?= ucfirst($role) ?> Dashboard</h2>

    <?php if ($role === 'owner'): ?>
        <div class="chart-container">
            <h3>Weekly Sales Revenue (Validated Orders)</h3>
            <div style="height: 250px;">
                <canvas id="salesChart"></canvas>
            </div>
            <div style="margin-top: 15px;">
                <a href="excel_generate.php" style="background:#27ae60; color:#fff; text-decoration:none; padding:8px 16px; border-radius:4px; font-size:14px; font-weight:bold; display:inline-block;">📥 Download Excel Report</a>
            </div>
        </div>

        <script>
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Total Revenue (Rp)',
                        data: <?= json_encode($chart_values) ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(41, 128, 185, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    <?php endif; ?>

    <h3><?= $role === 'cashier' ? 'Orders Assigned & Pending' : 'All Shop Orders' ?></h3>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total Amount</th>
            <th>Discount</th>
            <th>Handled By</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while($o = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td><strong>#<?= $o['order_id'] ?></strong></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td>Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
            <td>Rp <?= number_format($o['discount_amount'], 0, ',', '.') ?></td>
            <td><?= $o['cashier_name'] ?? '<em>Unassigned</em>' ?></td>
            <td>
                <span class="badge <?= $o['status'] === 'validated' ? 'validated' : 'pending' ?>">
                    <?= ucfirst($o['status']) ?>
                </span>
            </td>
            <td>
                <?php if ($role === 'cashier' && $o['status'] === 'pending'): ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="validate">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <button type="submit" class="btn-validate">Validate & Accept</button>
                    </form>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>