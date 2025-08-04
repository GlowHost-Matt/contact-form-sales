<?php
/**
 * GlowHost Contact Form – Single-File Installer v3.0 (ASCII-safe)
 * Performs advanced diagnostics for server environment and download capabilities.
 */

// --- Configuration ---
const GH_ZIP_URL  = 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip';
const GH_HOST     = 'raw.githubusercontent.com';
const TMP_ZIP     = 'glowhost-installer.zip';
const INSTALL_DIR = 'install';
const MIN_PHP     = '7.4.0';

// --- Utility & Error Handling ---
function fail($title, $message, $tech_details = '') {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><title>Installer Error</title><style>body{font-family:system-ui,sans-serif;color:#333;background:#f9fafb;margin:0;padding:2em}div.container{max-width:700px;margin:0 auto;padding:2em;background:white;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.08)}h2{color:#c53030;border-bottom:1px solid #e5e7eb;padding-bottom:.5em;margin-top:0}p{line-height:1.6}pre{background:#f3f4f6;padding:1em;border-radius:6px;white-space:pre-wrap;word-wrap:break-word;font-family:monospace;border:1px solid #d1d5db;margin-top:.5em}</style></head><body><div class="container">';
    echo '<h2>'.htmlspecialchars($title).'</h2><p>'.($message).'</p>';
    if ($tech_details) {
        echo '<pre>'.htmlspecialchars($tech_details).'</pre>';
    }
    echo '</div></body></html>';
    if (file_exists(TMP_ZIP)) @unlink(TMP_ZIP);
    exit;
}

// --- Step 1: Basic Prerequisites ---
$errors = [];
if (version_compare(PHP_VERSION, MIN_PHP, '<')) $errors[] = 'PHP version '.MIN_PHP.' or higher is required. You are running '.PHP_VERSION.'.';
if (!class_exists('ZipArchive')) $errors[] = 'The PHP ZipArchive extension is missing or disabled.';
if (!is_writable(__DIR__)) $errors[] = 'The directory <strong>'.htmlspecialchars(__DIR__).'</strong> is not writable by the web server.';
if (!function_exists('curl_init') && !ini_get('allow_url_fopen')) $errors[] = 'Both cURL and allow_url_fopen are disabled. At least one is required for downloads.';
if ($errors) {
    fail('Prerequisites Not Met', 'Your server environment does not meet the minimum requirements to run the installer. Please resolve the following issues:<br><ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
}

// --- Step 2: Advanced Connectivity Test ---
function test_outbound_connection() {
    $host = GH_HOST;
    $port = 443;
    $timeout = 15; // Increased timeout
    $error_str = '';
    $error_no = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => 'HEAD',
            CURLOPT_NOBODY => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'GlowHost Installer Connectivity Test/3.0'
        ]);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 400) return ['success' => true];
        return ['success' => false, 'method' => 'cURL', 'error' => "Received HTTP status $http_code. Error: $curl_error"];
    }

    if (ini_get('allow_url_fopen')) {
        $stream_context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $socket = @fsockopen('ssl://' . $host, $port, $error_no, $error_str, $timeout);
        if ($socket) {
            fclose($socket);
            return ['success' => true];
        }
        return ['success' => false, 'method' => 'fsockopen (for allow_url_fopen)', 'error' => "Error #$error_no: $error_str"];
    }

    return ['success' => false, 'method' => 'Unknown', 'error' => 'No valid download method could be tested.'];
}

$connection_test = test_outbound_connection();
if (!$connection_test['success']) {
    $message = 'Your server cannot make outbound HTTPS connections to GitHub. This is required to download the application files. This is a server configuration issue, not a script error.<br><br><strong>What to do:</strong><br>Contact your hosting provider or server administrator and ask them to ensure that your server can connect to <strong>'.GH_HOST.'</strong> on port 443. This is often blocked by a firewall (like CSF or firewalld) or due to incorrect PHP/cURL SSL configurations.';
    $tech_details = "Test Method: {$connection_test['method']}\nHost: " . GH_HOST . ":443\nError Details: {$connection_test['error']}";
    fail('Outbound Connection Failed', $message, $tech_details);
}

// --- Step 3: Run the Installer (Full logic from v2.1) ---
$wizard_path = __DIR__ . DIRECTORY_SEPARATOR . INSTALL_DIR . DIRECTORY_SEPARATOR . 'index.php';
if (file_exists($wizard_path)) {
    require $wizard_path;
    exit;
}

function download_zip($url, $dest) {
    if (function_exists('curl_init')) {
        $fp = fopen($dest, 'w+');
        if ($fp === false) fail('File System Error', 'Failed to open local file for writing.', 'Path: ' . htmlspecialchars($dest));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_USERAGENT      => 'GlowHost-Installer/3.0',
            CURLOPT_FAILONERROR    => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        if ($result === false) {
             fail("Download Failed (cURL)", "The script passed the initial connection test, but the actual file download failed.", "HTTP code: $http_code. Error: " . htmlspecialchars($curl_error));
        }
        return true;
    }
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create(['http' => ['user_agent' => 'GlowHost-Installer/3.0', 'timeout' => 120, 'follow_location' => 1, 'max_redirects' => 20]]);
        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            $last_error = error_get_last();
            fail('Download Failed (file_get_contents)', 'The script passed the initial connection test, but the actual file download failed.', 'Last error: ' . htmlspecialchars($last_error['message'] ?? 'Unknown error'));
        }
        if (file_put_contents($dest, $data) === false) {
            fail('File System Error', 'Failed to write downloaded data to local file.', 'Path: ' . htmlspecialchars($dest));
        }
        return true;
    }
    return false;
}

download_zip(GH_ZIP_URL, TMP_ZIP);

if (!file_exists(TMP_ZIP) || filesize(TMP_ZIP) < 100000) {
    $size = file_exists(TMP_ZIP) ? filesize(TMP_ZIP) : 0;
    fail('Download Verification Failed', 'The downloaded file is missing or too small.', 'Expected > 100000 bytes, but got ' . $size . ' bytes.');
}

$zip = new ZipArchive();
if ($zip->open(TMP_ZIP) !== TRUE) {
    fail('Extraction Failed', 'Could not open the downloaded ZIP file. It may be corrupt or not a valid ZIP archive.');
}

$tmp_extract_dir = __DIR__ . DIRECTORY_SEPARATOR . '_tmp_installer_' . uniqid();
if (!mkdir($tmp_extract_dir, 0755, true)) {
    fail('File System Error', 'Could not create temporary extraction directory. Check permissions.');
}

if (!$zip->extractTo($tmp_extract_dir)) {
    $zip->close();
    fail('Extraction Failed', 'Could not extract files from the ZIP archive.');
}
$zip->close();

$repo_dir_name = '';
foreach (scandir($tmp_extract_dir) as $file) {
    if ($file !== '.' && $file !== '..') {
        $potential_dir = $tmp_extract_dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($potential_dir)) {
            $repo_dir_name = $file;
            break;
        }
    }
}
if (empty($repo_dir_name)) {
    fail('Extraction Failed', 'Could not find the main repository directory inside the extracted ZIP file.');
}

$install_dir_source = $tmp_extract_dir . DIRECTORY_SEPARATOR . $repo_dir_name . DIRECTORY_SEPARATOR . INSTALL_DIR;
if (!is_dir($install_dir_source)) {
    fail('Extraction Failed', 'The "install" directory was not found inside the extracted ZIP archive.');
}

$final_install_dir = __DIR__ . DIRECTORY_SEPARATOR . INSTALL_DIR;
if (is_dir($final_install_dir)) {
    rename($final_install_dir, $final_install_dir . '_backup_' . time());
}
if (!rename($install_dir_source, $final_install_dir)) {
    fail('File System Error', 'Failed to move the extracted "install" directory into place. Check file permissions.');
}

@unlink(TMP_ZIP);

if (file_exists($wizard_path)) {
    header("Location: " . INSTALL_DIR . "/index.php");
    exit;
} else {
    fail('Installation Failed', 'The final installation files could not be found after extraction.');
}
?>
