<?php
/**
 * GlowHost Contact Form – Single-File Installer (ASCII-safe)
 * Upload only this file, browse to /install.php, follow the wizard.
 */

// --- Configuration ---
const GH_ZIP_URL  = 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip';
const TMP_ZIP     = 'glowhost-installer.zip';
const INSTALL_DIR = 'install';
const MIN_PHP     = '7.4.0';

// --- Pre-flight checks ---
$errors = [];
if (version_compare(PHP_VERSION, MIN_PHP, '<'))
    $errors[] = 'PHP '.MIN_PHP.' or higher required (current '.PHP_VERSION.')';
if (!ini_get('allow_url_fopen') && !function_exists('curl_version'))
    $errors[] = 'Outbound HTTPS download disabled; enable allow_url_fopen or cURL.';
if (!class_exists('ZipArchive'))
    $errors[] = 'PHP ZipArchive extension missing.';
if (!is_writable(__DIR__))
    $errors[] = 'Directory '.htmlspecialchars(__DIR__)." is not writable by PHP.";
if ($errors) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h2 style="font-family:system-ui;color:#c00">Installer prerequisites not met</h2><ul>';
    foreach ($errors as $e) echo '<li>'.$e.'</li>';
    echo '</ul><p>Fix the items above and reload this page.</p>';
    exit;
}

// --- If wizard already present, load it ---
$wizard = __DIR__ . '/' . INSTALL_DIR . '/index.php';
if (is_file($wizard)) {
    require $wizard;
    exit;
}

// --- Download payload zip ---
function fetch_zip($url, $dest) {
    if (function_exists('curl_version')) {
        $fp = fopen($dest, 'w');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'GlowHost Installer',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 60,
        ]);
        $ok = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return (bool)$ok;
    }
    $data = @file_get_contents($url);
    return $data !== false && file_put_contents($dest, $data);
}
if (!fetch_zip(GH_ZIP_URL, TMP_ZIP)) {
    exit('Download failed – check firewall / allow_url_fopen / cURL.');
}

// --- Extract /install directory ---
$zip = new ZipArchive();
if ($zip->open(TMP_ZIP) !== true) exit('Zip open failed.');
$tmp = __DIR__ . '/_tmp_' . uniqid();
mkdir($tmp);
$zip->extractTo($tmp);
$zip->close();
unlink(TMP_ZIP);

$found = '';
foreach (scandir($tmp) as $e) {
    if ($e === '.' || $e === '..') continue;
    $p = $tmp . '/' . $e . '/install';
    if (is_dir($p) && is_file($p . '/index.php')) { $found = $p; break; }
}
if (!$found) exit('Install directory not found in zip.');
rename($found, __DIR__ . '/' . INSTALL_DIR);

// cleanup
function rr($d){foreach(scandir($d) as $f){if($f==='.'||$f==='..')continue; $p="$d/$f"; is_dir($p)?rr($p):unlink($p);} rmdir($d);} rr($tmp);

// --- Forward to wizard ---
require __DIR__ . '/' . INSTALL_DIR . '/index.php';
?>
