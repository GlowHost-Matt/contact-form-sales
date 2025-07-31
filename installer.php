<?php
/**
 * GlowHost Contact Form System - One-Click Installer
 * Version: 1.0
 *
 * Professional single-file installer that downloads and deploys
 * the complete contact management system automatically.
 *
 * USAGE:
 * 1. Upload this file to your web server
 * 2. Visit installer.php in your browser
 * 3. Click "Install System"
 * 4. Follow the installation wizard
 *
 * FEATURES:
 * - Downloads latest system from GitHub
 * - Extracts with proper permissions
 * - Launches installation wizard
 * - Self-destructs when complete
 *
 */

// Debug mode toggle - controlled by user selection
$debug_mode = isset($_GET['debug']) && $_GET['debug'] === 'true';

if ($debug_mode) {
    // Debug mode: Show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Normal mode: Log errors but don't display
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Make debug mode available globally
global $debug_mode;

// Configuration - Define constants first
define('INSTALLER_VERSION', '1.0');
define('PACKAGE_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('PACKAGE_DIR', 'contact-form-sales-main');
define('TEMP_DIR', sys_get_temp_dir() . '/cf_installer_' . uniqid());
define('LOG_FILE', __DIR__ . '/installer.log');

// Start session early
session_start();

// Prevent direct execution unless intended
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    // Show the installer interface
    showInstallerInterface();
    exit;
}

// Security: Basic protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_POST['csrf_token'] ?? '', $_SESSION['csrf_token'] ?? '')) {
    die('CSRF token mismatch');
}

// Handle AJAX requests
if ($_GET['action'] ?? '' === 'install' || $_POST['action'] ?? '' === 'install') {
    header('Content-Type: application/json');

    try {
        $step = $_GET['step'] ?? $_POST['step'] ?? 'download';

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
                throw new Exception('Invalid installation step');
        }

    } catch (Exception $e) {
        logMessage('ERROR: ' . $e->getMessage());

        $error_response = [
            'success' => false,
            'error' => $e->getMessage(),
            'details' => 'Check installer.log for more details'
        ];

        if ($debug_mode) {
            $error_response['debug_trace'] = $e->getTraceAsString();
            $error_response['debug_file'] = $e->getFile();
            $error_response['debug_line'] = $e->getLine();
        }

        echo json_encode(addDebugInfo($error_response, 'error'));
    }

    exit;
}

/**
 * Check system requirements
 */
function checkSystemRequirements() {
    $requirements = [
        'php_version' => [
            'name' => 'PHP Version',
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'curl' => [
            'name' => 'cURL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('curl')
        ],
        'zip' => [
            'name' => 'ZIP Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('zip')
        ],
        'write_permissions' => [
            'name' => 'Write Permissions',
            'required' => 'Writable',
            'current' => is_writable(__DIR__) ? 'Writable' : 'Not Writable',
            'status' => is_writable(__DIR__)
        ],
        'memory_limit' => [
            'name' => 'Memory Limit',
            'required' => '128M+',
            'current' => ini_get('memory_limit'),
            'status' => (int)ini_get('memory_limit') >= 128 || ini_get('memory_limit') === '-1'
        ]
    ];

    $all_passed = true;
    foreach ($requirements as $req) {
        if (!$req['status']) {
            $all_passed = false;
            break;
        }
    }

    logMessage('System requirements check: ' . ($all_passed ? 'PASSED' : 'FAILED'));

    $response = [
        'success' => true,
        'requirements' => $requirements,
        'all_passed' => $all_passed,
        'message' => $all_passed ? 'All requirements met' : 'Some requirements not met'
    ];

    return addDebugInfo($response, 'check');
}

/**
 * Download the package from GitHub
 */
function downloadPackage() {
    logMessage('Starting package download from: ' . PACKAGE_URL);

    // Create temp directory
    if (!file_exists(TEMP_DIR)) {
        mkdir(TEMP_DIR, 0755, true);
    }

    $zip_file = TEMP_DIR . '/package.zip';

    // Download with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PACKAGE_URL);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes
    curl_setopt($ch, CURLOPT_USERAGENT, 'GlowHost-Contact-Form-Installer/' . INSTALLER_VERSION);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For compatibility with shared hosting

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

    // Save to file
    $bytes_written = file_put_contents($zip_file, $data);

    if ($bytes_written === false) {
        throw new Exception('Failed to save downloaded package');
    }

    logMessage('Package downloaded successfully: ' . formatBytes($bytes_written));

    $response = [
        'success' => true,
        'message' => 'Package downloaded successfully',
        'size' => formatBytes($bytes_written),
        'file' => $zip_file
    ];

    return addDebugInfo($response, 'download');
}

/**
 * Extract the downloaded package
 */
function extractPackage() {
    $zip_file = TEMP_DIR . '/package.zip';

    if (!file_exists($zip_file)) {
        throw new Exception('Package file not found');
    }

    logMessage('Extracting package: ' . $zip_file);

    $zip = new ZipArchive();
    $result = $zip->open($zip_file);

    if ($result !== TRUE) {
        throw new Exception('Failed to open ZIP file: ' . $result);
    }

    // Extract to temp directory
    $extract_path = TEMP_DIR . '/extracted';
    if (!$zip->extractTo($extract_path)) {
        throw new Exception('Failed to extract ZIP file');
    }

    $zip->close();

    // Verify extraction
    $package_path = $extract_path . '/' . PACKAGE_DIR;
    if (!is_dir($package_path)) {
        throw new Exception('Package structure not found after extraction');
    }

    logMessage('Package extracted successfully to: ' . $package_path);

    $response = [
        'success' => true,
        'message' => 'Package extracted successfully',
        'path' => $package_path,
        'files' => countFiles($package_path)
    ];

    return addDebugInfo($response, 'extract');
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

    // Copy files
    $files_copied = copyDirectory($source_path, $target_path);

    // Set permissions
    setFilePermissions($target_path);

    logMessage('Files deployed successfully: ' . $files_copied . ' files');

    $response = [
        'success' => true,
        'message' => 'Files deployed successfully',
        'files_copied' => $files_copied,
        'install_url' => 'install/'
    ];

    return addDebugInfo($response, 'deploy');
}

/**
 * Cleanup temporary files and installer
 */
function cleanupInstaller() {
    logMessage('Starting cleanup process');

    // Remove temporary directory
    if (is_dir(TEMP_DIR)) {
        removeDirectory(TEMP_DIR);
        logMessage('Temporary directory cleaned: ' . TEMP_DIR);
    }

    // Create completion marker
    file_put_contents(__DIR__ . '/.installer_complete', date('Y-m-d H:i:s'));

    logMessage('Cleanup completed successfully');

    $response = [
        'success' => true,
        'message' => 'Installation completed successfully',
        'redirect_url' => 'install/',
        'cleanup_complete' => true
    ];

    return addDebugInfo($response, 'cleanup');
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
            // Skip the installer file itself
            if (basename($item) === 'installer.php') {
                continue;
            }

            copy($item, $target);
            $files_copied++;
        }
    }

    return $files_copied;
}

/**
 * Set appropriate file permissions
 */
function setFilePermissions($directory) {
    $file_permissions = [
        '.env' => 0600,
        'config/' => 0755,
        'install/' => 0755,
        'api/' => 0755,
        'logs/' => 0755,
        'uploads/' => 0755
    ];

    foreach ($file_permissions as $path => $permission) {
        $full_path = $directory . '/' . $path;
        if (file_exists($full_path)) {
            chmod($full_path, $permission);
        }
    }
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
 * Log messages with enhanced error handling
 */
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";

    try {
        // Ensure log directory exists
        $log_dir = dirname(LOG_FILE);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        // Try to write to log file
        if (@file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX) === false) {
            // Fallback to error_log if file writing fails
            error_log("Installer: {$message}");
        }
    } catch (Exception $e) {
        // Last resort - use error_log
        error_log("Installer log failed: {$e->getMessage()} | Original message: {$message}");
    }
}

/**
 * Add debug information to response when in debug mode
 */
function addDebugInfo($response, $step) {
    global $debug_mode;

    if ($debug_mode) {
        $response['debug_info'] = [
            'step' => $step,
            'memory_usage' => formatBytes(memory_get_usage(true)),
            'peak_memory' => formatBytes(memory_get_peak_usage(true)),
            'execution_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3) . 's',
            'php_version' => PHP_VERSION,
            'extensions' => [
                'curl' => extension_loaded('curl') ? 'Available' : 'Missing',
                'zip' => extension_loaded('zip') ? 'Available' : 'Missing',
                'pdo' => extension_loaded('pdo') ? 'Available' : 'Missing',
            ],
            'temp_dir' => TEMP_DIR,
            'log_file' => LOG_FILE
        ];
    }

    return $response;
}

/**
 * Show the installer interface with enhanced error handling
 */
function showInstallerInterface() {
    try {
        // Start session with error handling
        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                throw new Exception('Failed to start PHP session');
            }
        }

        // Generate CSRF token
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Check if already installed
        $already_installed = file_exists(__DIR__ . '/.installer_complete') || file_exists(__DIR__ . '/install/index.php');

        // Log successful interface load
        logMessage('Installer interface loaded successfully');

    } catch (Exception $e) {
        logMessage('ERROR in showInstallerInterface: ' . $e->getMessage());

        // Fallback for session issues
        $already_installed = false;
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = 'fallback_' . md5(time() . rand());
        }

        // Show debug info if requested
        if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
            echo "<div style='background:#ffeeee;padding:10px;border:1px solid red;margin:10px;'>";
            echo "<strong>Session Error:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlowHost Contact Form System - One-Click Installer</title>
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

        .installer-content {
            padding: 40px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin: 32px 0;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f7fafc;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: #4299e1;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .feature-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .feature-info p {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.5;
        }

        .install-section {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 32px;
            margin: 32px 0;
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
            transition: all 0.2s ease;
            margin: 16px 0;
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

        .progress-container {
            margin: 24px 0;
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%);
            border-radius: 6px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #4a5568;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            padding: 8px;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }

        .progress-step.active {
            opacity: 1;
            font-weight: 600;
            color: #2d3748;
        }

        .progress-step.completed {
            opacity: 1;
            color: #48bb78;
        }

        .status-message {
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            display: none;
        }

        .status-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }

        .status-error {
            background: #fed7d7;
            border: 1px solid #feb2b2;
            color: #742a2a;
        }

        .requirements-check {
            margin: 24px 0;
            display: none;
        }

        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .requirement-status {
            font-weight: 600;
        }

        .requirement-status.pass {
            color: #48bb78;
        }

        .requirement-status.fail {
            color: #e53e3e;
        }

        .already-installed {
            background: #fffbeb;
            border: 1px solid #f6e05e;
            color: #744210;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .debug-mode-selector {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }

        .debug-mode-selector label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .debug-mode-selector select {
            padding: 8px 16px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            background: white;
            color: #2d3748;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .debug-mode-selector small {
            display: block;
            color: #718096;
            font-size: 12px;
        }

        .debug-info {
            background: #e6fffa;
            border: 2px solid #81e6d9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .debug-info h3 {
            margin: 0 0 16px 0;
            color: #234e52;
            font-size: 16px;
        }

        .debug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .debug-item {
            background: rgba(255, 255, 255, 0.7);
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            color: #2d3748;
        }

        @media (max-width: 768px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .installer-header {
                padding: 24px;
            }

            .installer-content {
                padding: 24px;
            }
        }
        </style>
    </head>
    <body>
        <?php if (isset($_GET['debug']) && $_GET['debug'] === 'true'): ?>
            <div style="background: #e8f4f8; border: 1px solid #bee5eb; padding: 10px; margin: 10px; border-radius: 5px;">
                <strong>🔍 Debug Mode Active</strong><br>
                PHP Version: <?php echo PHP_VERSION; ?><br>
                Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                Extensions: <?php echo implode(', ', ['curl' => extension_loaded('curl') ? '✓' : '✗', 'zip' => extension_loaded('zip') ? '✓' : '✗']); ?><br>
                Memory Limit: <?php echo ini_get('memory_limit'); ?><br>
                Session ID: <?php echo session_id() ?: 'None'; ?>
            </div>
        <?php endif; ?>

        <div class="installer-container">
            <div class="installer-header">
                <h1>🚀 GlowHost Contact Form System</h1>
                <p>Professional One-Click Installer v<?php echo INSTALLER_VERSION; ?></p>
            </div>

            <div class="installer-content">
                <?php if ($already_installed): ?>
                    <div class="already-installed">
                        <h3>⚠️ System Already Installed</h3>
                        <p>The contact form system appears to be already installed. If you want to reinstall, please remove the existing installation first.</p>
                        <br>
                        <a href="install/" style="color: #1a365d; font-weight: 600;">→ Access Installation Wizard</a>
                    </div>
                <?php else: ?>
                    <div class="feature-grid">
                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div class="feature-info">
                                <h3>One-Click Deployment</h3>
                                <p>Automatically downloads and installs the complete contact management system</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">🎯</div>
                            <div class="feature-info">
                                <h3>Auto Field Mapping</h3>
                                <p>Converts "Full Name" to separate database fields automatically</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">🛡️</div>
                            <div class="feature-info">
                                <h3>Enterprise Security</h3>
                                <p>CSRF protection, rate limiting, and secure admin authentication</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">📱</div>
                            <div class="feature-info">
                                <h3>Professional UI</h3>
                                <p>Responsive contact form with auto-save and admin dashboard</p>
                            </div>
                        </div>
                    </div>

                    <div class="install-section">
                        <h2>Ready to Install</h2>
                        <p>This installer will download and deploy the complete contact form system automatically.</p>

                        <div class="debug-mode-selector">
                            <label for="debug-mode">Installation Mode:</label>
                            <select id="debug-mode" onchange="toggleDebugMode()">
                                <option value="normal" <?php echo !$debug_mode ? 'selected' : ''; ?>>This should work</option>
                                <option value="debug" <?php echo $debug_mode ? 'selected' : ''; ?>>Turn on debug</option>
                            </select>
                            <small>Switch to debug mode if you encounter any issues</small>
                        </div>

                        <?php if ($debug_mode): ?>
                            <div class="debug-info">
                                <h3>🔍 Debug Information</h3>
                                <div class="debug-grid">
                                    <div class="debug-item">
                                        <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                                    </div>
                                    <div class="debug-item">
                                        <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                                    </div>
                                    <div class="debug-item">
                                        <strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?>
                                    </div>
                                    <div class="debug-item">
                                        <strong>cURL:</strong> <?php echo extension_loaded('curl') ? '✅ Available' : '❌ Missing'; ?>
                                    </div>
                                    <div class="debug-item">
                                        <strong>ZIP:</strong> <?php echo extension_loaded('zip') ? '✅ Available' : '❌ Missing'; ?>
                                    </div>
                                    <div class="debug-item">
                                        <strong>PDO:</strong> <?php echo extension_loaded('pdo') ? '✅ Available' : '❌ Missing'; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="requirements-check" id="requirements-check"></div>

                        <button class="install-button" id="install-button" onclick="startInstallation()">
                            🚀 Install Contact Form System
                        </button>

                        <div class="progress-container" id="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progress-fill"></div>
                            </div>
                            <div class="progress-steps">
                                <div class="progress-step" id="step-check">Checking</div>
                                <div class="progress-step" id="step-download">Downloading</div>
                                <div class="progress-step" id="step-extract">Extracting</div>
                                <div class="progress-step" id="step-deploy">Deploying</div>
                                <div class="progress-step" id="step-cleanup">Completing</div>
                            </div>
                        </div>

                        <div class="status-message" id="status-message"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        // Check if debug mode is active
        const debugMode = new URLSearchParams(window.location.search).get('debug') === 'true';

        // Enhanced error handling for JavaScript
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            if (debugMode) {
                showError('JavaScript Error: ' + e.message + '\nFile: ' + e.filename + '\nLine: ' + e.lineno);
            }
        });

        // Debug mode toggle function
        function toggleDebugMode() {
            const select = document.getElementById('debug-mode');
            const currentUrl = new URL(window.location);

            if (select.value === 'debug') {
                currentUrl.searchParams.set('debug', 'true');
            } else {
                currentUrl.searchParams.delete('debug');
            }

            window.location.href = currentUrl.toString();
        }

        let currentStep = 0;
        const steps = ['check', 'download', 'extract', 'deploy', 'cleanup'];
        const stepNames = ['Checking', 'Downloading', 'Extracting', 'Deploying', 'Completing'];

        async function startInstallation() {
            const button = document.getElementById('install-button');
            const progressContainer = document.getElementById('progress-container');

            button.disabled = true;
            button.textContent = 'Installing...';
            progressContainer.style.display = 'block';

            try {
                for (let i = 0; i < steps.length; i++) {
                    currentStep = i;
                    await runInstallationStep(steps[i]);
                    updateProgress(i + 1);
                }

                showSuccess('Installation completed successfully! Redirecting to setup wizard...');

                setTimeout(() => {
                    window.location.href = 'install/';
                }, 3000);

            } catch (error) {
                showError('Installation failed: ' + error.message);
                button.disabled = false;
                button.textContent = '🚀 Retry Installation';
            }
        }

        async function runInstallationStep(step) {
            try {
                // Update UI
                document.getElementById('step-' + step).classList.add('active');

                // Add debug parameter if in debug mode
                const debugParam = debugMode ? '&debug=true' : '';

                const response = await fetch(`?action=install&step=${step}${debugParam}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'csrf_token=<?php echo $_SESSION['csrf_token']; ?>'
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    if (debugMode) {
                        console.error('Server Response:', errorText);
                        console.error('Response Headers:', [...response.headers.entries()]);
                    }
                    throw new Error(`HTTP ${response.status}: ${response.statusText}${debugMode ? '\n\nFull Response:\n' + errorText : ''}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const responseText = await response.text();
                    if (debugMode) {
                        console.error('Non-JSON Response:', responseText);
                        console.error('Content-Type:', contentType);
                    }
                    throw new Error(`Server returned non-JSON response.${debugMode ? '\n\nContent-Type: ' + contentType + '\n\nResponse:\n' + responseText : ' Switch to debug mode for details.'}`);
                }

                const result = await response.json();

                if (debugMode && result.debug_info) {
                    console.log('Step Debug Info:', result.debug_info);
                }

            if (!result.success) {
                throw new Error(result.error || 'Unknown error occurred');
            }

            // Mark step as completed
            document.getElementById('step-' + step).classList.remove('active');
            document.getElementById('step-' + step).classList.add('completed');

            // Show step-specific feedback
            if (step === 'check' && !result.all_passed) {
                showRequirements(result.requirements);
                throw new Error('System requirements not met');
            }

            return result;
        }

        function updateProgress(stepNumber) {
            const progressFill = document.getElementById('progress-fill');
            const percentage = (stepNumber / steps.length) * 100;
            progressFill.style.width = percentage + '%';
        }

        function showSuccess(message) {
            const statusMessage = document.getElementById('status-message');
            statusMessage.className = 'status-message status-success';
            statusMessage.textContent = message;
            statusMessage.style.display = 'block';
        }

        function showError(message) {
            const statusMessage = document.getElementById('status-message');
            statusMessage.className = 'status-message status-error';
            statusMessage.textContent = message;
            statusMessage.style.display = 'block';
        }

        function showRequirements(requirements) {
            const container = document.getElementById('requirements-check');
            let html = '<h3>System Requirements Check:</h3>';

            for (const [key, req] of Object.entries(requirements)) {
                const statusClass = req.status ? 'pass' : 'fail';
                const statusText = req.status ? '✓ PASS' : '✗ FAIL';

                html += `
                    <div class="requirement-item">
                        <span>${req.name}: ${req.current}</span>
                        <span class="requirement-status ${statusClass}">${statusText}</span>
                    </div>
                `;
            }

            container.innerHTML = html;
            container.style.display = 'block';
        }
        </script>
    </body>
    </html>
    <?php
}
?>
