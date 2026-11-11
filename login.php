<?php
require_once 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_name'] = $user['role_name'];

        // Sync existing guest session cart with this registered user account
        $sid = session_id();
        $sync = mysqli_prepare($conn, "UPDATE carts SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
        mysqli_stmt_bind_param($sync, "is", $user['id'], $sid);
        mysqli_stmt_execute($sync);

        // Redirect based on role
        if ($user['role_name'] === 'owner') {
            header("Location: view_orders.php");
        } elseif ($user['role_name'] === 'cashier') {
            header("Location: view_orders.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $msg = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #2c3e50; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1a252f; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h2>Sign In</h2>
    <?php if (isset($_GET['registered'])): ?><p style="color:green;">Registration successful! Please login below.</p><?php endif; ?>
    <?php if ($msg): ?><p style="color:red;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Log In</button>
    </form>
    <p style="font-size:14px; text-align:center; margin-top:15px;">
        Don't have an account? <a href="register.php">Register now</a>
    </p>
</div>
</body>
</html>