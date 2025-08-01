<?php
/**
 * GlowHost Contact Form System - One-Click Installer
 * Version: 1.5 - With Comprehensive Cleanup
 */

// Configuration
define('INSTALLER_VERSION', '1.5');
define('PACKAGE_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('PACKAGE_DIR', 'contact-form-sales-main');
define('LOG_FILE', __DIR__ . '/installer.log');
define('TESTING_MODE', true);

// PHP Settings
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
define('TEMP_DIR', __DIR__ . '/installer_temp_' . session_id());

/**
 * Remove directory recursively
 */
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

/**
 * COMPREHENSIVE CLEANUP FUNCTION
 */
function comprehensiveCleanup() {
    $items_removed = 0;
    $cleanup_log = [];

    // Installation markers and state files
    $markers = [
        '.installer_complete',
        '.env.local',
        'next-env.d.ts',
        'tsconfig.tsbuildinfo'
    ];

    foreach ($markers as $marker) {
        $path = __DIR__ . '/' . $marker;
        if (file_exists($path)) {
            unlink($path);
            $items_removed++;
            $cleanup_log[] = "Removed marker: $marker";
        }
    }

    // Installation directories
    $directories = [
        'install',
        'src',
        'config',
        'api',
        'scripts',
        'Contact-Form-Sales',
        'contact-form-sales',
        '.next',
        'node_modules',
        'out'
    ];

    foreach ($directories as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (is_dir($path)) {
            removeDirectory($path);
            $items_removed++;
            $cleanup_log[] = "Removed directory: $dir";
        }
    }

    // Configuration files
    $config_files = [
        'package.json',
        'package-lock.json',
        'next.config.js',
        'tsconfig.json',
        'tailwind.config.ts',
        'biome.json',
        'eslint.config.mjs',
        'components.json',
        'postcss.config.mjs',
        'netlify.toml',
        'bun.lock',
        '.gitignore'
    ];

    foreach ($config_files as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            unlink($path);
            $items_removed++;
            $cleanup_log[] = "Removed config: $file";
        }
    }

    // Documentation files
    $docs = [
        'README.md',
        'START-HERE-AI.md',
        'DEPLOYMENT.md',
        'DATABASE_INTEGRATION.md'
    ];

    foreach ($docs as $doc) {
        $path = __DIR__ . '/' . $doc;
        if (file_exists($path)) {
            unlink($path);
            $items_removed++;
            $cleanup_log[] = "Removed doc: $doc";
        }
    }

    // Debug and test files
    $debug_files = [
        'installer-debug.php',
        'installer-fixed.php',
        'installer-reset.php',
        'installer-reset-clean.php',
        'line-97-debug.php',
        'installer-ajax-debug.php',
        'webhook-deploy-secure.php',
        'webhook-htaccess-security.txt'
    ];

    foreach ($debug_files as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            unlink($path);
            $items_removed++;
            $cleanup_log[] = "Removed debug file: $file";
        }
    }

    // Temp directories
    $temp_dirs = glob(__DIR__ . '/installer_temp_*');
    foreach ($temp_dirs as $temp_dir) {
        if (is_dir($temp_dir)) {
            removeDirectory($temp_dir);
            $items_removed++;
            $cleanup_log[] = "Removed temp: " . basename($temp_dir);
        }
    }

    // Log files
    $logs = ['installer.log', 'reset.log', 'error.log'];
    foreach ($logs as $log) {
        $path = __DIR__ . '/' . $log;
        if (file_exists($path)) {
            unlink($path);
            $items_removed++;
            $cleanup_log[] = "Removed log: $log";
        }
    }

    // Archives
    $archives = glob(__DIR__ . '/*.zip');
    foreach ($archives as $archive) {
        unlink($archive);
        $items_removed++;
        $cleanup_log[] = "Removed archive: " . basename($archive);
    }

    // Optional: Remove .htaccess if it was created during installation
    $htaccess = __DIR__ . '/.htaccess';
    if (file_exists($htaccess)) {
        // Only remove if it looks like it was created by our installer
        $content = file_get_contents($htaccess);
        if (strpos($content, 'RewriteEngine') !== false || strpos($content, 'Next.js') !== false) {
            unlink($htaccess);
            $items_removed++;
            $cleanup_log[] = "Removed installer .htaccess";
        }
    }

    return [
        'items_removed' => $items_removed,
        'cleanup_log' => $cleanup_log
    ];
}

/**
 * Testing functions with comprehensive cleanup
 */
if (TESTING_MODE) {
    // Enhanced reset with comprehensive cleanup
    if (isset($_GET['action']) && $_GET['action'] === 'reset') {
        $result = comprehensiveCleanup();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Comprehensive cleanup complete: {$result['items_removed']} items removed",
            'items_removed' => $result['items_removed'],
            'cleanup_log' => $result['cleanup_log']
        ]);
        exit;
    }

    // Nuclear option - complete filesystem reset
    if (isset($_GET['action']) && $_GET['action'] === 'nuclear_reset') {
        $result = comprehensiveCleanup();

        // Also remove any remaining files that might be installation-related
        $all_files = scandir(__DIR__);
        $protected_files = ['.', '..', 'installer.php', '.same', '.well-known'];
        $additional_removed = 0;

        foreach ($all_files as $file) {
            if (!in_array($file, $protected_files)) {
                $path = __DIR__ . '/' . $file;
                if (is_file($path)) {
                    // Be extra careful - only remove known file types
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $safe_extensions = ['php', 'js', 'json', 'md', 'txt', 'yml', 'yaml', 'log', 'zip'];
                    if (in_array($ext, $safe_extensions)) {
                        unlink($path);
                        $additional_removed++;
                        $result['cleanup_log'][] = "Nuclear: removed $file";
                    }
                } elseif (is_dir($path)) {
                    removeDirectory($path);
                    $additional_removed++;
                    $result['cleanup_log'][] = "Nuclear: removed directory $file";
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Nuclear reset complete: " . ($result['items_removed'] + $additional_removed) . " total items removed",
            'items_removed' => $result['items_removed'] + $additional_removed,
            'cleanup_log' => $result['cleanup_log']
        ]);
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'clear_logs') {
        $logs_cleared = 0;
        $log_files = ['installer.log', 'reset.log', 'error.log'];
        foreach ($log_files as $log_file) {
            $path = __DIR__ . '/' . $log_file;
            if (file_exists($path)) {
                unlink($path);
                $logs_cleared++;
            }
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Cleared {$logs_cleared} log files"
        ]);
        exit;
    }
}

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
        'message' => $php_ok ? 'Compatible' : 'Requires PHP 7.4 - 8.2'
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

// Handle AJAX requests
$get_action = isset($_GET['action']) ? $_GET['action'] : '';
$post_action = isset($_POST['action']) ? $_POST['action'] : '';
if ($get_action === 'install' || $post_action === 'install') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

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

    logMessage('FIXED DEPLOYMENT: Deploying files from ' . $source_path . ' directly to web root: ' . $target_path);

    $files_copied = 0;
    $items = scandir($source_path);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $source_item = $source_path . '/' . $item;
        $target_item = $target_path . '/' . $item;

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

    logMessage('FIXED DEPLOYMENT: Files deployed successfully - ' . $files_copied . ' files copied directly to web root');

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

// Show installer interface
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
        .cleanup-notice {
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
        .installer-content { padding: 40px; }
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
            position: relative;
            overflow: hidden;
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
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        .testing-tools {
            margin-top: 40px;
            padding: 20px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            border-radius: 8px;
        }
        .test-button {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 8px 8px 0;
        }
        .test-button.nuclear {
            background: #dc2626;
            font-weight: bold;
        }
        .test-button.secondary {
            background: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>🚀 GlowHost Contact Form System</h1>
            <p>Professional One-Click Installer v<?php echo INSTALLER_VERSION; ?></p>
        </div>

        <div class="cleanup-notice">
            ✅ <strong>ENHANCED:</strong> Built-in comprehensive filesystem cleanup - no more manual commands!
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
                    <a href="install/" style="color: #1a365d; font-weight: 600;">→ Access Installation Wizard</a>
                    <br><br>
                    <p><strong>For testing:</strong> Use the "Comprehensive Reset" button below to clean everything and start fresh.</p>
                </div>
            <?php else: ?>
                <div style="text-align: center;">
                    <h2>Ready to Install</h2>
                    <p>This installer deploys files directly to the web root with built-in comprehensive cleanup.</p>

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

                    <div id="status-messages"></div>
                </div>
            <?php endif; ?>

            <?php if (TESTING_MODE): ?>
            <div class="testing-tools">
                <h3>🧪 Enhanced Testing Tools</h3>
                <p><strong>Comprehensive cleanup built-in:</strong> No more manual command-line cleanup needed!</p>
                <button onclick="comprehensiveReset()" class="test-button">🔄 Comprehensive Reset</button>
                <button onclick="nuclearReset()" class="test-button nuclear">💥 Nuclear Reset</button>
                <button onclick="clearLogs()" class="test-button secondary">📝 Clear Logs</button>
            </div>
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

            showStatus('🎉 Installation completed successfully! Redirecting...', 'success');
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

        let bgColor = type === 'success' ? '#f0fdf4' : type === 'error' ? '#fef2f2' : '#f0f9ff';
        let borderColor = type === 'success' ? '#bbf7d0' : type === 'error' ? '#fecaca' : '#bae6fd';
        let textColor = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#2563eb';

        statusDiv.style.cssText = `background: ${bgColor}; border: 1px solid ${borderColor}; color: ${textColor}; padding: 12px; border-radius: 8px; margin: 8px 0; text-align: center; font-weight: 500;`;
        statusDiv.innerHTML = message;
        container.appendChild(statusDiv);

        if (container.children.length > 5) {
            container.removeChild(container.firstChild);
        }
    }

    <?php if (TESTING_MODE): ?>
    async function comprehensiveReset() {
        if (!confirm("🔄 This will remove ALL installation files, configs, logs, and artifacts. Continue?")) return;
        showStatus("🧹 Running comprehensive reset...", "info");
        try {
            const response = await fetch("?action=reset");
            const result = await response.json();
            if (result.success) {
                showStatus(`✅ ${result.message}`, "success");
                if (result.cleanup_log) {
                    result.cleanup_log.slice(0, 5).forEach(log => {
                        showStatus(`• ${log}`, "info");
                    });
                }
                setTimeout(() => location.reload(), 3000);
            }
        } catch (error) {
            showStatus("❌ Reset error: " + error.message, "error");
        }
    }

    async function nuclearReset() {
        if (!confirm("💥 NUCLEAR RESET: This will remove EVERYTHING except installer.php, .same/, and .well-known/. This is the most aggressive cleanup possible. Are you absolutely sure?")) return;
        showStatus("💥 Running nuclear reset...", "info");
        try {
            const response = await fetch("?action=nuclear_reset");
            const result = await response.json();
            if (result.success) {
                showStatus(`✅ ${result.message}`, "success");
                if (result.cleanup_log) {
                    result.cleanup_log.slice(0, 5).forEach(log => {
                        showStatus(`• ${log}`, "info");
                    });
                }
                setTimeout(() => location.reload(), 4000);
            }
        } catch (error) {
            showStatus("❌ Nuclear reset error: " + error.message, "error");
        }
    }

    function clearLogs() {
        if (!confirm("📝 Clear all installer logs?")) return;
        fetch("?action=clear_logs")
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showStatus("✅ " + result.message, "success");
                }
            });
    }
    <?php endif; ?>
    </script>
</body>
</html>
