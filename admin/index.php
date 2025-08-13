<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION["admin_user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["admin_user"];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_submissions");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: system-ui; margin: 0; background: #f3f4f6; }
        .header { background: #2563eb; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { padding: 8px 16px; background: #6b7280; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div>
            Welcome, <?php echo htmlspecialchars($user["username"]); ?>
            <a href="?logout=1" class="btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h3>Dashboard Overview</h3>
            <p><strong>Total Form Submissions:</strong> <?php echo $stats["total"]; ?></p>
            <p>Contact form system installed successfully!</p>
            <div style="margin-top: 20px;">
                <a href="../" class="btn" style="background: #2563eb; margin-right: 10px;">View Contact Form</a>
                <a href="#" class="btn" style="background: #10b981;">Manage System</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>