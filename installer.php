<?php
/**
 * GlowHost Contact Form System - One-Click Installer
 * Version: 2.0 - Simple Working Installer
 * 
 * This installer downloads the complete source code and creates
 * a proper installation wizard at /install/
 */

// Configuration
define('INSTALLER_VERSION', '2.0');
define('PACKAGE_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('PACKAGE_DIR', 'contact-form-sales-main');
define('LOG_FILE', __DIR__ . '/installer.log');

// PHP Settings
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
define('TEMP_DIR', __DIR__ . '/installer_temp_' . session_id());

/**
 * System check
 */
function runSystemCheck() {
    $checks = [];

    $php_version = PHP_VERSION;
    $php_ok = version_compare($php_version, '7.4.0', '>=') && version_compare($php_version, '8.3.0', '<=');
    $checks['php_version'] = [
        'name' => 'PHP Version',
        'value' => $php_version,
        'status' => $php_ok ? 'OK' : 'ERROR',
        'message' => $php_ok ? 'Compatible' : 'Requires PHP 7.4 - 8.3'
    ];

    $required_extensions = ['curl', 'zip', 'json', 'session'];
    foreach ($required_extensions as $ext) {
        $loaded = extension_loaded($ext);
        $checks["ext_{$ext}"] = [
            'name' => "{$ext} Extension",
            'value' => $loaded ? 'Loaded' : 'Missing',
            'status' => $loaded ? 'OK' : 'ERROR',
            'message' => $loaded ? 'Available' : 'Required extension missing'
        ];
    }

    $writable = is_writable(__DIR__);
    $checks['write_permissions'] = [
        'name' => 'Write Permissions',
        'value' => $writable ? 'Writable' : 'Not Writable',
        'status' => $writable ? 'OK' : 'ERROR',
        'message' => $writable ? 'Directory is writable' : 'Cannot write to directory'
    ];

    return $checks;
}

// Handle AJAX installation requests
$get_action = $_GET['action'] ?? '';
$post_action = $_POST['action'] ?? '';

if ($get_action === 'install' || $post_action === 'install') {
    header('Content-Type: application/json');

    // Security check
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $post_csrf = $_POST['csrf_token'] ?? '';
    $session_csrf = $_SESSION['csrf_token'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($post_csrf, $session_csrf)) {
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
        exit;
    }

    try {
        $step = $_GET['step'] ?? $_POST['step'] ?? 'check';

        switch ($step) {
            case 'check':
                echo json_encode(checkSystemRequirements());
                break;
            case 'download':
                echo json_encode(downloadPackage());
                break;
            case 'extract':
                echo json_encode(extractPackage());
                break;
            case 'deploy':
                echo json_encode(deployFiles());
                break;
            case 'cleanup':
                echo json_encode(cleanupInstaller());
                break;
            default:
                throw new Exception('Invalid installation step: ' . $step);
        }
    } catch (Exception $e) {
        logMessage('ERROR: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Installation functions
 */
function checkSystemRequirements() {
    $requirements = runSystemCheck();
    $all_passed = true;
    foreach ($requirements as $req) {
        if ($req['status'] === 'ERROR') {
            $all_passed = false;
            break;
        }
    }
    return [
        'success' => true,
        'requirements' => $requirements,
        'all_passed' => $all_passed,
        'message' => $all_passed ? 'All requirements met' : 'Some requirements not met'
    ];
}

function downloadPackage() {
    logMessage('Starting package download from: ' . PACKAGE_URL);

    if (!file_exists(TEMP_DIR)) {
        if (!mkdir(TEMP_DIR, 0755, true)) {
            throw new Exception('Failed to create temp directory: ' . TEMP_DIR);
        }
    }

    $zip_file = TEMP_DIR . '/package.zip';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PACKAGE_URL);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GlowHost-Contact-Form-Installer/' . INSTALLER_VERSION);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('Download failed: ' . $error);
    }
    if ($http_code !== 200) {
        throw new Exception('Download failed with HTTP code: ' . $http_code);
    }
    if (!$data) {
        throw new Exception('No data received from download');
    }

    $bytes_written = file_put_contents($zip_file, $data);
    if ($bytes_written === false) {
        throw new Exception('Failed to save downloaded package');
    }

    logMessage('Package downloaded successfully: ' . formatBytes($bytes_written));

    return [
        'success' => true,
        'message' => 'Package downloaded successfully',
        'size' => formatBytes($bytes_written),
        'file' => $zip_file
    ];
}

function extractPackage() {
    $zip_file = TEMP_DIR . '/package.zip';

    if (!file_exists($zip_file)) {
        throw new Exception('Package file not found: ' . $zip_file);
    }

    $zip = new ZipArchive();
    $result = $zip->open($zip_file);
    if ($result !== TRUE) {
        throw new Exception('Failed to open ZIP file: ' . $result);
    }

    $extract_path = TEMP_DIR . '/extracted';
    if (!$zip->extractTo($extract_path)) {
        throw new Exception('Failed to extract ZIP file');
    }
    $zip->close();

    $package_path = $extract_path . '/' . PACKAGE_DIR;
    if (!is_dir($package_path)) {
        throw new Exception('Package structure not found after extraction');
    }

    logMessage('Package extracted successfully to: ' . $package_path);

    return [
        'success' => true,
        'message' => 'Package extracted successfully',
        'path' => $package_path,
        'files' => countFiles($package_path)
    ];
}

function deployFiles() {
    $source_path = TEMP_DIR . '/extracted/' . PACKAGE_DIR;
    $target_path = __DIR__;

    if (!is_dir($source_path)) {
        throw new Exception('Source directory not found: ' . $source_path);
    }

    logMessage('Deploying files from ' . $source_path . ' to web root: ' . $target_path);

    $files_copied = 0;
    $items = scandir($source_path);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $source_item = $source_path . '/' . $item;
        $target_item = $target_path . '/' . $item;

        // Skip copying installer.php to avoid overwriting ourselves
        if (strpos($item, 'installer') !== false) {
            logMessage('Skipping installer file: ' . $item);
            continue;
        }

        if (is_dir($source_item)) {
            logMessage('Copying directory: ' . $item);
            $files_copied += copyDirectoryContents($source_item, $target_item);
        } else {
            logMessage('Copying file: ' . $item);
            if (copy($source_item, $target_item)) {
                $files_copied++;
            }
        }
    }

    // Create the install wizard directory and files
    createInstallWizard();

    logMessage('Files deployed successfully - ' . $files_copied . ' files copied to web root');

    return [
        'success' => true,
        'message' => 'Files deployed successfully to web root',
        'files_copied' => $files_copied,
        'install_url' => 'install/'
    ];
}

function copyDirectoryContents($source, $destination) {
    $files_copied = 0;

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $target = $destination . '/' . $iterator->getSubPathName();

        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            $basename = basename($item);
            if (strpos($basename, 'installer') === false) {
                copy($item, $target);
                $files_copied++;
            }
        }
    }

    return $files_copied;
}

function createInstallWizard() {
    $install_dir = __DIR__ . '/install';
    
    // Create install directory if it doesn't exist
    if (!is_dir($install_dir)) {
        mkdir($install_dir, 0755, true);
        logMessage('Created install directory: ' . $install_dir);
    }

    // Create index.php for the install wizard
    $wizard_content = '<?php
/**
 * GlowHost Contact Form System - Installation Wizard
 * Generated by installer v' . INSTALLER_VERSION . '
 */

session_start();

// Security check
if (!file_exists(__DIR__ . "/../.installer_complete")) {
    die("Installation not properly completed. Please run installer.php first.");
}

$step = isset($_GET["step"]) ? $_GET["step"] : "welcome";
$error = isset($_GET["error"]) ? $_GET["error"] : "";
$success = isset($_GET["success"]) ? $_GET["success"] : "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - Setup Wizard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .wizard-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            min-height: 500px;
        }
        .wizard-header {
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .wizard-content {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }
        .step {
            padding: 8px 16px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 14px;
        }
        .step.active {
            background: #3b82f6;
            color: white;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .btn {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
        }
        .help-text {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>🚀 Contact Form Setup</h1>
            <p>Complete your installation in just a few steps</p>
        </div>

        <div class="wizard-content">
            <div class="step-indicator">
                <div class="step <?php echo $step === "welcome" ? "active" : ""; ?>">Welcome</div>
                <div class="step <?php echo $step === "config" ? "active" : ""; ?>">Configuration</div>
                <div class="step <?php echo $step === "complete" ? "active" : ""; ?>">Complete</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($step === "welcome"): ?>
                <div style="text-align: center;">
                    <h2>Welcome to GlowHost Contact Form System!</h2>
                    <p style="margin: 20px 0; color: #6b7280; line-height: 1.6;">
                        Your contact form system has been successfully deployed. 
                        Now let\'s configure it to work with your specific requirements.
                    </p>
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: left;">
                        <h3>✅ What\'s been installed:</h3>
                        <ul style="margin: 10px 0 0 20px; color: #374151;">
                            <li>Complete contact form system</li>
                            <li>API endpoints for form processing</li>
                            <li>Configuration files</li>
                            <li>Source code and assets</li>
                        </ul>
                    </div>

                    <a href="?step=config" class="btn">Continue to Configuration →</a>
                </div>

            <?php elseif ($step === "config"): ?>
                <h2>Configuration Settings</h2>
                <p style="margin-bottom: 20px; color: #6b7280;">
                    Configure your contact form system settings below:
                </p>

                <form method="post" action="setup.php">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" placeholder="Your Website Name" required>
                        <div class="help-text">This will appear in email headers and form titles</div>
                    </div>

                    <div class="form-group">
                        <label for="admin_email">Admin Email Address</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="admin@yoursite.com" required>
                        <div class="help-text">Where contact form submissions will be sent</div>
                    </div>

                    <div class="form-group">
                        <label for="from_email">From Email Address</label>
                        <input type="email" id="from_email" name="from_email" placeholder="noreply@yoursite.com" required>
                        <div class="help-text">Email address used for sending notifications</div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_host">SMTP Host (Optional)</label>
                        <input type="text" id="smtp_host" name="smtp_host" placeholder="mail.yourhost.com">
                        <div class="help-text">Leave empty to use default PHP mail() function</div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_username">SMTP Username (Optional)</label>
                        <input type="text" id="smtp_username" name="smtp_username" placeholder="your-smtp-username">
                    </div>

                    <div class="form-group">
                        <label for="smtp_password">SMTP Password (Optional)</label>
                        <input type="password" id="smtp_password" name="smtp_password" placeholder="your-smtp-password">
                    </div>

                    <button type="submit" class="btn">Save Configuration & Complete Setup</button>
                    <a href="?step=welcome" class="btn" style="background: #6b7280;">← Back</a>
                </form>

            <?php elseif ($step === "complete"): ?>
                <div style="text-align: center;">
                    <h2>🎉 Installation Complete!</h2>
                    <p style="margin: 20px 0; color: #6b7280; line-height: 1.6;">
                        Your GlowHost Contact Form System is now ready to use!
                    </p>

                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h3 style="color: #16a34a; margin-bottom: 15px;">Next Steps:</h3>
                        <div style="text-align: left; color: #374151;">
                            <p>• <strong>View your contact form:</strong> <a href="../" style="color: #2563eb;">Go to your website</a></p>
                            <p>• <strong>Test the form:</strong> Submit a test message to verify everything works</p>
                            <p>• <strong>Customize:</strong> Edit the form design and fields as needed</p>
                            <p>• <strong>Documentation:</strong> Check the README.md file for more details</p>
                        </div>
                    </div>

                    <a href="../" class="btn">View Your Contact Form</a>
                    <a href="../README.md" class="btn" style="background: #6b7280;">Read Documentation</a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>';

    $wizard_file = $install_dir . '/index.php';
    if (file_put_contents($wizard_file, $wizard_content) !== false) {
        logMessage('Created installation wizard: ' . $wizard_file);
    } else {
        logMessage('WARNING: Failed to create installation wizard');
    }

    // Create setup.php for handling configuration
    $setup_content = '<?php
/**
 * Setup handler for configuration
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $config = [
        "site_name" => $_POST["site_name"] ?? "",
        "admin_email" => $_POST["admin_email"] ?? "",
        "from_email" => $_POST["from_email"] ?? "",
        "smtp_host" => $_POST["smtp_host"] ?? "",
        "smtp_username" => $_POST["smtp_username"] ?? "",
        "smtp_password" => $_POST["smtp_password"] ?? ""
    ];

    // Save configuration
    $config_file = __DIR__ . "/../config/app.php";
    $config_dir = dirname($config_file);
    
    if (!is_dir($config_dir)) {
        mkdir($config_dir, 0755, true);
    }

    $config_content = "<?php\\n\\n";
    $config_content .= "// GlowHost Contact Form Configuration\\n";
    $config_content .= "// Generated on " . date("Y-m-d H:i:s") . "\\n\\n";
    $config_content .= "return [\\n";
    foreach ($config as $key => $value) {
        $config_content .= "    \"" . $key . "\" => \"" . addslashes($value) . "\",\\n";
    }
    $config_content .= "];\\n";

    if (file_put_contents($config_file, $config_content)) {
        header("Location: index.php?step=complete&success=" . urlencode("Configuration saved successfully!"));
    } else {
        header("Location: index.php?step=config&error=" . urlencode("Failed to save configuration."));
    }
} else {
    header("Location: index.php?step=config");
}
exit;';

    $setup_file = $install_dir . '/setup.php';
    if (file_put_contents($setup_file, $setup_content) !== false) {
        logMessage('Created setup handler: ' . $setup_file);
    }
}

function cleanupInstaller() {
    logMessage('Starting cleanup process');

    if (is_dir(TEMP_DIR)) {
        removeDirectory(TEMP_DIR);
        logMessage('Temporary directory cleaned: ' . TEMP_DIR);
    }

    file_put_contents(__DIR__ . '/.installer_complete', date('Y-m-d H:i:s'));

    logMessage('Cleanup completed successfully - installation complete');

    return [
        'success' => true,
        'message' => 'Installation completed successfully',
        'redirect_url' => 'install/',
        'cleanup_complete' => true
    ];
}

function removeDirectory($directory) {
    if (!is_dir($directory)) return false;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getRealPath());
        } else {
            unlink($item->getRealPath());
        }
    }
    rmdir($directory);
    return true;
}

function countFiles($directory) {
    $count = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $count++;
        }
    }
    return $count;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    @file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

// Initialize session token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$already_installed = file_exists(__DIR__ . '/.installer_complete') || file_exists(__DIR__ . '/install/index.php');
$system_checks = runSystemCheck();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form System - Installer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            min-height: 600px;
        }
        .installer-header {
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .installer-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .installer-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .system-check-container {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 32px;
        }
        .system-checks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .check-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid;
            background: white;
        }
        .check-item.ok {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .check-item.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .installer-content { 
            padding: 40px; 
            text-align: center;
        }
        .install-button {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin: 16px 0;
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }
        .install-button:hover {
            background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
        }
        .install-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .progress-bar {
            width: 100%;
            max-width: 400px;
            height: 12px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin: 16px auto;
            display: none;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%);
            border-radius: 6px;
            transition: width 0.3s ease;
            width: 0%;
        }
        .status-messages {
            margin-top: 20px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .status-message {
            padding: 12px;
            border-radius: 8px;
            margin: 8px 0;
            font-weight: 500;
        }
        .status-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        .status-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .status-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #2563eb;
        }
        .instructions {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .instructions h3 {
            color: #1f2937;
            margin-bottom: 10px;
        }
        .instructions ol {
            color: #374151;
            line-height: 1.6;
            margin-left: 20px;
        }
        .instructions li {
            margin-bottom: 5px;
        }
        .instructions code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>🚀 GlowHost Contact Form System</h1>
            <p>Simple One-Click Installer v<?php echo INSTALLER_VERSION; ?></p>
        </div>

        <div class="system-check-container">
            <h2>🔍 System Compatibility Check</h2>
            <div class="system-checks">
                <?php foreach ($system_checks as $check): ?>
                <div class="check-item <?php echo strtolower($check['status']); ?>">
                    <div>
                        <?php if ($check['status'] === 'OK'): ?>
                            ✅
                        <?php else: ?>
                            ❌
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($check['name']); ?></div>
                        <div style="font-size: 13px; font-family: monospace;"><?php echo htmlspecialchars($check['value']); ?></div>
                        <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($check['message']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="installer-content">
            <?php if ($already_installed): ?>
                <div style="background: #fffbeb; border: 1px solid #f6e05e; color: #744210; padding: 20px; border-radius: 8px;">
                    <h3>⚠️ System Already Installed</h3>
                    <p>The contact form system appears to be already installed.</p>
                    <br>
                    <a href="install/" style="color: #1a365d; font-weight: 600; text-decoration: none; background: #e2e8f0; padding: 8px 16px; border-radius: 6px; display: inline-block;">→ Access Installation Wizard</a>
                </div>
            <?php else: ?>
                <h2>Ready to Install</h2>
                <p>This installer will download and deploy the complete contact form system.</p>

                <div class="instructions">
                    <h3>📋 What this installer does:</h3>
                    <ol>
                        <li>Downloads the complete source code from GitHub</li>
                        <li>Extracts and deploys all files to your web directory</li>
                        <li>Creates an installation wizard at <code>/install/</code></li>
                        <li>Sets up the basic configuration structure</li>
                        <li>Provides a guided setup process</li>
                    </ol>
                </div>

                <?php
                $can_install = true;
                foreach ($system_checks as $check) {
                    if ($check['status'] === 'ERROR') {
                        $can_install = false;
                        break;
                    }
                }
                ?>

                <div class="progress-bar" id="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>

                <button class="install-button" id="install-button" onclick="startInstallation()" <?php echo $can_install ? '' : 'disabled'; ?>>
                    <span id="button-text">
                        <?php echo $can_install ? '🚀 Install Contact Form System' : '❌ Cannot Install - Fix Errors Above'; ?>
                    </span>
                </button>

                <div class="status-messages" id="status-messages"></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    const steps = ['check', 'download', 'extract', 'deploy', 'cleanup'];
    const stepNames = ['Checking Requirements', 'Downloading Package', 'Extracting Files', 'Deploying System', 'Completing Installation'];

    async function startInstallation() {
        const button = document.getElementById('install-button');
        const progressBar = document.getElementById('progress-bar');
        const progressFill = document.getElementById('progress-fill');

        button.disabled = true;
        button.innerHTML = '<span class="spinner"></span> Installing...';
        progressBar.style.display = 'block';

        try {
            for (let i = 0; i < steps.length; i++) {
                showStatus(`${stepNames[i]}...`, 'info');
                await runInstallationStep(steps[i]);
                const progress = ((i + 1) / steps.length) * 100;
                progressFill.style.width = progress + '%';
                showStatus(`${stepNames[i]} completed!`, 'success');
            }

            showStatus('🎉 Installation completed successfully! Redirecting to setup wizard...', 'success');
            setTimeout(() => { window.location.href = 'install/'; }, 3000);

        } catch (error) {
            showStatus('❌ Installation failed: ' + error.message, 'error');
            button.disabled = false;
            button.innerHTML = '🚀 Install Contact Form System';
        }
    }

    async function runInstallationStep(step) {
        const response = await fetch(`?action=install&step=${step}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=<?php echo $_SESSION['csrf_token']; ?>'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Unknown error occurred');
        }
        return result;
    }

    function showStatus(message, type) {
        const container = document.getElementById('status-messages');
        const statusDiv = document.createElement('div');
        
        statusDiv.className = `status-message status-${type}`;
        statusDiv.innerHTML = message;
        container.appendChild(statusDiv);

        // Keep only the last 5 messages
        if (container.children.length > 5) {
            container.removeChild(container.firstChild);
        }
    }
    </script>
</body>
</html>