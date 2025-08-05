<?php
/**
 * GlowHost Contact Form - System Requirements Check
 * Version: 1.0 - Simplified, Reliable Detection
 */

// Prevent timeouts during checks
set_time_limit(60);

// Configuration
define('MIN_PHP_VERSION', '7.4.0');
define('RECOMMENDED_PHP_VERSION', '8.1.0');
define('INSTALLER_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php');

// Run system checks (executed directly, no AJAX)
$system_checks = [
    'php_version' => checkPHPVersion(),
    'extensions' => checkRequiredExtensions(),
    'directory_permissions' => checkDirectoryPermissions(),
    'connectivity' => checkConnectivity()
];

// Calculate overall qualification status
$system_qualified =
    $system_checks['php_version']['status'] &&
    $system_checks['extensions']['critical_passed'] &&
    $system_checks['directory_permissions']['status'] &&
    $system_checks['connectivity']['status'];

/**
 * CHECKING FUNCTIONS - Reliable, simple checks
 */
function checkPHPVersion() {
    $current = PHP_VERSION;
    $meets_min = version_compare($current, MIN_PHP_VERSION, '>=');
    $meets_recommended = version_compare($current, RECOMMENDED_PHP_VERSION, '>=');

    return [
        'status' => $meets_min,
        'level' => $meets_recommended ? 'excellent' : ($meets_min ? 'compatible' : 'incompatible'),
        'current' => $current,
        'required' => MIN_PHP_VERSION,
        'recommended' => RECOMMENDED_PHP_VERSION,
        'message' => $meets_min ?
            ($meets_recommended ? "PHP $current - Excellent" : "PHP $current - Compatible but upgrade recommended") :
            "PHP $current - Incompatible (requires " . MIN_PHP_VERSION . "+)"
    ];
}

function checkRequiredExtensions() {
    $extensions = [
        'PDO' => ['critical' => true, 'check' => 'class_exists', 'name' => 'PDO', 'message' => 'Required for database connectivity'],
        'ZipArchive' => ['critical' => true, 'check' => 'class_exists', 'name' => 'ZipArchive', 'message' => 'Required for package extraction'],
        'cURL or allow_url_fopen' => ['critical' => true, 'check' => 'custom', 'name' => 'curl_init', 'message' => 'Required for downloading components'],
        'mbstring' => ['critical' => false, 'check' => 'extension_loaded', 'name' => 'mbstring', 'message' => 'Recommended for text processing']
    ];

    $results = [];
    $critical_passed = true;
    $optional_passed = true;

    foreach ($extensions as $name => $extension) {
        $status = false;

        if ($extension['check'] === 'custom' && $name === 'cURL or allow_url_fopen') {
            // Special case for download capability
            $status = function_exists('curl_init') || ini_get('allow_url_fopen');
        } else {
            $check_func = $extension['check'];
            $check_name = $extension['name'];
            $status = $check_func($check_name);
        }

        $results[$name] = [
            'status' => $status,
            'critical' => $extension['critical'],
            'message' => $extension['message']
        ];

        if ($extension['critical'] && !$status) {
            $critical_passed = false;
        } elseif (!$extension['critical'] && !$status) {
            $optional_passed = false;
        }
    }

    return [
        'results' => $results,
        'critical_passed' => $critical_passed,
        'optional_passed' => $optional_passed
    ];
}

function checkDirectoryPermissions() {
    $dir = __DIR__;
    $writable = is_writable($dir);

    return [
        'status' => $writable,
        'directory' => $dir,
        'message' => $writable ?
            "Directory is writable" :
            "Directory is not writable - chmod or chown required"
    ];
}

function checkConnectivity() {
    $test_url = 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/README.md';
    $success = false;
    $method = '';
    $message = '';

    // Try cURL first
    if (function_exists('curl_init')) {
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result !== false && $status == 200) {
            $success = true;
            $method = 'cURL';
            $message = "GitHub connectivity successful";
        } else {
            $message = "cURL connection failed with status: $status";
        }
    }
    // Try file_get_contents if cURL failed or isn't available
    elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create(['http' => ['timeout' => 10]]);
        $result = @file_get_contents($test_url, false, $context);

        if ($result !== false) {
            $success = true;
            $method = 'file_get_contents';
            $message = "GitHub connectivity successful";
        } else {
            $message = "file_get_contents connection failed";
        }
    } else {
        $message = "No download method available (requires cURL or allow_url_fopen)";
    }

    return [
        'status' => $success,
        'method' => $method,
        'message' => $message
    ];
}

/**
 * Deploy phpinfo for additional diagnostics
 */
function deployPhpInfo() {
    if (!file_exists('phpinfo.php')) {
        $content = '<?php phpinfo(); ?>';
        @file_put_contents('phpinfo.php', $content);
    }
    return file_exists('phpinfo.php');
}

// Create phpinfo.php for extra diagnostics
deployPhpInfo();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - System Check</title>
    <style>
        /* Simple, clean styling */
        :root {
            --primary: #1e3b97;
            --primary-dark: #061c63;
            --primary-light: #4164dd;
            --success: #16a34a;
            --warning: #d97706;
            --error: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: var(--gray-800);
            background-color: var(--gray-50);
            margin: 0;
            padding: 0;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
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

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .status-box {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .status-icon {
            font-size: 1.5rem;
            width: 2rem;
            height: 2rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
        }

        .status-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .status-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
        }

        .status-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        .check-grid {
            display: grid;
            gap: 1rem;
        }

        .check-item {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .check-item.success {
            border-color: #bbf7d0;
            background-color: #f0fdf4;
        }

        .check-item.warning {
            border-color: #fde68a;
            background-color: #fffbeb;
        }

        .check-item.error {
            border-color: #fecaca;
            background-color: #fef2f2;
        }

        .check-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .check-title {
            flex: 1;
        }

        .check-title h3 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .check-title .status {
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .check-detail {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            padding-left: 2.75rem;
        }

        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .button:hover {
            background-color: var(--primary-dark);
        }

        .button-success {
            background-color: var(--success);
        }

        .button-success:hover {
            background-color: #15803d;
        }

        .command-box {
            background-color: #1e293b;
            color: white;
            padding: 1rem;
            border-radius: 6px;
            font-family: monospace;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .action-container {
            margin-top: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://glowhost.com/wp-content/uploads/logo-sans-tagline.png" alt="GlowHost">
        <h1>Contact Form System - Environment Check</h1>
        <p>Verifying your server compatibility</p>
    </div>

    <div class="container">
        <!-- Overall System Status -->
        <div class="card">
            <div class="status-box <?php echo $system_qualified ? 'status-success' : 'status-error'; ?>">
                <div class="status-icon">
                    <?php echo $system_qualified ? '✅' : '❌'; ?>
                </div>
                <div>
                    <h2><?php echo $system_qualified ? 'System Qualified' : 'System Check Failed'; ?></h2>
                    <p>
                        <?php echo $system_qualified ?
                            'Your server meets all requirements for the GlowHost Contact Form installation.' :
                            'Your server does not meet all requirements. Please review the issues below.';
                        ?>
                    </p>
                </div>
            </div>

            <!-- System Checks Details -->
            <div class="check-grid">
                <!-- PHP Version Check -->
                <div class="check-item <?php echo $system_checks['php_version']['status'] ?
                    ($system_checks['php_version']['level'] === 'excellent' ? 'success' : 'warning') :
                    'error'; ?>">
                    <div class="check-header">
                        <div class="check-icon">
                            <?php
                            if (!$system_checks['php_version']['status']) {
                                echo '❌';
                            } elseif ($system_checks['php_version']['level'] === 'excellent') {
                                echo '✅';
                            } else {
                                echo '⚠️';
                            }
                            ?>
                        </div>
                        <div class="check-title">
                            <h3>PHP Version</h3>
                            <div class="status"><?php echo $system_checks['php_version']['message']; ?></div>
                        </div>
                    </div>
                    <div class="check-detail">
                        <?php if (!$system_checks['php_version']['status']): ?>
                            <p>Your PHP version is too old for this application.</p>
                            <p><strong>Required:</strong> PHP <?php echo $system_checks['php_version']['required']; ?>+</p>
                            <p><strong>Recommended:</strong> PHP <?php echo $system_checks['php_version']['recommended']; ?>+</p>
                            <p><strong>How to fix:</strong> Contact your hosting provider to upgrade PHP, or use your hosting control panel to select a newer PHP version.</p>
                            <p><a href="phpinfo.php" target="_blank">View Full PHP Info</a></p>
                        <?php elseif ($system_checks['php_version']['level'] !== 'excellent'): ?>
                            <p>Your PHP version is compatible but not optimal.</p>
                            <p><strong>Recommended:</strong> Upgrade to PHP <?php echo $system_checks['php_version']['recommended']; ?>+ for better performance and security.</p>
                            <p><a href="phpinfo.php" target="_blank">View Full PHP Info</a></p>
                        <?php else: ?>
                            <p>Your PHP version is optimal for this application.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Required Extensions -->
                <div class="check-item <?php echo $system_checks['extensions']['critical_passed'] ?
                    ($system_checks['extensions']['optional_passed'] ? 'success' : 'warning') :
                    'error'; ?>">
                    <div class="check-header">
                        <div class="check-icon">
                            <?php
                            if (!$system_checks['extensions']['critical_passed']) {
                                echo '❌';
                            } elseif ($system_checks['extensions']['optional_passed']) {
                                echo '✅';
                            } else {
                                echo '⚠️';
                            }
                            ?>
                        </div>
                        <div class="check-title">
                            <h3>PHP Extensions</h3>
                            <div class="status">
                                <?php echo $system_checks['extensions']['critical_passed'] ?
                                    'Required extensions available' :
                                    'Missing critical extensions'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="check-detail">
                        <?php foreach ($system_checks['extensions']['results'] as $name => $ext): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <strong><?php echo $name; ?>:</strong>
                                <?php if ($ext['status']): ?>
                                    <span style="color: var(--success);">Available</span>
                                <?php else: ?>
                                    <span style="color: var(--error);">Missing</span>
                                    <?php if ($ext['critical']): ?> (Critical)<?php endif; ?>
                                <?php endif; ?>
                                <br>
                                <small><?php echo $ext['message']; ?></small>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$system_checks['extensions']['critical_passed']): ?>
                            <p><strong>How to fix:</strong> Contact your hosting provider to enable these PHP extensions, or install them if you have server access.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Directory Permissions -->
                <div class="check-item <?php echo $system_checks['directory_permissions']['status'] ? 'success' : 'error'; ?>">
                    <div class="check-header">
                        <div class="check-icon">
                            <?php echo $system_checks['directory_permissions']['status'] ? '✅' : '❌'; ?>
                        </div>
                        <div class="check-title">
                            <h3>Directory Permissions</h3>
                            <div class="status"><?php echo $system_checks['directory_permissions']['message']; ?></div>
                        </div>
                    </div>
                    <div class="check-detail">
                        <p>Directory: <?php echo $system_checks['directory_permissions']['directory']; ?></p>
                        <?php if (!$system_checks['directory_permissions']['status']): ?>
                            <p><strong>How to fix:</strong> Set proper write permissions on this directory using chmod or contact your hosting provider.</p>
                            <p>Command: <code>chmod 755 <?php echo $system_checks['directory_permissions']['directory']; ?></code></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- GitHub Connectivity -->
                <div class="check-item <?php echo $system_checks['connectivity']['status'] ? 'success' : 'error'; ?>">
                    <div class="check-header">
                        <div class="check-icon">
                            <?php echo $system_checks['connectivity']['status'] ? '✅' : '❌'; ?>
                        </div>
                        <div class="check-title">
                            <h3>GitHub Connectivity</h3>
                            <div class="status"><?php echo $system_checks['connectivity']['message']; ?></div>
                        </div>
                    </div>
                    <div class="check-detail">
                        <?php if ($system_checks['connectivity']['status']): ?>
                            <p>Method: <?php echo $system_checks['connectivity']['method']; ?></p>
                        <?php else: ?>
                            <p><strong>How to fix:</strong> Check your internet connection or firewall settings. Your server needs to access GitHub to download components.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="action-container">
                <?php if ($system_qualified): ?>
                    <div>
                        <p>Run this command to download the installer:</p>
                        <div class="command-box">wget <?php echo INSTALLER_URL; ?> -O installer.php</div>
                        <p>Then visit <a href="installer.php">installer.php</a> in your browser</p>
                    </div>
                    <a href="<?php echo INSTALLER_URL; ?>" class="button button-success" download="installer.php">Download Installer</a>
                <?php else: ?>
                    <button onclick="location.reload()" class="button">Refresh Checks</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Support Information -->
        <div class="card">
            <h2>Need Assistance?</h2>
            <p>If you're having trouble meeting the system requirements, contact GlowHost support:</p>
            <p><strong>24/7/365 Support</strong><br>
            Toll Free: <a href="tel:+18882934678">1 (888) 293-HOST</a></p>
            <p><a href="phpinfo.php" target="_blank">View Complete PHP Information</a></p>
        </div>
    </div>
</body>
</html>