<?php
/**
 * GlowHost Contact Form – Single-File Installer v2 (ASCII-safe)
 * Upload only this file, browse to /install.php, follow the wizard.
 */

// --- Configuration ---
const GH_ZIP_URL  = 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip';
const TMP_ZIP     = 'glowhost-installer.zip';
const INSTALL_DIR = 'install';
const MIN_PHP     = '7.4.0';

// --- Utility Functions ---
function fail($message) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><title>Installer Error</title><style>body{font-family:system-ui,sans-serif;color:#333;background:#fdfdfe;padding:2em;}div{max-width:600px;margin:0 auto;padding:2em;background:white;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.05);}h2{color:#d00;}</style></head><body><div>';
    echo '<h2>Installer Error</h2><p>' . htmlspecialchars($message) . '</p>';
    echo '</div></body></html>';
    // Attempt to clean up failed download
    if (file_exists(TMP_ZIP)) @unlink(TMP_ZIP);
    exit;
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $object))
                rrmdir($dir . DIRECTORY_SEPARATOR . $object);
            else
                unlink($dir . DIRECTORY_SEPARATOR . $object);
        }
    }
    rmdir($dir);
}

// --- Step 1: Pre-flight checks ---
$errors = [];
if (version_compare(PHP_VERSION, MIN_PHP, '<'))
    $errors[] = 'PHP '.MIN_PHP.' or higher is required (you are running ' . PHP_VERSION . ').';
if (!class_exists('ZipArchive'))
    $errors[] = 'The PHP ZipArchive extension is missing. Please ask your host to install it.';
if (!is_writable(__DIR__))
    $errors[] = 'The current directory (' . htmlspecialchars(__DIR__) . ") is not writable by the web server.";
if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
    $errors[] = 'Outbound HTTPS downloads are disabled. Please enable `allow_url_fopen` or the `cURL` extension in your PHP configuration.';

if ($errors) {
    $error_html = '<h2>Prerequisites Not Met</h2><ul><li>' . implode('</li><li>', $errors) . '</li></ul><p>Please fix the items above and reload this page.</p>';
    fail($error_html); // This uses the styled fail function.
}

// --- Step 2: If wizard already exists, load it ---
$wizard_path = __DIR__ . DIRECTORY_SEPARATOR . INSTALL_DIR . DIRECTORY_SEPARATOR . 'index.php';
if (file_exists($wizard_path)) {
    require $wizard_path;
    exit;
}

// --- Step 3: Download the payload zip ---
function download_zip($url, $dest) {
    if (function_exists('curl_init')) {
        $fp = fopen($dest, 'w+');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_USERAGENT      => 'GlowHost-Installer/2.0',
            CURLOPT_FAILONERROR    => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        if ($result === false) {
            fail("cURL Error: Failed to download ZIP file. " . htmlspecialchars($error));
        }
        return true;
    }
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create(['http' => ['user_agent' => 'GlowHost-Installer/2.0', 'timeout' => 90]]);
        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            fail('file_get_contents Error: Failed to download ZIP file. Check server firewall or allow_url_fopen settings.');
        }
        if (file_put_contents($dest, $data) === false) {
            fail('Failed to write downloaded data to local file: ' . TMP_ZIP);
        }
        return true;
    }
    return false;
}

download_zip(GH_ZIP_URL, TMP_ZIP);

// Verify download
if (!file_exists(TMP_ZIP) || filesize(TMP_ZIP) < 100000) { // Expecting ~600KB+
    fail('Downloaded file is missing or too small, suggesting a failed download.');
}

// --- Step 4: Extract the /install directory ---
$zip = new ZipArchive();
if ($zip->open(TMP_ZIP) !== TRUE) {
    fail('Failed to open the downloaded ZIP file. It may be corrupt.');
}

// Create a temporary directory for extraction
$tmp_extract_dir = __DIR__ . DIRECTORY_SEPARATOR . '_tmp_installer_' . uniqid();
if (!mkdir($tmp_extract_dir, 0755, true)) {
    fail('Could not create temporary extraction directory.');
}

// Extract the full archive
if (!$zip->extractTo($tmp_extract_dir)) {
    $zip->close();
    rrmdir($tmp_extract_dir);
    fail('Failed to extract files from the ZIP archive.');
}
$zip->close();

// Find the 'install' directory within the extracted files
$install_dir_source = null;
$scanned_dirs = scandir($tmp_extract_dir);

// The zip extracts to a single directory like 'contact-form-sales-main'
$repo_dir_name = '';
foreach ($scanned_dirs as $file) {
    if ($file !== '.' && $file !== '..') {
        $potential_dir = $tmp_extract_dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($potential_dir)) {
            $repo_dir_name = $file;
            break;
        }
    }
}

if (empty($repo_dir_name)) {
    rrmdir($tmp_extract_dir);
    fail('Could not find the main repository directory inside the ZIP file.');
}

$potential_install_path = $tmp_extract_dir . DIRECTORY_SEPARATOR . $repo_dir_name . DIRECTORY_SEPARATOR . INSTALL_DIR;

if (is_dir($potential_install_path) && file_exists($potential_install_path . DIRECTORY_SEPARATOR . 'index.php')) {
    $install_dir_source = $potential_install_path;
}

if ($install_dir_source === null) {
    rrmdir($tmp_extract_dir);
    fail('Could not find the "install" directory inside the extracted ZIP file. The repository structure may have changed.');
}

// --- Step 5: Move 'install' directory to the correct location ---
$final_install_dir = __DIR__ . DIRECTORY_SEPARATOR . INSTALL_DIR;
if (is_dir($final_install_dir)) {
    rrmdir($final_install_dir); // Clean up old one if it exists
}

if (!rename($install_dir_source, $final_install_dir)) {
    rrmdir($tmp_extract_dir);
    fail('Failed to move the install directory into place. Check file permissions.');
}

// --- Step 6: Cleanup ---
@unlink(TMP_ZIP);
rrmdir($tmp_extract_dir);

// --- Step 7: Forward to the wizard ---
if (file_exists($wizard_path)) {
    header("Location: " . INSTALL_DIR . "/index.php");
    exit;
} else {
    fail('Installation files were extracted, but the wizard entry point (index.php) could not be found.');
}

?>
