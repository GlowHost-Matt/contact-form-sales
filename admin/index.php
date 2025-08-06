<?php
/**
 * GlowHost Contact Form - Admin Interface (Single Entry Point)
 * Handles login, dashboard, and all admin functionality
 */

// CRITICAL: Check security cleanup before allowing any admin access
require_once 'security-check.php';

// If we reach here, security cleanup is complete and admin access is allowed
session_start();

// Handle different actions
$action = $_GET['action'] ?? '';
$view = 'login'; // Default view
$message = '';

// Handle logout
if ($action === 'logout') {
    session_destroy();
    session_start();
    $message = 'Successfully logged out.';
    $view = 'login';
}

// Handle login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple demo authentication (replace with actual admin system)
    if ($username === 'admin' && $password === 'demo') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        $view = 'dashboard';
    } else {
        $message = 'Invalid credentials. Use admin/demo for this demo.';
        $view = 'login';
    }
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $view = 'dashboard';

    // Handle phpinfo request (secure inline display)
    if ($action === 'phpinfo') {
        $view = 'phpinfo';
    }
}

// Handle phpinfo content generation
$phpinfo_content = '';
if ($view === 'phpinfo') {
    ob_start();
    phpinfo();
    $phpinfo_content = ob_get_clean();
    // Clean up the output for better display
    $phpinfo_content = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo_content);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view === 'login' ? 'Admin Login' : 'Admin Dashboard'; ?> - GlowHost Contact Form</title>
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
        }

        /* Login Styles */
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            max-height: 40px;
            margin-bottom: 1rem;
        }

        /* Dashboard Styles */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.2s;
            font-size: 0.9rem;
        }

        .nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }

        /* Common Styles */
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
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.25rem;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        .button-success {
            background-color: var(--success);
        }

        .button-full {
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray-700);
            font-size: 0.875rem;
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

        .security-status {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .phpinfo-container {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* Style phpinfo output */
        .phpinfo-container table {
            width: 100% !important;
            border-collapse: collapse;
        }

        .phpinfo-container td, .phpinfo-container th {
            padding: 0.5rem !important;
            border: 1px solid #e5e7eb !important;
            font-size: 0.875rem !important;
        }

        .phpinfo-container th {
            background-color: var(--gray-100) !important;
            font-weight: 600 !important;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php if ($view === 'login'): ?>
        <!-- Login View -->
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
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

                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'Success') !== false ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" name="login" class="button button-full">Login</button>
                </form>

                <div class="security-status" style="margin-top: 1rem;">
                    ✅ <strong>Security Verified</strong><br>
                    Installation files have been successfully removed.
                </div>
            </div>
        </div>

    <?php elseif ($view === 'phpinfo'): ?>
        <!-- PHP Info View -->
        <div class="dashboard-container">
            <div class="header">
                <h1>🔧 PHP System Information</h1>
                <div class="nav">
                    <a href="">← Back to Dashboard</a>
                    <span style="opacity: 0.8;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="?action=logout">Logout</a>
                </div>
            </div>

            <div class="content">
                <div class="card">
                    <h2>PHP System Information</h2>
                    <p>This information is displayed securely within the admin interface. No files are created.</p>
                    <a href="" class="button">← Back to Dashboard</a>

                    <div class="phpinfo-container">
                        <?php echo $phpinfo_content; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Dashboard View -->
        <div class="dashboard-container">
            <div class="header">
                <h1>🛡️ Admin Dashboard</h1>
                <div class="nav">
                    <a href="">Dashboard</a>
                    <a href="?action=phpinfo">System Info</a>
                    <a href="../">View Site</a>
                    <span style="opacity: 0.8;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="?action=logout">Logout</a>
                </div>
            </div>

            <div class="content">
                <div class="security-status">
                    <strong>✅ Security Status: Protected</strong><br>
                    All installation files have been successfully removed. Admin access is secure.
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Total Submissions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">New Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">✅</div>
                        <div class="stat-label">System Status</div>
                    </div>
                </div>

                <div class="card">
                    <h2>Quick Actions</h2>
                    <p>Manage your contact form system:</p>

                    <a href="#" class="button">📧 View Submissions</a>
                    <a href="?action=phpinfo" class="button">🔧 System Information</a>
                    <a href="#" class="button">⚙️ Settings</a>
                    <a href="../" class="button button-success">👁️ View Contact Form</a>
                </div>

                <div class="card">
                    <h2>System Information</h2>
                    <p><strong>Contact Form Status:</strong> ✅ Active and Ready</p>
                    <p><strong>Admin User:</strong> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                    <p><strong>Security Status:</strong> ✅ Installation files removed</p>
                    <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                    <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['login_time'] ?? time()); ?></p>

                    <h3>Security Features</h3>
                    <ul>
                        <li>✅ Installation files automatically removed</li>
                        <li>✅ Admin access protection enabled</li>
                        <li>✅ PHP info displayed securely (no file creation)</li>
                        <li>✅ Session-based authentication</li>
                        <li>✅ Single entry point admin interface</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
