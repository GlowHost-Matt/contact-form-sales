<?php
/**
 * GlowHost Contact Form System - Progressive Installation Wizard
 * Version: 3.1.1 - Enhanced with Automatic Database Creation
 *
 * Professional installation wizard for creating database-driven contact form system
 * with admin interface, user management, and AUTOMATIC database setup.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🚨 CRITICAL: THIS IS STEP 2 OF THE INSTALLATION FLOW 🚨
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * THIS FILE SHOULD NOT BE THE STARTING POINT!
 *
 * CORRECT FLOW:
 * 1. User starts with detect.php (the entry point)
 * 2. detect.php qualifies the system and downloads THIS file
 * 3. THIS FILE runs the 5-step installation wizard:
 *    - Step 1: Environment Check
 *    - Step 2: DATABASE SETUP (AUTOMATIC - like WordPress!)
 *    - Step 3: Admin Account Creation
 *    - Step 4: System Installation
 *    - Step 5: Completion → Security Cleanup
 *
 * KEY FEATURE: AUTOMATIC DATABASE CREATION
 * - Detects MySQL permissions automatically
 * - Creates databases when possible (like WordPress/Drupal)
 * - Smart database naming with conflict resolution
 * - Graceful fallback to manual when needed
 * - Real-time progress feedback
 *
 * IF USER BYPASSED detect.php: They missed system qualification!
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Configuration
define('INSTALLER_VERSION', '3.1.1');
define('MIN_PHP_VERSION', '7.4.0');
define('RECOMMENDED_PHP_VERSION', '8.1.0');

// Installation constants
define('CONFIG_FILE', 'config.php');
define('ADMIN_DIR', 'admin');
define('ASSETS_DIR', 'assets');
define('API_DIR', 'api');

// Security and session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * INSTALLATION STEP MANAGER
 */
class InstallationWizard {

    private $steps = [
        1 => ['name' => 'Environment Analysis', 'function' => 'checkEnvironment'],
        2 => ['name' => 'Database Config', 'function' => 'setupDatabase'],
        3 => ['name' => 'Admin Account', 'function' => 'createAdminUser'],
        4 => ['name' => 'Install System', 'function' => 'installSystem'],
        5 => ['name' => 'Complete', 'function' => 'completeInstallation']
    ];

    public function getCurrentStep() {
        return isset($_GET['step']) ? (int)$_GET['step'] : 1;
    }

    public function getStepName($step) {
        return $this->steps[$step]['name'] ?? 'Unknown Step';
    }

    public function getStepFunction($step) {
        return $this->steps[$step]['function'] ?? null;
    }

    public function getTotalSteps() {
        return count($this->steps);
    }

    public function isValidStep($step) {
        return isset($this->steps[$step]);
    }
}

/**
 * WEB ROOT VERIFICATION SYSTEM
 */
class WebRootVerifier {

    public static function verify() {
        $checks = [];

        // Check 1: Document root detection
        $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $current_dir = dirname(__FILE__);
        $checks['document_root'] = [
            'name' => 'Web Root Location',
            'status' => ($document_root === $current_dir),
            'message' => ($document_root === $current_dir) ?
                'Installer is in web root' :
                'Warning: Installer may not be in web root',
            'critical' => false
        ];

        // Check 2: Directory permissions
        $writable = is_writable($current_dir);
        $checks['permissions'] = [
            'name' => 'Write Permissions',
            'status' => $writable,
            'message' => $writable ?
                'Directory is writable' :
                'Directory is not writable',
            'critical' => true
        ];

        // Check 3: Clean installation
        $admin_exists = is_dir(ADMIN_DIR);
        $checks['clean_install'] = [
            'name' => 'Clean Installation',
            'status' => !$admin_exists,
            'message' => !$admin_exists ?
                'Ready for installation' :
                'Admin directory already exists',
            'critical' => true
        ];

        return $checks;
    }

    public static function canProceed($checks) {
        foreach ($checks as $check) {
            if ($check['critical'] && !$check['status']) {
                return false;
            }
        }
        return true;
    }
}

/**
 * ENVIRONMENT CHECKER
 */
class EnvironmentChecker {

    public static function checkPHP() {
        $version = PHP_VERSION;
        $min_met = version_compare($version, MIN_PHP_VERSION, '>=');
        $recommended_met = version_compare($version, RECOMMENDED_PHP_VERSION, '>=');

        return [
            'name' => 'PHP Version',
            'status' => $min_met,
            'level' => $recommended_met ? 'excellent' : ($min_met ? 'good' : 'error'),
            'message' => $min_met ?
                ($recommended_met ? "PHP $version (Excellent)" : "PHP $version (Compatible)") :
                "PHP $version (Requires " . MIN_PHP_VERSION . "+)",
            'critical' => true
        ];
    }

    public static function checkExtensions() {
        $required = [
            'mysqli' => ['name' => 'MySQL Support', 'critical' => true],
            'pdo' => ['name' => 'PDO Support', 'critical' => true],
            'json' => ['name' => 'JSON Support', 'critical' => true],
            'session' => ['name' => 'Session Support', 'critical' => true],
            'hash' => ['name' => 'Hash Support', 'critical' => true],
            'mbstring' => ['name' => 'Multibyte Strings', 'critical' => false],
            'curl' => ['name' => 'cURL Support', 'critical' => false]
        ];

        $results = [];
        foreach ($required as $ext => $config) {
            $loaded = extension_loaded($ext);

            $results[$ext] = [
                'name' => $config['name'],
                'status' => $loaded,
                'message' => $loaded ? 'Available' : 'Not available',
                'critical' => $config['critical']
            ];
        }

        return $results;
    }

    public static function runAllChecks() {
        $results = [
            'php' => self::checkPHP(),
            'extensions' => self::checkExtensions()
        ];

        return $results;
    }

    public static function canProceed($results) {
        // Check PHP version
        if (!$results['php']['status']) return false;

        // Check required extensions
        foreach ($results['extensions'] as $ext => $check) {
            if ($check['critical'] && !$check['status']) {
                return false;
            }
        }

        return true;
    }
}