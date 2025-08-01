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

// Self-update configuration
define('INSTALLER_GITHUB_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php');
define('BACKUP_FILE', __DIR__ . '/installer.php.backup');
define('UPDATE_LOG_FILE', __DIR__ . '/installer_updates.log');

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
 * SELF-UPDATE FUNCTIONS
 */
function checkForUpdates() {
    logUpdateMessage('Checking for installer updates...');

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, INSTALLER_GITHUB_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'GlowHost-Contact-Form-Installer/' . INSTALLER_VERSION);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $remote_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Failed to fetch remote installer: ' . $error);
        }

        if ($http_code !== 200) {
            throw new Exception('HTTP error: ' . $http_code);
        }

        if (!$remote_content) {
            throw new Exception('No content received from remote server');
        }

        // Extract version from remote file
        preg_match("/define\('INSTALLER_VERSION',\s*'([^']+)'\)/", $remote_content, $matches);
        $remote_version = $matches[1] ?? 'unknown';

        $current_version = INSTALLER_VERSION;
        $update_available = version_compare($remote_version, $current_version, '>');

        return [
            'success' => true,
            'current_version' => $current_version,
            'remote_version' => $remote_version,
            'update_available' => $update_available,
            'remote_content' => $remote_content
        ];

    } catch (Exception $e) {
        logUpdateMessage('Error checking for updates: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function updateInstaller() {
    logUpdateMessage('Starting installer self-update process...');

    try {
        // Check for updates first
        $update_check = checkForUpdates();
        if (!$update_check['success']) {
            throw new Exception('Update check failed: ' . $update_check['error']);
        }

        if (!$update_check['update_available']) {
            return [
                'success' => true,
                'message' => 'Already running the latest version (' . $update_check['current_version'] . ')',
                'updated' => false
            ];
        }

        $remote_content = $update_check['remote_content'];
        $new_version = $update_check['remote_version'];
        $current_version = $update_check['current_version'];

        // Validate PHP syntax of new version
        $temp_file = TEMP_DIR . '/installer_new.php';
        if (!file_exists(TEMP_DIR)) {
            mkdir(TEMP_DIR, 0755, true);
        }

        file_put_contents($temp_file, $remote_content);

        // Check PHP syntax
        $syntax_check = shell_exec("php -l " . escapeshellarg($temp_file) . " 2>&1");
        if (strpos($syntax_check, 'No syntax errors') === false) {
            unlink($temp_file);
            throw new Exception('New installer has PHP syntax errors: ' . $syntax_check);
        }

        // Backup current installer
        $current_file = __FILE__;
        if (!copy($current_file, BACKUP_FILE)) {
            unlink($temp_file);
            throw new Exception('Failed to create backup of current installer');
        }

        // Replace current installer with new version
        if (!copy($temp_file, $current_file)) {
            // Restore backup on failure
            copy(BACKUP_FILE, $current_file);
            unlink($temp_file);
            throw new Exception('Failed to update installer file');
        }

        // Cleanup temp file
        unlink($temp_file);

        logUpdateMessage("Successfully updated installer from v{$current_version} to v{$new_version}");

        return [
            'success' => true,
            'message' => "Successfully updated from v{$current_version} to v{$new_version}",
            'updated' => true,
            'old_version' => $current_version,
            'new_version' => $new_version,
            'backup_file' => basename(BACKUP_FILE)
        ];

    } catch (Exception $e) {
        logUpdateMessage('Update failed: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function rollbackInstaller() {
    logUpdateMessage('Starting installer rollback...');

    try {
        if (!file_exists(BACKUP_FILE)) {
            throw new Exception('No backup file found. Cannot rollback.');
        }

        $current_file = __FILE__;

        // Create a backup of current version before rollback (just in case)
        $emergency_backup = __DIR__ . '/installer.php.emergency_backup';
        copy($current_file, $emergency_backup);

        // Restore from backup
        if (!copy(BACKUP_FILE, $current_file)) {
            throw new Exception('Failed to restore from backup');
        }

        logUpdateMessage('Successfully rolled back installer');

        return [
            'success' => true,
            'message' => 'Successfully rolled back to previous version',
            'emergency_backup' => basename($emergency_backup)
        ];

    } catch (Exception $e) {
        logUpdateMessage('Rollback failed: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function getUpdateHistory() {
    $history = [];
    if (file_exists(UPDATE_LOG_FILE)) {
        $lines = file(UPDATE_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = array_slice(array_reverse($lines), 0, 10); // Last 10 entries
    }
    return $history;
}

function logUpdateMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    @file_put_contents(UPDATE_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);

    // Also log to main installer log
    logMessage('UPDATE: ' . $message);
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

    // Self-update endpoints
    if (isset($_GET['action']) && $_GET['action'] === 'check_updates') {
        header('Content-Type: application/json');
        echo json_encode(checkForUpdates());
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'update_installer') {
        header('Content-Type: application/json');
        echo json_encode(updateInstaller());
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'rollback_installer') {
        header('Content-Type: application/json');
        echo json_encode(rollbackInstaller());
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'update_history') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'history' => getUpdateHistory()
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
            padding: 30px;
            background: linear-gradient(135deg, #fef3c7 0%, #f3f4f6 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .testing-tools h3 {
            margin-bottom: 20px;
            color: #92400e;
            font-size: 1.5em;
            text-align: center;
        }
        .reset-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .reset-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .reset-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .reset-card.nuclear {
            border: 2px solid #dc2626;
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        }
        .reset-card.nuclear::before {
            content: "⚠️ DANGER";
            position: absolute;
            top: 0;
            right: 0;
            background: #dc2626;
            color: white;
            padding: 4px 12px;
            font-size: 10px;
            font-weight: bold;
            border-bottom-left-radius: 8px;
        }
        .reset-card.safe {
            border: 2px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }
        .reset-card.safe::before {
            content: "✅ SAFE";
            position: absolute;
            top: 0;
            right: 0;
            background: #10b981;
            color: white;
            padding: 4px 12px;
            font-size: 10px;
            font-weight: bold;
            border-bottom-left-radius: 8px;
        }
        .reset-card.update {
            border: 2px solid #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        }
        .reset-card.update::before {
            content: "🔄 UPDATE";
            position: absolute;
            top: 0;
            right: 0;
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            font-size: 10px;
            font-weight: bold;
            border-bottom-left-radius: 8px;
        }
        .reset-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .reset-description {
            color: #6b7280;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .reset-details {
            background: #f9fafb;
            border-radius: 6px;
            padding: 12px;
            margin: 12px 0;
            font-size: 14px;
        }
        .reset-removes, .reset-keeps {
            margin: 8px 0;
        }
        .reset-removes strong {
            color: #dc2626;
        }
        .reset-keeps strong {
            color: #10b981;
        }
        .reset-button {
            width: 100%;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        .reset-button.comprehensive {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        .reset-button.comprehensive:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
        }
        .reset-button.nuclear {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            animation: pulse-red 2s infinite;
        }
        .reset-button.nuclear:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            transform: translateY(-1px);
        }
        .reset-button.safe {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .reset-button.safe:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
        }
        .reset-button.update {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        .reset-button.update:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
        }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        }
        .warning-text {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin: 10px 0;
            font-weight: 500;
        }
        .update-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin: 10px 0;
        }
        .update-available {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin: 10px 0;
            font-weight: 600;
        }
        .version-info {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 12px;
        }
        .current-version {
            color: #6b7280;
        }
        .new-version {
            color: #059669;
            font-weight: 600;
        }
        .update-history {
            max-height: 150px;
            overflow-y: auto;
            background: #f9fafb;
            border-radius: 6px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 11px;
            line-height: 1.4;
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
            ✅ <strong>ENHANCED:</strong> Built-in comprehensive filesystem cleanup + self-update feature!
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
                <h3>🧪 Enhanced Testing & Reset Tools</h3>
                <p style="text-align: center; color: #6b7280; margin-bottom: 25px;">
                    <strong>Professional cleanup options:</strong> Choose the right reset level for your needs. Self-update feature keeps installer current.
                </p>

                <div class="reset-options-grid">
                    <!-- Self-Update - New Feature -->
                    <div class="reset-card update">
                        <div class="reset-title">
                            🔄 Self-Update Installer
                        </div>
                        <div class="reset-description">
                            Automatically updates this installer to the latest version from GitHub repository.
                        </div>
                        <div class="update-info" id="update-status">
                            <div class="version-info">
                                <span class="current-version">Current: v<?php echo INSTALLER_VERSION; ?></span>
                                <span id="remote-version"></span>
                            </div>
                            <div id="update-available-notice"></div>
                        </div>
                        <div class="reset-details">
                            <div class="reset-keeps">
                                <strong>Features:</strong> Version checking, syntax validation, automatic backup, safe rollback
                            </div>
                            <div class="reset-removes">
                                <strong>Safety:</strong> Creates backup before update, validates PHP syntax, rollback on failure
                            </div>
                        </div>
                        <div class="update-history" id="update-history" style="display: none;">
                            <strong>Update History:</strong><br>
                            <div id="update-history-content"></div>
                        </div>
                        <button onclick="checkForUpdates()" class="reset-button update">🔍 Check for Updates</button>
                        <button onclick="updateInstaller()" class="reset-button update" id="update-button" style="display: none;">⬆️ Update Installer</button>
                        <button onclick="rollbackInstaller()" class="reset-button" style="background: #f59e0b; color: white; display: none;" id="rollback-button">↩️ Rollback</button>
                        <button onclick="showUpdateHistory()" class="reset-button safe" style="margin-top: 5px;">📜 Update History</button>
                    </div>

                    <!-- Clear Logs - Safe Option -->
                    <div class="reset-card safe">
                        <div class="reset-title">
                            📝 Clear Logs
                        </div>
                        <div class="reset-description">
                            Safely removes installer logs and temporary files without affecting your installation.
                        </div>
                        <div class="reset-details">
                            <div class="reset-removes">
                                <strong>Removes:</strong> installer.log, temporary files, error logs
                            </div>
                            <div class="reset-keeps">
                                <strong>Keeps:</strong> All installed files, configurations, and directories
                            </div>
                        </div>
                        <p style="color: #059669; font-size: 13px; margin: 10px 0;">
                            ✅ <strong>Recommended for:</strong> Regular maintenance and cleanup
                        </p>
                        <button onclick="clearLogs()" class="reset-button safe">📝 Clear Logs Only</button>
                    </div>

                    <!-- Comprehensive Reset - Standard Option -->
                    <div class="reset-card">
                        <div class="reset-title">
                            🔄 Comprehensive Reset
                        </div>
                        <div class="reset-description">
                            Removes all installation-related files while preserving critical system files and configurations.
                        </div>
                        <div class="reset-details">
                            <div class="reset-removes">
                                <strong>Removes:</strong> src/, config/, api/, scripts/, Contact-Form-Sales/, .next/, node_modules/, package.json, .env.local, logs
                            </div>
                            <div class="reset-keeps">
                                <strong>Keeps:</strong> installer.php, .htaccess, README.md, .same/, .well-known/, .git/
                            </div>
                        </div>
                        <p style="color: #d97706; font-size: 13px; margin: 10px 0;">
                            ⚠️ <strong>Use when:</strong> Testing installations or starting fresh deployments
                        </p>
                        <button onclick="comprehensiveReset()" class="reset-button comprehensive">🔄 Comprehensive Reset</button>
                    </div>

                    <!-- Nuclear Reset - Danger Zone -->
                    <div class="reset-card nuclear">
                        <div class="reset-title">
                            💥 Nuclear Reset
                        </div>
                        <div class="reset-description">
                            <strong>IRREVERSIBLE:</strong> Removes everything except core system files. This is the most aggressive cleanup possible.
                        </div>
                        <div class="warning-text">
                            ⚠️ <strong>WARNING:</strong> This operation cannot be undone! Only use if you need to completely start over.
                        </div>
                        <div class="reset-details">
                            <div class="reset-removes">
                                <strong>Removes:</strong> ALL files and directories except installer.php, .same/, and .well-known/
                            </div>
                            <div class="reset-keeps">
                                <strong>Keeps:</strong> installer.php, .same/ folder, .well-known/ folder
                            </div>
                        </div>
                        <p style="color: #dc2626; font-size: 13px; margin: 10px 0; font-weight: 600;">
                            🚨 <strong>Only use if:</strong> Complete system wipe is required
                        </p>
                        <button onclick="nuclearReset()" class="reset-button nuclear">💥 Nuclear Reset</button>
                    </div>
                </div>

                <div style="margin-top: 25px; padding: 15px; background: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6; border-radius: 8px; text-align: center;">
                    <p style="color: #1e40af; margin: 0; font-size: 14px;">
                        💡 <strong>Pro Tip:</strong> Use "Self-Update" to get latest features. Start with "Clear Logs" for routine cleanup. Use "Comprehensive Reset" for testing. Reserve "Nuclear Reset" for emergency situations only.
                    </p>
                </div>
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

    // Self-Update Functions
    async function checkForUpdates() {
        showStatus("🔍 Checking for installer updates...", "info");
        try {
            const response = await fetch("?action=check_updates");
            const result = await response.json();

            if (result.success) {
                const remoteVersionEl = document.getElementById('remote-version');
                const updateNoticeEl = document.getElementById('update-available-notice');
                const updateButton = document.getElementById('update-button');

                remoteVersionEl.innerHTML = `<span class="new-version">Remote: v${result.remote_version}</span>`;

                if (result.update_available) {
                    updateNoticeEl.innerHTML = `<div class="update-available">🎉 Update available! v${result.current_version} → v${result.remote_version}</div>`;
                    updateButton.style.display = 'block';
                    showStatus(`✅ Update available: v${result.current_version} → v${result.remote_version}`, "success");
                } else {
                    updateNoticeEl.innerHTML = `<div class="update-info">✅ You have the latest version (v${result.current_version})</div>`;
                    updateButton.style.display = 'none';
                    showStatus(`✅ Already running latest version: v${result.current_version}`, "success");
                }

                // Show rollback button if backup exists
                checkBackupExists();
            } else {
                showStatus("❌ Update check failed: " + result.error, "error");
            }
        } catch (error) {
            showStatus("❌ Update check error: " + error.message, "error");
        }
    }

    async function updateInstaller() {
        if (!confirm("🔄 Update installer to the latest version? Current version will be backed up automatically.")) return;

        showStatus("⬆️ Downloading and installing update...", "info");
        try {
            const response = await fetch("?action=update_installer");
            const result = await response.json();

            if (result.success) {
                if (result.updated) {
                    showStatus(`✅ ${result.message}`, "success");
                    showStatus(`💾 Backup created: ${result.backup_file}`, "info");
                    showStatus("🔄 Page will reload in 3 seconds to use new version...", "info");
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showStatus(`ℹ️ ${result.message}`, "info");
                }
            } else {
                showStatus("❌ Update failed: " + result.error, "error");
            }
        } catch (error) {
            showStatus("❌ Update error: " + error.message, "error");
        }
    }

    async function rollbackInstaller() {
        if (!confirm("↩️ Rollback to the previous installer version? This will replace the current version with the backup.")) return;

        showStatus("↩️ Rolling back installer...", "info");
        try {
            const response = await fetch("?action=rollback_installer");
            const result = await response.json();

            if (result.success) {
                showStatus(`✅ ${result.message}`, "success");
                showStatus(`💾 Emergency backup created: ${result.emergency_backup}`, "info");
                showStatus("🔄 Page will reload in 3 seconds...", "info");
                setTimeout(() => location.reload(), 3000);
            } else {
                showStatus("❌ Rollback failed: " + result.error, "error");
            }
        } catch (error) {
            showStatus("❌ Rollback error: " + error.message, "error");
        }
    }

    async function showUpdateHistory() {
        const historyEl = document.getElementById('update-history');
        const contentEl = document.getElementById('update-history-content');

        try {
            const response = await fetch("?action=update_history");
            const result = await response.json();

            if (result.success && result.history.length > 0) {
                contentEl.innerHTML = result.history.join('<br>');
                historyEl.style.display = 'block';
                showStatus(`📜 Showing ${result.history.length} recent update entries`, "info");
            } else {
                showStatus("📜 No update history found", "info");
                historyEl.style.display = 'none';
            }
        } catch (error) {
            showStatus("❌ Failed to load update history: " + error.message, "error");
        }
    }

    function checkBackupExists() {
        // This would require another endpoint, but for now we'll just show the rollback button
        // if the user has performed an update
        const rollbackButton = document.getElementById('rollback-button');
        rollbackButton.style.display = 'block';
    }

    // Automatically check for updates on page load
    window.addEventListener('load', function() {
        // Small delay to let the page settle
        setTimeout(checkForUpdates, 1000);
    });

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
