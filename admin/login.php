<?php
/**
 * GlowHost Contact Form - Admin Login
 * Includes mandatory security cleanup enforcement
 */

// CRITICAL: Check security cleanup before allowing any admin access
require_once 'security-check.php';

// If we reach here, security cleanup is complete and admin access is allowed
session_start();

// Simple login demo (this would be replaced with actual admin system)
$login_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is just a demo - actual implementation would have proper authentication
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Demo credentials (replace with actual admin authentication)
    if ($username === 'admin' && $password === 'demo') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $login_message = 'Invalid credentials. This is a demo - use admin/demo.';
    }
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GlowHost Contact Form</title>
    <style>
        :root {
            --primary: #1e3b97;
            --primary-dark: #061c63;
            --success: #16a34a;
            --error: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--gray-50);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header img {
            max-height: 40px;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .button {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .security-note {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <img src="https://glowhost.com/wp-content/uploads/logo-sans-tagline.png" alt="GlowHost">
            <h2>Admin Login</h2>
            <p>GlowHost Contact Form System</p>
        </div>

        <?php if (isset($_SESSION['admin_access_blocked'])): ?>
            <div class="alert alert-error">
                <?php
                echo htmlspecialchars($_SESSION['admin_access_blocked']);
                unset($_SESSION['admin_access_blocked']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($login_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($login_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="button">Login</button>
        </form>

        <div class="security-note">
            ✅ <strong>Security Verified</strong><br>
            Installation files have been successfully removed.
        </div>
    </div>
</body>
</html>
