<?php
require_once 'config.php';
require_once 'auth.php';
require_role('owner');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=kopipiro_sales_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Set Excel column headers
fputcsv($output, ['Order ID', 'Date & Time', 'Customer Name', 'Cashier', 'Discount (Rp)', 'Total Paid (Rp)', 'Status']);

$sql = "SELECT o.order_id, o.created_at, o.customer_name, u.username as cashier_name, o.discount_amount, o.total_amount, o.status 
        FROM orders o 
        LEFT JOIN users u ON o.cashier_id = u.id 
        ORDER BY o.created_at DESC";

$res = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($output, [
        $row['order_id'],
        $row['created_at'],
        $row['customer_name'],
        $row['cashier_name'] ?? 'Unassigned',
        $row['discount_amount'],
        $row['total_amount'],
        ucfirst($row['status'])
    ]);
}

fclose($output);
exit();