<?php
/**
 * Installer AJAX Debug Version - Real-time debugging
 * This replicates the exact AJAX calls the installer makes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('INSTALLER_VERSION', '1.2');
define('PACKAGE_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('PACKAGE_DIR', 'contact-form-sales-main');
define('TEMP_DIR', sys_get_temp_dir() . '/cf_installer_' . uniqid());
define('LOG_FILE', __DIR__ . '/installer.log');

session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Logging function
function debugLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] AJAX-DEBUG: {$message}\n";
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
    echo "<div style='background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px; margin: 4px 0; border-radius: 4px;'>";
    echo "<strong>[{$timestamp}]</strong> {$message}";
    echo "</div>";
    flush();
}

// Format bytes
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Download function (copied from installer)
function downloadPackage() {
    debugLog('Starting package download from: ' . PACKAGE_URL);

    if (!file_exists(TEMP_DIR)) {
        if (!mkdir(TEMP_DIR, 0755, true)) {
            throw new Exception('Failed to create temp directory: ' . TEMP_DIR);
        }
        debugLog('Created temp directory: ' . TEMP_DIR);
    }

    $zip_file = TEMP_DIR . '/package.zip';
    debugLog('Target zip file: ' . $zip_file);

    // Download with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PACKAGE_URL);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GlowHost-Contact-Form-Installer/' . INSTALLER_VERSION);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    debugLog('Starting cURL download...');
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    debugLog('cURL completed - HTTP: ' . $http_code . ', Error: ' . ($error ?: 'None'));

    if ($error) {
        throw new Exception('Download failed: ' . $error);
    }

    if ($http_code !== 200) {
        throw new Exception('Download failed with HTTP code: ' . $http_code);
    }

    if (!$data) {
        throw new Exception('No data received from download');
    }

    debugLog('Downloaded ' . formatBytes(strlen($data)) . ' of data');

    $bytes_written = file_put_contents($zip_file, $data);

    if ($bytes_written === false) {
        throw new Exception('Failed to save downloaded package to: ' . $zip_file);
    }

    debugLog('Package saved successfully: ' . formatBytes($bytes_written));

    // Verify file exists
    if (file_exists($zip_file)) {
        debugLog('✅ ZIP file verified to exist: ' . $zip_file);
        debugLog('File size on disk: ' . formatBytes(filesize($zip_file)));
    } else {
        debugLog('❌ ZIP file NOT found after save: ' . $zip_file);
        throw new Exception('ZIP file disappeared after save');
    }

    return [
        'success' => true,
        'message' => 'Package downloaded successfully',
        'size' => formatBytes($bytes_written),
        'file' => $zip_file,
        'temp_dir' => TEMP_DIR
    ];
}

// Extract function (copied from installer)
function extractPackage() {
    $zip_file = TEMP_DIR . '/package.zip';

    debugLog('Starting extraction process...');
    debugLog('Looking for ZIP file: ' . $zip_file);

    if (!file_exists($zip_file)) {
        debugLog('❌ CRITICAL: Package file not found at: ' . $zip_file);

        // List what IS in the temp directory
        if (is_dir(TEMP_DIR)) {
            debugLog('Temp directory contents:');
            $files = scandir(TEMP_DIR);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    debugLog('  - ' . $file . ' (' . filesize(TEMP_DIR . '/' . $file) . ' bytes)');
                }
            }
        } else {
            debugLog('❌ Temp directory does not exist: ' . TEMP_DIR);
        }

        throw new Exception('Package file not found: ' . $zip_file);
    }

    debugLog('✅ ZIP file found: ' . $zip_file . ' (' . formatBytes(filesize($zip_file)) . ')');

    $zip = new ZipArchive();
    $result = $zip->open($zip_file);

    if ($result !== TRUE) {
        debugLog('❌ Failed to open ZIP file, error code: ' . $result);
        throw new Exception('Failed to open ZIP file: ' . $result);
    }

    debugLog('✅ ZIP file opened successfully, contains ' . $zip->numFiles . ' files');

    $extract_path = TEMP_DIR . '/extracted';
    debugLog('Extracting to: ' . $extract_path);

    if (!$zip->extractTo($extract_path)) {
        debugLog('❌ Failed to extract ZIP file');
        throw new Exception('Failed to extract ZIP file');
    }

    $zip->close();
    debugLog('✅ ZIP extracted successfully');

    $package_path = $extract_path . '/' . PACKAGE_DIR;
    debugLog('Looking for package directory: ' . $package_path);

    if (!is_dir($package_path)) {
        debugLog('❌ Expected directory not found: ' . $package_path);

        // List what was extracted
        if (is_dir($extract_path)) {
            debugLog('Extracted directory contents:');
            $dirs = scandir($extract_path);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..') {
                    debugLog('  - ' . $dir);
                }
            }
        }
        throw new Exception('Package structure not found after extraction');
    }

    debugLog('✅ Package directory found: ' . $package_path);

    // Count files
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($package_path));
    $file_count = 0;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $file_count++;
        }
    }

    debugLog('✅ Package contains ' . $file_count . ' files');

    return [
        'success' => true,
        'message' => 'Package extracted successfully',
        'path' => $package_path,
        'files' => $file_count
    ];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Installer AJAX Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #fff5f5; }
        .success { border-left-color: #28a745; background: #f0fff4; }
    </style>
</head>
<body>
    <h1>🔍 Installer AJAX Debug Test</h1>
    <p>This will replicate the exact steps the installer AJAX process follows:</p>

    <?php
    try {
        echo "<div class='step'>";
        echo "<h3>Step 1: Download Package</h3>";
        $download_result = downloadPackage();
        echo "<div class='success'>✅ Download completed successfully!</div>";
        echo "<pre>" . print_r($download_result, true) . "</pre>";
        echo "</div>";

        echo "<div class='step'>";
        echo "<h3>Step 2: Extract Package</h3>";
        $extract_result = extractPackage();
        echo "<div class='success'>✅ Extraction completed successfully!</div>";
        echo "<pre>" . print_r($extract_result, true) . "</pre>";
        echo "</div>";

        echo "<div class='step success'>";
        echo "<h3>🎉 Success!</h3>";
        echo "<p>Both download and extraction work perfectly in this debug test.</p>";
        echo "<p><strong>This proves the core functionality works.</strong></p>";
        echo "<p>The issue must be in the AJAX request handling or session/temp directory management.</p>";
        echo "</div>";

    } catch (Exception $e) {
        echo "<div class='step error'>";
        echo "<h3>❌ Error Occurred</h3>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>This shows us exactly where the installer is failing!</strong></p>";
        echo "</div>";
    }

    // Cleanup
    if (defined('TEMP_DIR') && is_dir(TEMP_DIR)) {
        function removeDirectory($directory) {
            if (!is_dir($directory)) return;
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
        }
        removeDirectory(TEMP_DIR);
        debugLog('🧹 Cleanup completed - temp directory removed');
    }
    ?>

    <div class="step">
        <h3>📊 Comparison Tests</h3>
        <p><a href="installer-debug.php" style="color: #007bff;">→ Run Original Debug Script</a></p>
        <p><a href="line-97-debug.php" style="color: #007bff;">→ Run Line 97 Debug Script</a></p>
        <p><a href="installer.php" style="color: #007bff;">→ Test Main Installer</a></p>

        <p><strong>Log File:</strong> Check installer.log for detailed debugging information</p>
    </div>
</body>
</html>
