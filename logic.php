<?php
// logic.php - The "Brain" of the Installer

// --- Core Configuration & Error Reporting ---
// Ensure we see all errors during installation
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Dynamic Username Detection ---
// Proven logic from our successful final_test.php
function get_database_prefix() {
    // Default to empty in case of failure
    $prefix = '';
    
    // The most reliable method for cPanel/standard environments
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        // Replace backslashes for Windows compatibility, though not expected here
        $doc_root = str_replace('\\', '/', $doc_root);
        // Remove trailing slash if it exists
        $doc_root = rtrim($doc_root, '/');
        
        $path_parts = explode('/', $doc_root);
        
        // The username is typically the 3rd component in /home/username/public_html
        if (count($path_parts) >= 3 && $path_parts[1] === 'home') {
            $username = $path_parts[2];
            // Sanitize and ensure it's a valid prefix component
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
            if (!empty($username)) {
                $prefix = $username . '_';
            }
        }
    }
    
    return htmlspecialchars($prefix);
}

// --- Pre-Flight Checks ---
// Function to check the server environment before installation
function run_pre_flight_checks() {
    $results = [];

    // 1. Check PHP Version
    $php_version_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
    $results['php_version'] = [
        'status' => $php_version_ok,
        'message' => 'PHP Version: ' . PHP_VERSION . ($php_version_ok ? ' (OK)' : ' (FAIL - Requires 7.4+)')
    ];

    // 2. Check Required Extensions
    $required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
    $missing_extensions = [];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing_extensions[] = $ext;
        }
    }
    $extensions_ok = empty($missing_extensions);
    $results['extensions'] = [
        'status' => $extensions_ok,
        'message' => $extensions_ok ? 'All required extensions are loaded.' : 'Missing extensions: ' . implode(', ', $missing_extensions)
    ];

    // 3. Check Directory Permissions
    $is_writable = is_writable(__DIR__);
    $results['permissions'] = [
        'status' => $is_writable,
        'message' => 'Directory is writable: ' . ($is_writable ? 'Yes' : 'No')
    ];
    
    // Overall Status
    $overall_ok = $php_version_ok && $extensions_ok && $is_writable;
    $results['overall_status'] = $overall_ok;

    return $results;
}

// --- Execute Logic ---
// Run the functions and prepare variables for install.php
$db_prefix = get_database_prefix();
$pre_flight_results = run_pre_flight_checks();

?>