<?php
/**
 * GlowHost Contact Form - Admin Dashboard
 * Includes mandatory security cleanup enforcement and secure phpinfo
 */

// CRITICAL: Check security cleanup before allowing any admin access
require_once 'security-check.php';

// If we reach here, security cleanup is complete and admin access is allowed
session_start();

// Simple auth check (replace with actual admin authentication)
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle phpinfo request (secure inline display)
$show_phpinfo = false;
$phpinfo_content = '';

if (isset($_GET['action']) && $_GET['action'] === 'phpinfo' && isset($_SESSION['admin_logged_in'])) {
    $show_phpinfo = true;

    // Capture phpinfo output securely
    ob_start();
    phpinfo();
    $phpinfo_content = ob_get_clean();

    // Clean up the output for better display
    $phpinfo_content = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo_content);
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GlowHost Contact Form</title>
    <style>
        :root {
            --primary: #1e3b97;
            --primary-dark: #061c63;
            --success: #16a34a;
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
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
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
            margin: 0.25rem;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        .button-success {
            background-color: var(--success);
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
    </style>
</head>
<body>
    <div class="header">
        <h1>🛡️ Admin Dashboard</h1>
        <div class="nav">
            <a href="?">Dashboard</a>
            <a href="?action=phpinfo">System Info</a>
            <a href="../">View Site</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="security-status">
            <strong>✅ Security Status: Protected</strong><br>
            All installation files have been successfully removed. Admin access is secure.
        </div>

        <?php if ($show_phpinfo): ?>
            <div class="card">
                <h2>PHP System Information</h2>
                <p>This information is displayed securely within the admin interface. No files are created.</p>
                <a href="?" class="button">← Back to Dashboard</a>

                <div class="phpinfo-container">
                    <?php echo $phpinfo_content; ?>
                </div>
            </div>
        <?php else: ?>
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
                <p><strong>Admin User:</strong> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?></p>
                <p><strong>Security Status:</strong> ✅ Installation files removed</p>
                <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>

                <h3>Security Features</h3>
                <ul>
                    <li>✅ Installation files automatically removed</li>
                    <li>✅ Admin access protection enabled</li>
                    <li>✅ PHP info displayed securely (no file creation)</li>
                    <li>✅ Session-based authentication</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
