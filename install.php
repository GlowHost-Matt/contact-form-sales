<?php
/**
 * GlowHost Contact Form System - Diagnostic Installer
 * Version: 3.2 - Simple and Clear
 *
 * Tests server environment and provides actionable guidance for configuration issues.
 */

// Prevent timeouts during installation
@set_time_limit(300);
@ini_set('max_execution_time', 300);

// Configuration
define('GH_ZIP_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('GH_TEST_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/README.md');
define('MIN_PHP', '7.4.0');
define('INSTALL_DIR', 'install');

/**
 * Display error page with clear guidance
 */
function show_error($title, $message, $details = null, $solutions = []) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Error - <?php echo htmlspecialchars($title); ?></title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f8f9fa; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
            .message { margin: 20px 0; }
            .details { background: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d; margin: 20px 0; border-radius: 4px; }
            .solutions { background: #e7f3ff; padding: 20px; border-left: 4px solid #0066cc; margin: 20px 0; border-radius: 4px; }
            .solutions h3 { color: #0066cc; margin-top: 0; }
            .solutions ol { margin: 10px 0; padding-left: 20px; }
            .solutions li { margin: 8px 0; }
            pre { background: #f1f3f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <div class="message"><?php echo $message; ?></div>

            <?php if ($details): ?>
            <div class="details">
                <strong>Technical Details:</strong><br>
                <pre><?php echo htmlspecialchars($details); ?></pre>
            </div>
            <?php endif; ?>

            <?php if (!empty($solutions)): ?>
            <div class="solutions">
                <h3>How to Fix This:</h3>
                <ol>
                    <?php foreach ($solutions as $solution): ?>
                    <li><?php echo $solution; ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php endif; ?>

            <div class="footer">
                <p><strong>Need Help?</strong> Contact your hosting provider or server administrator with the technical details above.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Test outbound HTTPS connectivity
 */
function test_connectivity() {
    $test_url = GH_TEST_URL;
    $timeout = 15;

    // Test with cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $test_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'GlowHost-Installer/3.2'
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result !== false && $http_code === 200) {
            return ['success' => true, 'method' => 'cURL'];
        }

        return [
            'success' => false,
            'method' => 'cURL',
            'error' => $error ?: "HTTP $http_code",
            'details' => "URL: $test_url\nHTTP Code: $http_code\nError: $error"
        ];
    }

    // Test with file_get_contents if allow_url_fopen is enabled
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => 'GlowHost-Installer/3.2',
                'follow_location' => 1
            ]
        ]);

        $result = @file_get_contents($test_url, false, $context);
        if ($result !== false) {
            return ['success' => true, 'method' => 'file_get_contents'];
        }

        $error = error_get_last();
        return [
            'success' => false,
            'method' => 'file_get_contents',
            'error' => $error['message'] ?? 'Unknown error',
            'details' => "URL: $test_url\nLast Error: " . ($error['message'] ?? 'Unknown')
        ];
    }

    return [
        'success' => false,
        'method' => 'none',
        'error' => 'No download method available',
        'details' => 'Both cURL and allow_url_fopen are disabled'
    ];
}

// Check if wizard already exists
$wizard_file = __DIR__ . '/' . INSTALL_DIR . '/index.php';
if (file_exists($wizard_file)) {
    require $wizard_file;
    exit;
}

// Step 1: Check PHP version
if (version_compare(PHP_VERSION, MIN_PHP, '<')) {
    show_error(
        'PHP Version Too Old',
        'This installer requires PHP ' . MIN_PHP . ' or higher. Your server is running PHP ' . PHP_VERSION . '.',
        'Current PHP Version: ' . PHP_VERSION . "\nRequired: " . MIN_PHP . '+',
        [
            'Contact your hosting provider to upgrade to PHP ' . MIN_PHP . ' or higher',
            'If you have access to server management, update your PHP installation',
            'Check if your hosting control panel has PHP version selection options'
        ]
    );
}

// Step 2: Check required extensions
$missing_extensions = [];
if (!class_exists('ZipArchive')) $missing_extensions[] = 'ZipArchive';
if (!function_exists('curl_init') && !ini_get('allow_url_fopen')) $missing_extensions[] = 'cURL or allow_url_fopen';

if (!empty($missing_extensions)) {
    show_error(
        'Missing PHP Extensions',
        'Required PHP extensions are not available: <strong>' . implode(', ', $missing_extensions) . '</strong>',
        'Missing: ' . implode(', ', $missing_extensions),
        [
            'Contact your hosting provider to enable the missing PHP extensions',
            'If you manage the server, install the required extensions using your package manager',
            'For cURL: install php-curl package',
            'For ZipArchive: install php-zip package',
            'Restart your web server after installing extensions'
        ]
    );
}

// Step 3: Check directory permissions
if (!is_writable(__DIR__)) {
    show_error(
        'Directory Not Writable',
        'The installer cannot write to the current directory. The web server needs write permissions.',
        'Directory: ' . __DIR__ . "\nWritable: No",
        [
            'Set directory permissions to 755 or 775: <code>chmod 755 ' . __DIR__ . '</code>',
            'Ensure the web server user (usually www-data, apache, or nginx) owns the directory',
            'If using cPanel or similar, use File Manager to set permissions',
            'Contact your hosting provider if you cannot change permissions'
        ]
    );
}

// Step 4: Test outbound connectivity
$connectivity = test_connectivity();
if (!$connectivity['success']) {
    $solutions = [
        'Check if your server firewall allows outbound HTTPS connections on port 443',
        'Verify that connections to <code>raw.githubusercontent.com</code> are not blocked',
        'If using a firewall like CSF, add GitHub domains to the whitelist',
        'Check PHP configuration - ensure <code>allow_url_fopen</code> is enabled or cURL is working',
        'Contact your hosting provider about outbound connection restrictions'
    ];

    if ($connectivity['method'] === 'cURL') {
        $solutions[] = 'cURL-specific: Check SSL certificate validation settings';
        $solutions[] = 'Try disabling SSL verification temporarily (not recommended for production)';
    }

    show_error(
        'Cannot Connect to GitHub',
        'Your server cannot download files from GitHub. This is required to fetch the installation files.',
        $connectivity['details'],
        $solutions
    );
}

// Step 5: Download and extract installer
function download_file($url, $dest) {
    if (function_exists('curl_init')) {
        $fp = fopen($dest, 'w');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'GlowHost-Installer/3.2'
        ]);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        return $result !== false && $http_code === 200;
    }

    $data = file_get_contents($url);
    return $data !== false && file_put_contents($dest, $data) !== false;
}

$zip_file = 'installer-temp.zip';
if (!download_file(GH_ZIP_URL, $zip_file)) {
    show_error(
        'Download Failed',
        'Could not download the installation files from GitHub.',
        'URL: ' . GH_ZIP_URL,
        [
            'Check your internet connection',
            'Verify firewall settings allow downloads from GitHub',
            'Try again in a few minutes in case of temporary network issues',
            'Contact your hosting provider about download restrictions'
        ]
    );
}

// Extract ZIP file
$zip = new ZipArchive();
if ($zip->open($zip_file) !== TRUE) {
    @unlink($zip_file);
    show_error(
        'Invalid ZIP File',
        'The downloaded file is not a valid ZIP archive or is corrupted.',
        'File: ' . $zip_file . "\nSize: " . (file_exists($zip_file) ? filesize($zip_file) : 0) . ' bytes',
        [
            'Try downloading again - the file may have been corrupted during transfer',
            'Check available disk space on your server',
            'Contact support if the problem persists'
        ]
    );
}

// Create temporary extraction directory
$temp_dir = 'temp_extract_' . uniqid();
if (!mkdir($temp_dir)) {
    $zip->close();
    @unlink($zip_file);
    show_error('Cannot Create Directory', 'Failed to create temporary extraction directory.');
}

// Extract files
$zip->extractTo($temp_dir);
$zip->close();
@unlink($zip_file);

// Find the install directory
$install_source = null;
foreach (scandir($temp_dir) as $item) {
    if ($item === '.' || $item === '..') continue;
    $check_path = $temp_dir . '/' . $item . '/' . INSTALL_DIR;
    if (is_dir($check_path) && file_exists($check_path . '/index.php')) {
        $install_source = $check_path;
        break;
    }
}

if (!$install_source) {
    show_error('Installation Files Missing', 'Could not find the installation wizard in the downloaded files.');
}

// Move install directory to final location
if (!rename($install_source, INSTALL_DIR)) {
    show_error('Cannot Install', 'Failed to move installation files to the correct location.');
}

// Cleanup
function removeDir($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDir($path) : unlink($path);
    }
    rmdir($dir);
}
removeDir($temp_dir);

// Success! Redirect to wizard
header('Location: ' . INSTALL_DIR . '/index.php');
exit;
?>
