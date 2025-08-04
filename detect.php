<?php
/**
 * GlowHost Contact Form - Minimal PHP Version Detector
 * Version: 5.2 Compatible - No modern syntax
 */

// Get current directory info (PHP 5.2 compatible)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$base_url = rtrim($protocol . $host . $path, '/');

// Step parameter (PHP 5.2 compatible)
$step = isset($_GET['step']) ? $_GET['step'] : 'deploy';

/**
 * Deploy phpinfo.php if needed
 */
function deployPhpInfo() {
    if (!file_exists('phpinfo.php')) {
        $content = '<?php phpinfo(); ?>';
        return file_put_contents('phpinfo.php', $content) !== false;
    }
    return true;
}

/**
 * Get PHP version from phpinfo
 */
function getPhpVersion($base_url) {
    $phpinfo_url = $base_url . '/phpinfo.php';

    // Try file_get_contents first
    $content = @file_get_contents($phpinfo_url);

    if ($content === false) {
        return false;
    }

    // Look for PHP Version in the output
    if (preg_match('/PHP Version ([0-9]+\.[0-9]+\.[0-9]+[^\s<]*)/i', $content, $matches)) {
        return $matches[1];
    }

    return false;
}

// Deploy phpinfo.php first
deployPhpInfo();

?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Version Check - GlowHost Contact Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 20px; margin: -30px -30px 20px -30px; border-radius: 8px 8px 0 0; text-align: center; }
        .version-box { background: #f0f9ff; border: 2px solid #2563eb; padding: 20px; margin: 20px 0; border-radius: 6px; }
        .error-box { background: #fef2f2; border: 2px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 6px; }
        .success-box { background: #f0fdf4; border: 2px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 6px; }
        .warning-box { background: #fffbeb; border: 2px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 6px; }
        .btn { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .btn-success { background: #10b981; }
        .btn-warning { background: #f59e0b; }
        ol { margin: 10px 0 0 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>PHP Environment Check</p>
        </div>

        <?php if ($step === 'deploy'): ?>
            <h2>🔍 Checking Your PHP Version...</h2>
            <div class="version-box">
                <p>Deploying diagnostic files and checking compatibility...</p>
            </div>
            <script>
            setTimeout(function() {
                window.location.href = '?step=check';
            }, 2000);
            </script>

        <?php elseif ($step === 'check'): ?>
            <?php
            $detected_version = getPhpVersion($base_url);

            if ($detected_version === false):
            ?>
                <h2>❌ Could Not Detect PHP Version</h2>
                <div class="error-box">
                    <p><strong>Automatic detection failed.</strong></p>
                    <p>This could mean your server has restrictions on accessing phpinfo.</p>
                    <p>Please check your hosting control panel for PHP version information.</p>
                </div>

                <div class="warning-box">
                    <h3>Manual Steps:</h3>
                    <ol>
                        <li>Log into your hosting control panel (cPanel)</li>
                        <li>Look for "PHP Version" or "MultiPHP Manager"</li>
                        <li>Ensure PHP is set to 8.1 or higher</li>
                        <li>Contact support if you need help upgrading</li>
                    </ol>
                </div>

            <?php else: ?>
                <h2>📊 PHP Version Analysis</h2>

                <div class="version-box">
                    <h3>Detected: PHP <?php echo htmlspecialchars($detected_version); ?></h3>
                </div>

                <?php if (version_compare($detected_version, '8.1.0', '>=')):  ?>
                    <!-- PHP 8.1+ Perfect -->
                    <div class="success-box">
                        <h3>✅ Excellent PHP Version!</h3>
                        <p><strong>Status:</strong> Perfect for modern web applications</p>
                        <p><strong>Compatibility:</strong> Full installer support</p>
                        <p>Your PHP version is current and secure. Proceeding with installation is recommended.</p>
                    </div>

                    <div style="text-align: center;">
                        <a href="?step=install" class="btn btn-success">🚀 Download & Run Installer</a>
                    </div>

                <?php elseif (version_compare($detected_version, '7.4.0', '>=')):  ?>
                    <!-- PHP 7.4-8.0 Compatible but recommend upgrade -->
                    <div class="warning-box">
                        <h3>⚠️ PHP Version Compatible But Outdated</h3>
                        <p><strong>Status:</strong> Will work, but not optimal</p>
                        <p><strong>Recommendation:</strong> Upgrade to PHP 8.4 for best results</p>
                        <p>Your current PHP version is functional but consider upgrading for better security and performance.</p>
                    </div>

                    <div class="version-box">
                        <h4>Upgrade Instructions (Recommended):</h4>
                        <ol>
                            <li>Access your hosting control panel (cPanel)</li>
                            <li>Find "MultiPHP Manager" or "PHP Version"</li>
                            <li>Change PHP version to <strong>8.4</strong></li>
                            <li>Apply changes and wait a few minutes</li>
                        </ol>
                    </div>

                    <div style="text-align: center;">
                        <a href="?step=install" class="btn btn-warning">⚡ Proceed Anyway</a>
                        <a href="?step=check" class="btn">🔄 Check Again After Upgrade</a>
                    </div>

                <?php else: ?>
                    <!-- PHP < 7.4 Too old -->
                    <div class="error-box">
                        <h3>🚨 PHP Version Too Old</h3>
                        <p><strong>Current:</strong> <?php echo htmlspecialchars($detected_version); ?></p>
                        <p><strong>Required:</strong> 7.4+ (8.4 recommended)</p>
                        <p><strong>Your PHP version is over 10 years old and cannot run modern applications.</strong></p>
                    </div>

                    <div class="warning-box">
                        <h4>🛠️ Required: Upgrade Your PHP Version</h4>

                        <h5>Method 1: cPanel (Most Common)</h5>
                        <ol>
                            <li>Log into your hosting control panel</li>
                            <li>Look for <strong>"MultiPHP Manager"</strong></li>
                            <li>Select your domain</li>
                            <li>Change PHP version to <strong>8.4</strong></li>
                            <li>Apply and wait 5-10 minutes</li>
                        </ol>

                        <h5>Method 2: PHP Selector</h5>
                        <ol>
                            <li>Find <strong>"Select PHP Version"</strong> in cPanel</li>
                            <li>Choose <strong>PHP 8.4</strong></li>
                            <li>Enable extensions: PDO, cURL, ZIP</li>
                            <li>Save settings</li>
                        </ol>

                        <h5>Need Help?</h5>
                        <p>Contact your hosting provider: <em>"Please upgrade my PHP to version 8.4 for security and compatibility."</em></p>
                    </div>

                    <div style="text-align: center;">
                        <a href="?step=check" class="btn">🔄 Check Again After Upgrade</a>
                        <a href="phpinfo.php" class="btn" target="_blank">📋 View Full PHP Info</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif ($step === 'install'): ?>
            <h2>📥 Downloading Modern Installer</h2>

            <div class="success-box">
                <h3>✅ PHP Version Compatible!</h3>
                <p>Downloading the full installation wizard...</p>
            </div>

            <div class="version-box">
                <h4>Manual Download Command:</h4>
                <pre style="background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto;">wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php -O install.php</pre>

                <p><strong>Then visit:</strong> <code>install.php</code> in your browser</p>
            </div>

            <div class="warning-box">
                <h4>🧹 Cleanup (After Successful Installation):</h4>
                <p>You can safely delete these diagnostic files:</p>
                <ul>
                    <li><code>detect.php</code> (this file)</li>
                    <li><code>phpinfo.php</code> (diagnostic file)</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="install.php" class="btn btn-success">🏁 Start Installation</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
