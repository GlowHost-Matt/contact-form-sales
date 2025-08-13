<?php
require_once "../config.php";
session_start();

if ($_POST) {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = \"active\"");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["admin_user"] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid credentials";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: system-ui; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; }
        .form-input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .btn { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
        .error { background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-input" name="username" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>