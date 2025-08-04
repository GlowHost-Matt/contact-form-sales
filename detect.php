<?php
/**
 * GlowHost Contact Form System - PHP Version Detector
 * Version: 5.0 - Smart Detection Approach
 *
 * This script deploys phpinfo.php, scrapes it for version info,
 * and guides users based on their PHP version compatibility.
 */

// Configuration
define('MIN_PHP_VERSION', '7.4.0');
define('RECOMMENDED_PHP_VERSION', '8.1.0');
define('PHPINFO_FILE', 'phpinfo.php');
define('INSTALLER_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php');
define('PHPINFO_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/phpinfo.php');

// Get current script URL for phpinfo detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$base_url = rtrim($protocol . $host . $path, '/');

/**
 * Deploy phpinfo.php if it doesn't exist
 */
function deployPhpInfo() {
    if (!file_exists(PHPINFO_FILE)) {
        $phpinfo_content = file_get_contents(PHPINFO_URL);
        if ($phpinfo_content !== false) {
            file_put_contents(PHPINFO_FILE, $phpinfo_content);
            return true;
        }
        return false;
    }
    return true;
}

/**
 * Scrape PHP version from phpinfo output
 */
function detectPhpVersion($base_url) {
    $phpinfo_url = $base_url . '/' . PHPINFO_FILE;

    // Use cURL if available, otherwise file_get_contents
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $phpinfo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        $content = @file_get_contents($phpinfo_url);
    }

    if ($content === false) {
        return false;
    }

    // Look for "PHP Version X.X.X" in the first few lines
    if (preg_match('/PHP Version ([0-9]+\.[0-9]+\.[0-9]+[^\s]*)/i', $content, $matches)) {
        return $matches[1];
    }

    return false;
}

/**
 * Download modern installer
 */
function downloadInstaller() {
    $installer_content = file_get_contents(INSTALLER_URL);
    if ($installer_content !== false) {
        file_put_contents('install.php', $installer_content);
        return true;
    }
    return false;
}

// Main execution
$step = isset($_GET['step']) ? $_GET['step'] : 'detect';

// Deploy phpinfo.php first
if (!deployPhpInfo()) {
    die('Error: Could not deploy phpinfo.php diagnostic file.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - Environment Detection</title>
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --text: #1f2937;
            --bg: #f9fafb;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: var(--primary);
            color: var(--white);
            padding: 32px;
            text-align: center;
        }

        .content {
            padding: 32px;
        }

        .detection-box {
            background: var(--bg);
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }

        .version-info {
            background: #eff6ff;
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .upgrade-needed {
            background: #fef2f2;
            border: 2px solid var(--error);
            border-radius: 8px;
            padding: 24px;
            margin: 20px 0;
        }

        .upgrade-steps {
            background: #f0fdf4;
            border: 2px solid var(--success);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }

        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-success { background: var(--success); color: var(--white); }
        .btn-warning { background: var(--warning); color: var(--white); }

        .loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Smart PHP Environment Detection v5.0</p>
        </div>

        <div class="content">
            <?php if ($step === 'detect'): ?>
                <h2>🔍 Detecting Your PHP Environment</h2>

                <div class="detection-box">
                    <div class="loading">
                        <div class="spinner"></div>
                        <span>Analyzing your server's PHP configuration...</span>
                    </div>
                </div>

                <script>
                setTimeout(function() {
                    // Perform detection via AJAX or redirect
                    window.location.href = '?step=analyze';
                }, 2000);
                </script>

            <?php elseif ($step === 'analyze'): ?>
                <?php
                $detected_version = detectPhpVersion($base_url);

                if ($detected_version === false):
                ?>
                    <h2>❌ Detection Failed</h2>
                    <div class="upgrade-needed">
                        <p><strong>Could not detect PHP version automatically.</strong></p>
                        <p>This usually means your server has restrictions on accessing phpinfo.php</p>
                        <p>Please contact your hosting provider or try manual installation.</p>
                    </div>

                    <div class="upgrade-steps">
                        <h3>Manual Steps:</h3>
                        <ol>
                            <li>Check your hosting control panel for PHP version settings</li>
                            <li>Ensure PHP version is 7.4 or higher (8.1+ recommended)</li>
                            <li>Contact support if you need assistance upgrading PHP</li>
                        </ol>
                    </div>

                <?php else: ?>
                    <h2>📊 PHP Version Analysis</h2>

                    <div class="version-info">
                        <h3>Detected PHP Version: <?php echo htmlspecialchars($detected_version); ?></h3>
                    </div>

                    <?php if (version_compare($detected_version, MIN_PHP_VERSION, '<')): ?>
                        <div class="upgrade-needed">
                            <h3>🚨 PHP Version Too Old</h3>
                            <p><strong>Current:</strong> <?php echo htmlspecialchars($detected_version); ?></p>
                            <p><strong>Required:</strong> <?php echo MIN_PHP_VERSION; ?>+ (<?php echo RECOMMENDED_PHP_VERSION; ?>+ recommended)</p>
                            <p><strong>Your server is running PHP that's over 15 years old!</strong></p>
                        </div>

                        <div class="upgrade-steps">
                            <h3>🛠️ How to Upgrade PHP (Choose Your Method):</h3>

                            <h4>📊 cPanel/Shared Hosting:</h4>
                            <ol>
                                <li>Log into your hosting control panel (cPanel)</li>
                                <li>Look for <strong>"MultiPHP Manager"</strong> or <strong>"PHP Version"</strong></li>
                                <li>Select your domain and change PHP version to <strong>8.1</strong> or <strong>8.4</strong></li>
                                <li>Apply changes and wait 5-10 minutes</li>
                            </ol>

                            <h4>🎛️ Alternative Method (PHP Selector):</h4>
                            <ol>
                                <li>Look for <strong>"Select PHP Version"</strong> in cPanel</li>
                                <li>Choose <strong>PHP 8.1</strong> or higher</li>
                                <li>Enable required extensions: PDO, cURL, ZIP</li>
                                <li>Save settings</li>
                            </ol>

                            <h4>☎️ Need Help?</h4>
                            <p>Contact your hosting provider and say: <br>
                            <em>"Please upgrade my PHP version to 8.1 or higher for security and compatibility."</em></p>
                        </div>

                        <div style="text-align: center;">
                            <a href="?step=analyze" class="btn btn-primary">🔄 Check Again After Upgrade</a>
                            <a href="<?php echo $base_url . '/' . PHPINFO_FILE; ?>" class="btn btn-warning" target="_blank">📋 View Full PHP Info</a>
                        </div>

                    <?php else: ?>
                        <div class="upgrade-steps">
                            <h3>✅ PHP Version Compatible!</h3>
                            <p><strong>Detected:</strong> <?php echo htmlspecialchars($detected_version); ?></p>
                            <p><strong>Status:</strong>
                                <?php if (version_compare($detected_version, RECOMMENDED_PHP_VERSION, '>=')): ?>
                                    <span style="color: var(--success);">Excellent - Modern PHP version</span>
                                <?php else: ?>
                                    <span style="color: var(--warning);">Good - Compatible but consider upgrading to 8.1+</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div style="text-align: center;">
                            <a href="?step=install" class="btn btn-success">🚀 Proceed with Installation</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($step === 'install'): ?>
                <?php
                // Download and set up the modern installer
                $install_success = downloadInstaller();
                ?>

                <h2><?php echo $install_success ? '✅' : '❌'; ?> Installation Setup</h2>

                <?php if ($install_success): ?>
                    <div class="upgrade-steps">
                        <h3>🎉 Modern Installer Downloaded Successfully!</h3>
                        <p>The full installation wizard has been deployed to your server.</p>
                        <p>You can now proceed with the complete installation process.</p>
                    </div>

                    <div style="text-align: center;">
                        <a href="install.php" class="btn btn-success" style="font-size: 16px; padding: 16px 32px;">
                            🏁 Start Full Installation Wizard
                        </a>
                    </div>

                    <div class="version-info">
                        <h4>🧹 Cleanup Notice:</h4>
                        <p>After successful installation, you can safely delete:</p>
                        <ul style="margin: 10px 0 0 20px;">
                            <li><code>detect.php</code> (this file)</li>
                            <li><code>phpinfo.php</code> (diagnostic file)</li>
                        </ul>
                    </div>

                <?php else: ?>
                    <div class="upgrade-needed">
                        <h3>❌ Download Failed</h3>
                        <p>Could not download the installer automatically.</p>
                        <p>Please try the manual download method:</p>
                    </div>

                    <div class="upgrade-steps">
                        <h4>Manual Download:</h4>
                        <pre style="background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto;">wget <?php echo INSTALLER_URL; ?> -O install.php</pre>
                        <p>Then visit: <code>install.php</code></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
