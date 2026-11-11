<?php
require_once 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Customer role_id is 3
    $stmt = mysqli_prepare($conn, "INSERT INTO users (role_id, username, email, password) VALUES (3, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $email,$password);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php?registered=1");
        exit();
    } else {
        $msg = "Registration failed. Username or email may already be in use.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Kopipiro</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fafafa; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #e67e22; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #d35400; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h2>Create Account</h2>
    <?php if ($msg): ?><p style="color:red;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Sign Up</button>
    </form>
    <p style="font-size:14px; text-align:center; margin-top:15px;">
        Already have an account? <a href="login.php">Log In</a>
    </p>
</div>
</body>
</html>