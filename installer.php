<?php
/**
 * GlowHost Contact Form System - Progressive Installation Wizard
 * Version: 3.0.0 - Complete Backend Platform
 *
 * Professional installation wizard for creating database-driven contact form system
 * with admin interface, user management, and comprehensive backend capabilities.
 */

// Configuration
define('INSTALLER_VERSION', '3.0.0');
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
        1 => ['name' => 'Environment Check', 'function' => 'checkEnvironment'],
        2 => ['name' => 'Database Setup', 'function' => 'setupDatabase'],
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

/**
 * DATABASE MANAGER
 */
class DatabaseManager {

    public static function testConnection($host, $username, $password, $database = null) {
        try {
            $dsn = "mysql:host=$host";
            if ($database) {
                $dsn .= ";dbname=$database";
            }

            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            return ['success' => true, 'message' => 'Connection successful'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    public static function createDatabase($host, $username, $password, $database) {
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            return ['success' => true, 'message' => "Database '$database' created successfully"];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database creation failed: ' . $e->getMessage()];
        }
    }

    public static function installSchema($host, $username, $password, $database) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Admin users table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admin_users (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('super_admin', 'admin') DEFAULT 'admin',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_login TIMESTAMP NULL,
                    status ENUM('active', 'inactive') DEFAULT 'active'
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");

            // Form submissions table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS form_submissions (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    subject VARCHAR(200) NOT NULL,
                    message TEXT NOT NULL,
                    attachments JSON NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('new', 'read', 'responded', 'archived') DEFAULT 'new',
                    admin_notes TEXT NULL
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");

            // System settings table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    key_name VARCHAR(50) PRIMARY KEY,
                    value TEXT NOT NULL,
                    description TEXT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");

            // Insert default settings
            $default_settings = [
                ['site_title', 'GlowHost Contact Form System', 'Main site title'],
                ['admin_email', '', 'Primary admin email for notifications'],
                ['form_notifications', '1', 'Enable email notifications for form submissions'],
                ['max_file_size', '10485760', 'Maximum file upload size in bytes (10MB)'],
                ['allowed_file_types', 'jpg,jpeg,png,gif,pdf,txt,doc,docx', 'Allowed file extensions'],
                ['installation_date', date('Y-m-d H:i:s'), 'System installation timestamp']
            ];

            $stmt = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value, description) VALUES (?, ?, ?)");
            foreach ($default_settings as $setting) {
                $stmt->execute($setting);
            }

            return ['success' => true, 'message' => 'Database schema installed successfully'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Schema installation failed: ' . $e->getMessage()];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'check_environment':
            $web_root = WebRootVerifier::verify();
            $environment = EnvironmentChecker::runAllChecks();

            $can_proceed = WebRootVerifier::canProceed($web_root) &&
                          EnvironmentChecker::canProceed($environment);

            echo json_encode([
                'success' => true,
                'web_root' => $web_root,
                'environment' => $environment,
                'can_proceed' => $can_proceed
            ]);
            break;

        case 'test_database':
            $host = $_POST['db_host'] ?? '';
            $username = $_POST['db_username'] ?? '';
            $password = $_POST['db_password'] ?? '';
            $database = $_POST['db_name'] ?? '';

            $result = DatabaseManager::testConnection($host, $username, $password, $database);
            echo json_encode($result);
            break;

        case 'install_database':
            $host = $_POST['db_host'] ?? '';
            $username = $_POST['db_username'] ?? '';
            $password = $_POST['db_password'] ?? '';
            $database = $_POST['db_name'] ?? '';
            $create_db = isset($_POST['create_database']);

            if ($create_db) {
                $create_result = DatabaseManager::createDatabase($host, $username, $password, $database);
                if (!$create_result['success']) {
                    echo json_encode($create_result);
                    break;
                }
            }

            $schema_result = DatabaseManager::installSchema($host, $username, $password, $database);
            echo json_encode($schema_result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Initialize wizard
$wizard = new InstallationWizard();
$current_step = $wizard->getCurrentStep();

// Validate step
if (!$wizard->isValidStep($current_step)) {
    $current_step = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form System - Installation</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border: #e5e7eb;
            --background: #f9fafb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.15);
            --radius: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Container */
        .installer {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
        }

        /* Header */
        .header {
            background: var(--primary);
            color: var(--white);
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        /* Progress */
        .progress {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 24px 32px;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin-bottom: 16px;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border);
            z-index: 1;
        }

        .step {
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-muted);
            z-index: 2;
            position: relative;
        }

        .step.active {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--white);
        }

        .step.completed {
            border-color: var(--success);
            background: var(--success);
            color: var(--white);
        }

        .step-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
        }

        .step-label {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            flex: 1;
        }

        .step-label.active {
            color: var(--primary);
            font-weight: 500;
        }

        .step-label.completed {
            color: var(--success);
        }

        /* Content */
        .content {
            padding: 40px;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
        }

        .step-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .step-description {
            color: var(--text-secondary);
            margin-bottom: 32px;
        }

        /* Check Items */
        .checks {
            display: grid;
            gap: 16px;
            margin: 24px 0;
        }

        .check {
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: border-color 0.2s;
        }

        .check.success {
            border-color: var(--success);
            background: #f0fdf4;
        }

        .check.warning {
            border-color: var(--warning);
            background: #fffbeb;
        }

        .check.error {
            border-color: var(--error);
            background: #fef2f2;
        }

        .check-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .check-content h4 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .check-content p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0;
        }

        .checkbox input {
            margin: 0;
        }

        .checkbox label {
            font-size: 14px;
            margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--text-secondary);
            color: var(--white);
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--text-primary);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover:not(:disabled) {
            background: var(--primary);
            color: var(--white);
        }

        /* Actions */
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin: 16px 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message.success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .message.info {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        /* Loading */
        .loading {
            display: none;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid var(--border);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .installer {
                margin: 10px;
            }

            .header {
                padding: 24px;
            }

            .header h1 {
                font-size: 20px;
            }

            .progress {
                padding: 20px 24px;
            }

            .content {
                padding: 24px;
            }

            .step {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .step-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Installation Wizard v<?php echo INSTALLER_VERSION; ?></p>
        </div>

        <div class="progress">
            <div class="progress-bar">
                <?php for ($i = 1; $i <= $wizard->getTotalSteps(); $i++): ?>
                <div class="step <?php echo $i < $current_step ? 'completed' : ($i === $current_step ? 'active' : ''); ?>">
                    <?php echo $i < $current_step ? '✓' : $i; ?>
                </div>
                <?php endfor; ?>
            </div>
            <div class="step-labels">
                <?php for ($i = 1; $i <= $wizard->getTotalSteps(); $i++): ?>
                <div class="step-label <?php echo $i < $current_step ? 'completed' : ($i === $current_step ? 'active' : ''); ?>">
                    <?php echo $wizard->getStepName($i); ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="content">
            <!-- Step 1: Environment Check -->
            <div class="step-content <?php echo $current_step === 1 ? 'active' : ''; ?>" id="step-1">
                <div class="step-title">Environment Check</div>
                <div class="step-description">
                    Verifying your server environment and installation requirements.
                </div>

                <div class="loading active" id="check-loading">
                    <div class="spinner"></div>
                    <span>Running checks...</span>
                </div>

                <div id="environment-results"></div>

                <div class="actions">
                    <div></div>
                    <button class="btn btn-primary" id="next-step-1" onclick="nextStep(1)" disabled>
                        Continue →
                    </button>
                </div>
            </div>

            <!-- Step 2: Database Setup -->
            <div class="step-content <?php echo $current_step === 2 ? 'active' : ''; ?>" id="step-2">
                <div class="step-title">Database Setup</div>
                <div class="step-description">
                    Configure your MySQL database connection and install the required tables.
                </div>

                <form id="database-form">
                    <div class="form-group">
                        <label class="form-label" for="db_host">Database Host</label>
                        <input type="text" class="form-input" id="db_host" name="db_host" value="localhost" required>
                        <div class="form-help">Usually 'localhost' for shared hosting</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="db_name">Database Name</label>
                        <input type="text" class="form-input" id="db_name" name="db_name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="db_username">Username</label>
                        <input type="text" class="form-input" id="db_username" name="db_username" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="db_password">Password</label>
                        <input type="password" class="form-input" id="db_password" name="db_password">
                    </div>

                    <div class="checkbox">
                        <input type="checkbox" id="create_database" name="create_database">
                        <label for="create_database">Create database if it doesn't exist</label>
                    </div>

                    <button type="button" class="btn btn-outline" onclick="testDatabase()">
                        Test Connection
                    </button>
                </form>

                <div id="database-messages"></div>

                <div class="actions">
                    <button class="btn btn-secondary" onclick="previousStep(2)">
                        ← Back
                    </button>
                    <button class="btn btn-primary" id="next-step-2" onclick="installDatabase()" disabled>
                        Install Database →
                    </button>
                </div>
            </div>

            <!-- Additional steps will be added here -->

        </div>
    </div>

    <script>
        const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('step-1').classList.contains('active')) {
                runEnvironmentChecks();
            }
        });

        async function runEnvironmentChecks() {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=check_environment&csrf_token=${csrfToken}`
                });

                const result = await response.json();

                if (result.success) {
                    displayEnvironmentResults(result);
                    document.getElementById('next-step-1').disabled = !result.can_proceed;
                } else {
                    showMessage('Error running environment checks', 'error');
                }
            } catch (error) {
                showMessage('Failed to run environment checks: ' + error.message, 'error');
            } finally {
                document.getElementById('check-loading').classList.remove('active');
            }
        }

        function displayEnvironmentResults(result) {
            let html = '<div class="checks">';

            // Web root checks
            for (const [key, check] of Object.entries(result.web_root)) {
                const status = check.status ? 'success' : (check.critical ? 'error' : 'warning');
                const icon = check.status ? '✅' : (check.critical ? '❌' : '⚠️');

                html += `
                    <div class="check ${status}">
                        <div class="check-icon">${icon}</div>
                        <div class="check-content">
                            <h4>${check.name}</h4>
                            <p>${check.message}</p>
                        </div>
                    </div>
                `;
            }

            // PHP check
            const phpCheck = result.environment.php;
            const phpStatus = phpCheck.status ? (phpCheck.level === 'excellent' ? 'success' : 'warning') : 'error';
            const phpIcon = phpCheck.status ? '✅' : '❌';

            html += `
                <div class="check ${phpStatus}">
                    <div class="check-icon">${phpIcon}</div>
                    <div class="check-content">
                        <h4>${phpCheck.name}</h4>
                        <p>${phpCheck.message}</p>
                    </div>
                </div>
            `;

            // Extension checks
            for (const [ext, check] of Object.entries(result.environment.extensions)) {
                const status = check.status ? 'success' : (check.critical ? 'error' : 'warning');
                const icon = check.status ? '✅' : (check.critical ? '❌' : '⚠️');

                html += `
                    <div class="check ${status}">
                        <div class="check-icon">${icon}</div>
                        <div class="check-content">
                            <h4>${check.name}</h4>
                            <p>${check.message}</p>
                        </div>
                    </div>
                `;
            }

            html += '</div>';

            if (!result.can_proceed) {
                html += '<div class="message error">❌ Please resolve the critical errors above before continuing.</div>';
            } else {
                html += '<div class="message success">✅ Environment check passed! Ready to proceed.</div>';
            }

            document.getElementById('environment-results').innerHTML = html;
        }

        async function testDatabase() {
            const form = document.getElementById('database-form');
            const formData = new FormData(form);
            formData.append('action', 'test_database');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('✅ ' + result.message, 'success', 'database-messages');
                    document.getElementById('next-step-2').disabled = false;
                } else {
                    showMessage('❌ ' + result.message, 'error', 'database-messages');
                    document.getElementById('next-step-2').disabled = true;
                }
            } catch (error) {
                showMessage('❌ Connection test failed: ' + error.message, 'error', 'database-messages');
            }
        }

        async function installDatabase() {
            const form = document.getElementById('database-form');
            const formData = new FormData(form);
            formData.append('action', 'install_database');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('✅ ' + result.message, 'success', 'database-messages');
                    setTimeout(() => nextStep(2), 1500);
                } else {
                    showMessage('❌ ' + result.message, 'error', 'database-messages');
                }
            } catch (error) {
                showMessage('❌ Database installation failed: ' + error.message, 'error', 'database-messages');
            }
        }

        function nextStep(currentStep) {
            window.location.href = `?step=${currentStep + 1}`;
        }

        function previousStep(currentStep) {
            window.location.href = `?step=${currentStep - 1}`;
        }

        function showMessage(message, type, containerId = 'environment-results') {
            const container = document.getElementById(containerId);
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.innerHTML = message;

            // Remove existing messages
            const existingMessages = container.querySelectorAll('.message');
            existingMessages.forEach(msg => msg.remove());

            container.appendChild(messageDiv);

            if (type === 'success') {
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 5000);
            }
        }
    </script>
</body>
</html>