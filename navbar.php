<?php
require_once __DIR__ . '/auth.php';
$role = get_user_role();

// Check if we are inside the /admin folder to adjust link prefixes
$is_admin_folder = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $is_admin_folder ? '../' : '';
$admin_path = $is_admin_folder ? '' : 'admin/';
?>
<nav style="display:flex; align-items:center; gap:16px; padding:14px 20px; background:#2c3e50; color:#fff; font-family:sans-serif; margin-bottom:24px;">
    <strong style="font-size:18px; color:#e67e22;">☕ Kopipiro</strong>
    <a href="<?= $base_path ?>index.php" style="color:#ecf0f1; text-decoration:none;">Menu</a>
    <a href="<?= $base_path ?>cart.php" style="color:#ecf0f1; text-decoration:none;">Cart</a>

    <?php if ($role === 'customer'): ?>
        <a href="<?= $base_path ?>order_history.php" style="color:#ecf0f1; text-decoration:none;">My Orders</a>
    <?php endif; ?>

    <?php if ($role === 'cashier'): ?>
        <a href="<?= $base_path ?>view_orders.php" style="color:#ecf0f1; text-decoration:none;">Manage Orders</a>
    <?php endif; ?>

    <?php if ($role === 'owner'): ?>
        <a href="<?= $admin_path ?>index.php" style="color:#ecf0f1; text-decoration:none;">Product CRUD</a>
        <a href="<?= $base_path ?>view_orders.php" style="color:#ecf0f1; text-decoration:none;">Orders & Analytics</a>
        <a href="<?= $base_path ?>excel_generate.php" style="color:#ecf0f1; text-decoration:none;">Export Excel</a>
    <?php endif; ?>

    <div style="margin-left:auto; display:flex; align-items:center; gap:12px;">
        <?php if (is_logged_in()): ?>
            <span style="font-size:14px;">Logged as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= htmlspecialchars($role) ?>)</span>
            <a href="<?= $base_path ?>logout.php" style="color:#e74c3c; text-decoration:none; font-weight:bold;">Logout</a>
        <?php else: ?>
            <a href="<?= $base_path ?>login.php" style="color:#ecf0f1; text-decoration:none;">Login</a>
            <span style="color:#7f8c8d;">|</span>
            <a href="<?= $base_path ?>register.php" style="color:#ecf0f1; text-decoration:none;">Register</a>
        <?php endif; ?>
    </div>
</nav>