<?php
/**
 * GlowHost Contact Form System - One-Click Installer (Fixed Temp Directory)
 * Version: 1.2-fixed - Uses persistent temp directory in web root
 */

// Always show errors for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Configuration - FIXED: Use web directory for temp files
define('INSTALLER_VERSION', '1.2-fixed');
define('PACKAGE_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('PACKAGE_DIR', 'contact-form-sales-main');
define('TEMP_DIR', __DIR__ . '/installer_temp_' . session_id()); // FIXED: Use web directory
define('LOG_FILE', __DIR__ . '/installer.log');

// Start session FIRST
session_start();

// Generate consistent temp directory based on session
if (!defined('TEMP_DIR')) {
    define('TEMP_DIR', __DIR__ . '/installer_temp_' . session_id());
}

/**
 * Run system compatibility check
 */
function runSystemCheck() {
    $checks = [];

    // PHP Version Check
    $php_version = PHP_VERSION;
    $php_ok = version_compare($php_version, '7.4.0', '>=') && version_compare($php_version, '8.3.0', '<=');
    $checks['php_version'] = [
        'name' => 'PHP Version',
        'value' => $php_version,
        'status' => $php_ok ? 'OK' : 'ERROR',
        'message' => $php_ok ? 'Compatible' : 'Requires PHP 7.4 - 8.2 (current: ' . $php_version . ')'
    ];

    // Required Extensions
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

    // Memory Limit
    $memory_limit = ini_get('memory_limit');
    $memory_ok = (int)$memory_limit >= 128 || $memory_limit === '-1';
    $checks['memory_limit'] = [
        'name' => 'Memory Limit',
        'value' => $memory_limit,
        'status' => $memory_ok ? 'OK' : 'WARNING',
        'message' => $memory_ok ? 'Sufficient' : 'May need 128M+'
    ];

    // Write Permissions
    $writable = is_writable(__DIR__);
    $checks['write_permissions'] = [
        'name' => 'Write Permissions',
        'value' => $writable ? 'Writable' : 'Not Writable',
        'status' => $writable ? 'OK' : 'ERROR',
        'message' => $writable ? 'Directory is writable' : 'Cannot write to directory'
    ];

    // Session Support
    $session_ok = function_exists('session_start');
    $checks['session_support'] = [
        'name' => 'Session Support',
        'value' => $session_ok ? 'Available' : 'Missing',
        'status' => $session_ok ? 'OK' : 'ERROR',
        'message' => $session_ok ? 'Sessions supported' : 'Session functions missing'
    ];

    // Server Software
    $server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
    $checks['server_software'] = [
        'name' => 'Server Software',
        'value' => $server,
        'status' => 'INFO',
        'message' => 'Server information'
    ];

    return $checks;
}

// Handle AJAX installation requests
$get_action = isset($_GET['action']) ? $_GET['action'] : '';
$post_action = isset($_POST['action']) ? $_POST['action'] : '';
if ($get_action === 'install' || $post_action === 'install') {
    header('Content-Type: application/json');

    // Security check
    $post_csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $session_csrf = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($post_csrf, $session_csrf)) {
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
        exit;
    }

    try {
        $get_step = isset($_GET['step']) ? $_GET['step'] : '';
        $post_step = isset($_POST['step']) ? $_POST['step'] : '';
        $step = $get_step ?: $post_step ?: 'check';

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
            'error' => $e->getMessage(),
            'details' => 'Check installer.log for more details'
        ]);
    }
    exit;
}

/**
 * Check system requirements
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

    logMessage('System requirements check: ' . ($all_passed ? 'PASSED' : 'FAILED'));

    return [
        'success' => true,
        'requirements' => $requirements,
        'all_passed' => $all_passed,
        'message' => $all_passed ? 'All requirements met' : 'Some requirements not met'
    ];
}

/**
 * Download the package from GitHub
 */
function downloadPackage() {
    logMessage('Starting package download from: ' . PACKAGE_URL);
    logMessage('Using temp directory: ' . TEMP_DIR);

    if (!file_exists(TEMP_DIR)) {
        if (!mkdir(TEMP_DIR, 0755, true)) {
            throw new Exception('Failed to create temp directory: ' . TEMP_DIR);
        }
        logMessage('Created temp directory: ' . TEMP_DIR);
    }

    $zip_file = TEMP_DIR . '/package.zip';

    // Download with cURL
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
    logMessage('ZIP file saved to: ' . $zip_file);

    return [
        'success' => true,
        'message' => 'Package downloaded successfully',
        'size' => formatBytes($bytes_written),
        'file' => $zip_file,
        'temp_dir' => TEMP_DIR
    ];
}

/**
 * Extract the downloaded package
 */
function extractPackage() {
    $zip_file = TEMP_DIR . '/package.zip';

    logMessage('Looking for ZIP file: ' . $zip_file);

    if (!file_exists($zip_file)) {
        // Debug: List what's in temp directory
        if (is_dir(TEMP_DIR)) {
            logMessage('Temp directory contents:');
            $files = scandir(TEMP_DIR);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    logMessage('  - ' . $file);
                }
            }
        } else {
            logMessage('Temp directory does not exist: ' . TEMP_DIR);
        }
        throw new Exception('Package file not found: ' . $zip_file);
    }

    logMessage('Extracting package: ' . $zip_file);

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

/**
 * Deploy files to the web directory
 */
function deployFiles() {
    $source_path = TEMP_DIR . '/extracted/' . PACKAGE_DIR;
    $target_path = __DIR__;

    if (!is_dir($source_path)) {
        throw new Exception('Source directory not found');
    }

    logMessage('Deploying files from: ' . $source_path . ' to: ' . $target_path);

    $files_copied = copyDirectory($source_path, $target_path);

    logMessage('Files deployed successfully: ' . $files_copied . ' files');

    return [
        'success' => true,
        'message' => 'Files deployed successfully',
        'files_copied' => $files_copied,
        'install_url' => 'install/'
    ];
}

/**
 * Cleanup temporary files and installer
 */
function cleanupInstaller() {
    logMessage('Starting cleanup process');

    if (is_dir(TEMP_DIR)) {
        removeDirectory(TEMP_DIR);
        logMessage('Temporary directory cleaned: ' . TEMP_DIR);
    }

    file_put_contents(__DIR__ . '/.installer_complete', date('Y-m-d H:i:s'));

    logMessage('Cleanup completed successfully');

    return [
        'success' => true,
        'message' => 'Installation completed successfully',
        'redirect_url' => 'install/',
        'cleanup_complete' => true
    ];
}

/**
 * Copy directory recursively
 */
function copyDirectory($source, $destination) {
    $files_copied = 0;

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            if (basename($item) !== 'installer.php' && !strpos(basename($item), 'installer-')) {
                copy($item, $target);
                $files_copied++;
            }
        }
    }

    return $files_copied;
}

/**
 * Remove directory recursively
 */
function removeDirectory($directory) {
    if (!is_dir($directory)) {
        return false;
    }

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

/**
 * Count files in directory
 */
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

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Log messages
 */
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";

    try {
        $log_dir = dirname(LOG_FILE);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        if (@file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Installer: {$message}");
        }
    } catch (Exception $e) {
        error_log("Installer log failed: {$e->getMessage()} | Original message: {$message}");
    }
}

// Show installer interface if not handling AJAX
showInstallerInterface();

/**
 * Show the installer interface
 */
function showInstallerInterface() {
    try {
        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                throw new Exception('Failed to start PHP session');
            }
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $already_installed = file_exists(__DIR__ . '/.installer_complete') || file_exists(__DIR__ . '/install/index.php');

        try {
            $system_checks = runSystemCheck();
        } catch (Exception $checkError) {
            logMessage('ERROR in runSystemCheck: ' . $checkError->getMessage());
            $system_checks = [
                'error' => [
                    'name' => 'System Check Error',
                    'value' => 'Failed to run compatibility check',
                    'status' => 'ERROR',
                    'message' => 'Error running system check: ' . $checkError->getMessage()
                ]
            ];
        }

        logMessage('Installer interface loaded successfully');

    } catch (Exception $e) {
        logMessage('ERROR in showInstallerInterface: ' . $e->getMessage());

        $already_installed = false;
        $system_checks = [];

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = 'fallback_' . md5(time() . rand());
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlowHost Contact Form System - Fixed Installer</title>
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            max-width: 900px;
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

        .fix-notice {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 16px;
            margin: 16px 32px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        .system-check-container {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 32px;
        }

        .system-check-container h2 {
            margin: 0 0 20px 0;
            color: #1a202c;
            font-size: 20px;
            font-weight: 600;
        }

        .system-checks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        .check-item.warning {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .check-item.info {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .check-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .check-details {
            flex: 1;
        }

        .check-name {
            font-weight: 600;
            color: #1a202c;
            font-size: 14px;
        }

        .check-value {
            color: #4b5563;
            font-size: 13px;
            font-family: 'Monaco', 'Menlo', monospace;
        }

        .check-message {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }

        .system-summary {
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .summary-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #d97706;
        }

        .summary-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        .installer-content {
            padding: 40px;
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
            transition: all 0.2s ease;
            margin: 16px 0;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .install-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(66, 153, 225, 0.3);
        }

        .install-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .button-content,
        .button-loader {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .button-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .install-button.loading .button-content {
            opacity: 0;
        }

        .install-button.loading .button-loader {
            display: flex !important;
        }

        .install-button.loading {
            pointer-events: none;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin: 16px 0;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%);
            border-radius: 6px;
            transition: width 0.3s ease;
            width: 0%;
        }
        </style>
    </head>
    <body>
        <div class="installer-container">
            <div class="installer-header">
                <h1>🚀 GlowHost Contact Form System</h1>
                <p>Fixed Installer v<?php echo INSTALLER_VERSION; ?> - Persistent Temp Directory</p>
            </div>

            <div class="fix-notice">
                ✅ <strong>Fixed:</strong> This version uses persistent temp directories to prevent AJAX session issues
            </div>

            <div class="system-check-container">
                <h2>🔍 System Compatibility Check</h2>

                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Status Indicators:</div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; font-size: 13px;">
                        <div>✅ <strong style="color: #16a34a;">Green:</strong> Requirement met - ready to go!</div>
                        <div>❌ <strong style="color: #dc2626;">Red:</strong> Critical error - must fix to install</div>
                        <div>⚠️ <strong style="color: #d97706;">Yellow:</strong> Warning - installation can proceed</div>
                        <div>ℹ️ <strong style="color: #2563eb;">Blue:</strong> Information only - no action needed</div>
                    </div>
                </div>

                <div class="system-checks">
                    <?php if (!empty($system_checks) && is_array($system_checks)): ?>
                        <?php foreach ($system_checks as $check): ?>
                        <div class="check-item <?php echo strtolower($check['status']); ?>">
                            <div class="check-icon">
                                <?php if ($check['status'] === 'OK'): ?>
                                    ✅
                                <?php elseif ($check['status'] === 'ERROR'): ?>
                                    ❌
                                <?php elseif ($check['status'] === 'WARNING'): ?>
                                    ⚠️
                                <?php else: ?>
                                    ℹ️
                                <?php endif; ?>
                            </div>
                            <div class="check-details">
                                <div class="check-name"><?php echo htmlspecialchars($check['name']); ?></div>
                                <div class="check-value"><?php echo htmlspecialchars($check['value']); ?></div>
                                <div class="check-message"><?php echo htmlspecialchars($check['message']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="check-item error">
                            <div class="check-icon">❌</div>
                            <div class="check-details">
                                <div class="check-name">System Check Failed</div>
                                <div class="check-value">Unable to run compatibility check</div>
                                <div class="check-message">Please check PHP error logs for details</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                $has_errors = false;
                $has_warnings = false;
                if (!empty($system_checks) && is_array($system_checks)) {
                    foreach ($system_checks as $check) {
                        if ($check['status'] === 'ERROR') $has_errors = true;
                        if ($check['status'] === 'WARNING') $has_warnings = true;
                    }
                } else {
                    $has_errors = true;
                }
                ?>

                <div class="system-summary">
                    <?php if ($has_errors): ?>
                        <div class="summary-error">
                            <strong>❌ Installation Blocked</strong><br>
                            Critical system requirements are not met. Please fix the errors above before proceeding.
                        </div>
                    <?php elseif ($has_warnings): ?>
                        <div class="summary-warning">
                            <strong>⚠️ Warnings Detected</strong><br>
                            Installation can proceed but some warnings should be addressed.
                        </div>
                    <?php else: ?>
                        <div class="summary-success">
                            <strong>✅ System Compatible</strong><br>
                            All requirements are met. Ready to install!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="installer-content">
                <?php if ($already_installed): ?>
                    <div style="background: #fffbeb; border: 1px solid #f6e05e; color: #744210; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3>⚠️ System Already Installed</h3>
                        <p>The contact form system appears to be already installed.</p>
                        <br>
                        <a href="install/" style="color: #1a365d; font-weight: 600;">→ Access Installation Wizard</a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center;">
                        <h2>Ready to Install</h2>
                        <p>This installer uses persistent temp directories to prevent AJAX session issues.</p>

                        <?php
                        $can_install = true;
                        if (!empty($system_checks) && is_array($system_checks)) {
                            foreach ($system_checks as $check) {
                                if ($check['status'] === 'ERROR') {
                                    $can_install = false;
                                    break;
                                }
                            }
                        } else {
                            $can_install = false;
                        }
                        ?>

                        <div class="progress-bar" id="progress-bar">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>

                        <button class="install-button" id="install-button"
                                onclick="startInstallation()"
                                <?php echo $can_install ? '' : 'disabled'; ?>>
                            <span class="button-content">
                                <span class="button-icon"><?php echo $can_install ? '🚀' : '❌'; ?></span>
                                <span class="button-text">
                                    <?php echo $can_install ? 'Install Contact Form System (Fixed)' : 'Cannot Install - Fix Errors Above'; ?>
                                </span>
                            </span>
                            <span class="button-loader" style="display: none;">
                                <span class="spinner"></span>
                                <span class="loading-text">Installing...</span>
                            </span>
                        </button>

                        <div id="status-messages"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        let currentStep = 0;
        const steps = ['check', 'download', 'extract', 'deploy', 'cleanup'];
        const stepNames = ['Checking Requirements', 'Downloading Package', 'Extracting Files', 'Deploying System', 'Completing Installation'];

        async function startInstallation() {
            const button = document.getElementById('install-button');
            const progressBar = document.getElementById('progress-bar');
            const progressFill = document.getElementById('progress-fill');

            button.classList.add('loading');
            button.disabled = true;
            progressBar.style.display = 'block';

            try {
                for (let i = 0; i < steps.length; i++) {
                    currentStep = i;
                    showStatus(`${stepNames[i]}...`, 'info');

                    await runInstallationStep(steps[i]);

                    const progress = ((i + 1) / steps.length) * 100;
                    progressFill.style.width = progress + '%';

                    showStatus(`${stepNames[i]} completed!`, 'success');
                }

                showStatus('🎉 Installation completed successfully! Redirecting...', 'success');

                setTimeout(() => {
                    window.location.href = 'install/';
                }, 3000);

            } catch (error) {
                console.error('Installation error:', error);
                showStatus('❌ Installation failed: ' + error.message, 'error');
                button.classList.remove('loading');
                button.disabled = false;
            }
        }

        async function runInstallationStep(step) {
            try {
                const response = await fetch(`?action=install&step=${step}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'csrf_token=<?php echo $_SESSION['csrf_token']; ?>'
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server Response Error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const responseText = await response.text();
                    console.error('Non-JSON Response:', responseText);
                    throw new Error('Server returned non-JSON response');
                }

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || 'Unknown error occurred');
                }

                return result;

            } catch (error) {
                console.error(`Error in step ${step}:`, error);
                throw error;
            }
        }

        function showStatus(message, type) {
            const container = document.getElementById('status-messages');
            const statusDiv = document.createElement('div');

            let bgColor, borderColor, textColor;
            switch(type) {
                case 'success':
                    bgColor = '#f0fdf4'; borderColor = '#bbf7d0'; textColor = '#16a34a';
                    break;
                case 'error':
                    bgColor = '#fef2f2'; borderColor = '#fecaca'; textColor = '#dc2626';
                    break;
                default:
                    bgColor = '#f0f9ff'; borderColor = '#bae6fd'; textColor = '#2563eb';
            }

            statusDiv.style.cssText = `background: ${bgColor}; border: 1px solid ${borderColor}; color: ${textColor}; padding: 12px; border-radius: 8px; margin: 8px 0; text-align: center; font-weight: 500;`;
            statusDiv.innerHTML = message;
            container.appendChild(statusDiv);

            // Remove old messages
            const messages = container.children;
            if (messages.length > 3) {
                container.removeChild(messages[0]);
            }
        }
        </script>
    </body>
    </html>
    <?php
}
?>
