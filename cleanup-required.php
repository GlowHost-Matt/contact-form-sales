<?php
/**
 * GlowHost Contact Form - Security Cleanup Required
 * This page enforces mandatory cleanup of installation files before admin access
 */

session_start();

// Define security files that must be removed
$security_files = [
    'detect.php' => 'Detection script (exposes system information)',
    'install.php' => 'Installation script (major security vulnerability)',
    'phpinfo.php' => 'PHP information file (exposes server configuration)'
];

// Check current status of security files
$files_still_exist = [];
$cleanup_verified = true;

foreach ($security_files as $file => $description) {
    if (file_exists($file)) {
        $files_still_exist[$file] = $description;
        $cleanup_verified = false;
    }
}

// Process cleanup requests
$cleanup_result = ['success' => false, 'message' => '', 'deleted' => [], 'failed' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF protection
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $cleanup_result['message'] = 'Security verification failed. Please refresh and try again.';
    } elseif (isset($_POST['auto_cleanup'])) {
        // Attempt automatic cleanup
        foreach ($security_files as $file => $description) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    $cleanup_result['deleted'][] = $file;
                } else {
                    $cleanup_result['failed'][] = $file;
                }
            }
        }

        // Re-check file status after cleanup attempt
        $files_still_exist = [];
        $cleanup_verified = true;
        foreach ($security_files as $file => $description) {
            if (file_exists($file)) {
                $files_still_exist[$file] = $description;
                $cleanup_verified = false;
            }
        }

        if ($cleanup_verified) {
            $cleanup_result['success'] = true;
            $cleanup_result['message'] = 'All security files successfully removed! Redirecting to admin login...';
        } else {
            $cleanup_result['message'] = 'Some files could not be automatically deleted. Please remove manually.';
        }
    } elseif (isset($_POST['verify_cleanup'])) {
        // Manual verification requested
        if ($cleanup_verified) {
            $cleanup_result['success'] = true;
            $cleanup_result['message'] = 'Cleanup verified! Redirecting to admin login...';
        } else {
            $cleanup_result['message'] = 'Files still exist. Please remove them before proceeding.';
        }
    }
}

// If cleanup is verified, remove the security flag and allow admin access
if ($cleanup_verified) {
    if (file_exists('admin/.installation_cleanup_required')) {
        unlink('admin/.installation_cleanup_required');
    }

    // JavaScript redirect after showing success message
    if ($cleanup_result['success']) {
        header('refresh:3;url=admin/login.php');
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Cleanup Required - GlowHost Contact Form</title>
    <style>
        /* Use the same styling as installer for consistency */
        :root {
            --primary: #1e3b97;
            --primary-dark: #061c63;
            --success: #16a34a;
            --error: #dc2626;
            --warning: #d97706;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--gray-50);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header img {
            max-height: 40px;
            margin-bottom: 1rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .security-warning {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .security-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .file-list {
            margin: 1.5rem 0;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .file-item.exists {
            background-color: #fef2f2;
            border-color: #fecaca;
        }

        .file-item.removed {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }

        .file-status {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 2rem;
            text-align: center;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 600;
            color: var(--gray-800);
        }

        .file-description {
            font-size: 0.875rem;
            color: var(--gray-700);
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
            margin: 0.5rem;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        .button-success {
            background-color: var(--success);
        }

        .button-warning {
            background-color: var(--warning);
        }

        .button-error {
            background-color: var(--error);
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin: 1rem 0;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .instructions {
            background: var(--gray-100);
            border-radius: 6px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .command-box {
            background-color: #1e293b;
            color: white;
            padding: 1rem;
            border-radius: 6px;
            font-family: monospace;
            margin: 1rem 0;
            overflow-x: auto;
        }

        .loading {
            text-align: center;
            padding: 2rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://glowhost.com/wp-content/uploads/logo-sans-tagline.png" alt="GlowHost">
        <h1>🔒 Security Checkpoint</h1>
        <p>Installation Complete - Cleanup Required</p>
    </div>

    <div class="container">
        <?php if ($cleanup_verified): ?>
            <!-- Success - Cleanup Complete -->
            <div class="card">
                <div class="alert alert-success">
                    <h2>✅ Security Cleanup Complete!</h2>
                    <p>All installation files have been successfully removed. Admin access is now enabled.</p>
                    <p>Redirecting to admin login in 3 seconds...</p>
                </div>
                <div class="text-center">
                    <a href="admin/login.php" class="button button-success">Access Admin Panel</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Warning - Cleanup Required -->
            <div class="security-warning">
                <div class="security-icon">🛡️</div>
                <h2>SECURITY CHECKPOINT ACTIVATED</h2>
                <p><strong>Admin access is blocked for your protection.</strong></p>
                <p>Installation files must be removed before you can access the admin interface.</p>
            </div>

            <?php if (!empty($cleanup_result['message'])): ?>
                <div class="alert <?php echo $cleanup_result['success'] ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($cleanup_result['message']); ?>
                    <?php if (!empty($cleanup_result['deleted'])): ?>
                        <br><strong>Deleted:</strong> <?php echo implode(', ', $cleanup_result['deleted']); ?>
                    <?php endif; ?>
                    <?php if (!empty($cleanup_result['failed'])): ?>
                        <br><strong>Failed to delete:</strong> <?php echo implode(', ', $cleanup_result['failed']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h3>Files that MUST be removed:</h3>
                <div class="file-list">
                    <?php foreach ($security_files as $file => $description): ?>
                        <div class="file-item <?php echo file_exists($file) ? 'exists' : 'removed'; ?>">
                            <div class="file-status">
                                <?php echo file_exists($file) ? '⚠️' : '✅'; ?>
                            </div>
                            <div class="file-info">
                                <div class="file-name"><?php echo htmlspecialchars($file); ?></div>
                                <div class="file-description"><?php echo htmlspecialchars($description); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3>Cleanup Options:</h3>
                <form method="POST" style="text-align: center;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <button type="submit" name="auto_cleanup" class="button button-warning"
                            onclick="return confirm('Are you sure you want to automatically delete these files? This action cannot be undone.')">
                        🗑️ Automatic Cleanup
                    </button>

                    <button type="submit" name="verify_cleanup" class="button button-success">
                        ✅ Verify Manual Cleanup
                    </button>
                </form>

                <div class="instructions">
                    <h4>Manual Cleanup Instructions:</h4>
                    <p>If you prefer to remove files manually, use one of these methods:</p>

                    <h5>Option 1: File Manager (cPanel/Plesk)</h5>
                    <ol>
                        <li>Log in to your hosting control panel</li>
                        <li>Open File Manager</li>
                        <li>Navigate to your website directory</li>
                        <li>Delete: detect.php, install.php, phpinfo.php</li>
                    </ol>

                    <h5>Option 2: FTP Client</h5>
                    <ol>
                        <li>Connect to your server via FTP</li>
                        <li>Navigate to your website directory</li>
                        <li>Delete the files listed above</li>
                    </ol>

                    <h5>Option 3: SSH/Terminal</h5>
                    <div class="command-box">
rm detect.php install.php phpinfo.php
                    </div>

                    <p><strong>After manual removal, click "Verify Manual Cleanup" above.</strong></p>
                </div>

                <div class="alert alert-warning">
                    <h4>⚠️ Why is this required?</h4>
                    <ul>
                        <li><strong>detect.php:</strong> Exposes system information that could help attackers</li>
                        <li><strong>install.php:</strong> Major security vulnerability - could allow site takeover</li>
                        <li><strong>phpinfo.php:</strong> Reveals detailed server configuration to potential attackers</li>
                    </ul>
                    <p><strong>Leaving these files accessible is a serious security risk!</strong></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Support Information -->
        <div class="card">
            <h3>Need Help?</h3>
            <p>If you're having trouble with the cleanup process:</p>
            <p><strong>GlowHost Support</strong><br>
            Toll Free: <a href="tel:+18882934678">1 (888) 293-HOST</a><br>
            Available 24/7/365</p>
        </div>
    </div>

    <?php if ($cleanup_verified && $cleanup_result['success']): ?>
    <script>
        // Auto-redirect after success
        setTimeout(function() {
            window.location.href = 'admin/login.php';
        }, 3000);
    </script>
    <?php endif; ?>
</body>
</html>
