<?php
/**
 * Admin Security Check - Installation Cleanup Enforcement
 * Include this file at the top of all admin pages to enforce security cleanup
 */

// Check if security cleanup is required
if (file_exists('.installation_cleanup_required') || file_exists('../.installation_cleanup_required')) {
    // Security cleanup is required - block access and redirect

    // Clear any admin sessions for security
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Destroy admin session if exists
    if (isset($_SESSION['admin_logged_in'])) {
        session_destroy();
        session_start();
    }

    // Set error message for cleanup page
    $_SESSION['admin_access_blocked'] = 'Admin access is blocked until security cleanup is completed.';

    // Determine redirect path based on current location
    $cleanup_url = file_exists('../cleanup-required.php') ? '../cleanup-required.php' : 'cleanup-required.php';

    // Redirect to cleanup page
    header("Location: $cleanup_url");
    exit('🔒 Security cleanup required before admin access. Redirecting...');
}

// If we reach here, security cleanup has been completed
// Continue with normal admin functionality

/**
 * Additional security function to double-check cleanup status
 */
function isSecurityCleanupComplete() {
    $security_files = ['detect.php', 'install.php', 'phpinfo.php'];

    foreach ($security_files as $file) {
        // Check in current directory and parent directory
        if (file_exists($file) || file_exists("../$file")) {
            return false;
        }
    }

    return true;
}

// Perform additional verification
if (!isSecurityCleanupComplete()) {
    // Files still exist - something went wrong with cleanup verification
    // Re-create the security flag and redirect

    $admin_dir = is_dir('.') ? '.' : '..';
    file_put_contents("$admin_dir/.installation_cleanup_required", time());

    $cleanup_url = file_exists('../cleanup-required.php') ? '../cleanup-required.php' : 'cleanup-required.php';
    header("Location: $cleanup_url");
    exit('🔒 Security verification failed. Installation files still detected.');
}

// All security checks passed - admin access is allowed
?>
