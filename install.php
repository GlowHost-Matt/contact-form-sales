<?php
/**
 * GlowHost Contact Form System - Complete Single-File Installer
 * Version: 4.0 - PHP 5.2+ Compatible Entry Point
 */

// CRITICAL: Check PHP version FIRST using only PHP 5.2 compatible syntax
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo '<!DOCTYPE html>
<html>
<head>
    <title>PHP Version Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .error-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
        .current-version { background: #ffebee; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .required-version { background: #e8f5e8; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .instructions { background: #f5f5f5; padding: 20px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1 class="error-title">⚠️ PHP Version Too Old</h1>

        <div class="current-version">
            <strong>Current PHP Version:</strong> ' . PHP_VERSION . '
        </div>

        <div class="required-version">
            <strong>Required PHP Version:</strong> 7.4.0 or higher (8.1+ recommended)
        </div>

        <p>This installer uses modern PHP features that are not available in your current PHP version.</p>

        <div class="instructions">
            <h3>How to Update PHP:</h3>
            <ul>
                <li><strong>cPanel/Shared Hosting:</strong> Look for "PHP Version" or "MultiPHP Manager" in your control panel</li>
                <li><strong>Command Line:</strong> Contact your hosting provider or system administrator</li>
                <li><strong>Local Development:</strong> Update your local PHP installation</li>
            </ul>
        </div>

        <p><strong>After updating PHP:</strong> Refresh this page to continue with installation.</p>

        <div style="text-align: center; margin-top: 30px;">
            <button onclick="location.reload()" style="background: #1976d2; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                Check Again After Update
            </button>
        </div>
    </div>
</body>
</html>';
    exit;
}

// PHP 7.4+ reached - now safe to use modern syntax
// Prevent timeouts during installation
set_time_limit(300);
ini_set('max_execution_time', 300);

// Configuration
define('INSTALLER_VERSION', '4.0');
define('MIN_PHP_VERSION', '7.4.0');
define('RECOMMENDED_PHP_VERSION', '8.1.0');
define('GH_TEST_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/AI-CONTEXT.md');

// Installation constants
define('CONFIG_FILE', 'config.php');
define('ADMIN_DIR', 'admin');

// Security and session management (safe to use modern functions now)
if (!session_id()) {
    session_start();
}

// CSRF protection (using modern PHP functions - safe after version check)
if (!isset($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        // Fallback for older PHP versions (shouldn't reach here due to version check)
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    }
}

// Determine current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;

// Handle AJAX requests for real-time checking
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check') {
    header('Content-Type: application/json');
    echo json_encode(runAllChecks());
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleFormSubmission();
}

/**
 * ENVIRONMENT CHECKING FUNCTIONS
 */
function runAllChecks() {
    $results = [
        'php_version' => checkPHPVersion(),
        'extensions' => checkExtensions(),
        'permissions' => checkPermissions(),
        'connectivity' => checkConnectivity(),
        'existing_install' => checkExistingInstall()
    ];

    $results['can_proceed'] =
        $results['php_version']['status'] &&
        $results['extensions']['critical_passed'] &&
        $results['permissions']['status'] &&
        $results['connectivity']['status'] &&
        !$results['existing_install']['exists'];

    return $results;
}

function checkPHPVersion() {
    $current = PHP_VERSION;
    $meets_min = version_compare($current, MIN_PHP_VERSION, '>=');
    $meets_recommended = version_compare($current, RECOMMENDED_PHP_VERSION, '>=');

    return [
        'status' => $meets_min,
        'level' => $meets_recommended ? 'excellent' : ($meets_min ? 'good' : 'error'),
        'current' => $current,
        'required' => MIN_PHP_VERSION,
        'message' => $meets_min ?
            ($meets_recommended ? "PHP $current (Excellent)" : "PHP $current (Compatible)") :
            "PHP $current - Requires " . MIN_PHP_VERSION . "+"
    ];
}

function checkExtensions() {
    $required = [
        'ZipArchive' => ['critical' => true, 'check' => 'class_exists', 'name' => 'ZipArchive'],
        'cURL' => ['critical' => true, 'check' => 'function_exists', 'name' => 'curl_init'],
        'PDO' => ['critical' => true, 'check' => 'class_exists', 'name' => 'PDO'],
        'mbstring' => ['critical' => false, 'check' => 'extension_loaded', 'name' => 'mbstring']
    ];

    $results = [];
    $critical_passed = true;

    foreach ($required as $ext => $config) {
        $check_func = $config['check'];
        $status = $check_func($config['name']);

        // Special case for cURL or allow_url_fopen
        if ($ext === 'cURL' && !$status) {
            $status = ini_get('allow_url_fopen');
            $ext = 'Download Capability';
        }

        $results[$ext] = [
            'status' => $status,
            'critical' => $config['critical'],
            'message' => $status ? 'Available' : 'Not Available'
        ];

        if ($config['critical'] && !$status) {
            $critical_passed = false;
        }
    }

    $results['critical_passed'] = $critical_passed;
    return $results;
}

function checkPermissions() {
    $writable = is_writable(__DIR__);

    return [
        'status' => $writable,
        'directory' => __DIR__,
        'message' => $writable ? 'Directory is writable' : 'Directory is not writable'
    ];
}

function checkConnectivity() {
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
            CURLOPT_USERAGENT => 'GlowHost-Installer/4.0'
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result !== false && $http_code === 200) {
            return ['status' => true, 'method' => 'cURL', 'message' => 'GitHub connectivity successful'];
        }

        return [
            'status' => false,
            'method' => 'cURL',
            'message' => "Connection failed: $error (HTTP $http_code)"
        ];
    }

    // Test with file_get_contents if allow_url_fopen is enabled
    if (ini_get('allow_url_fopen')) {
        $result = @file_get_contents($test_url);
        if ($result !== false) {
            return ['status' => true, 'method' => 'file_get_contents', 'message' => 'GitHub connectivity successful'];
        }

        $error = error_get_last();
        return [
            'status' => false,
            'method' => 'file_get_contents',
            'message' => 'Connection failed: ' . ($error['message'] ?? 'Unknown error')
        ];
    }

    return [
        'status' => false,
        'method' => 'none',
        'message' => 'No download method available'
    ];
}

function checkExistingInstall() {
    $config_exists = file_exists(CONFIG_FILE);
    $admin_exists = is_dir(ADMIN_DIR);

    return [
        'exists' => $config_exists || $admin_exists,
        'config_file' => $config_exists,
        'admin_directory' => $admin_exists,
        'message' => ($config_exists || $admin_exists) ? 'Previous installation detected' : 'Ready for fresh installation'
    ];
}

/**
 * FORM HANDLING
 */
function handleFormSubmission() {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Security error: Invalid CSRF token');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'start_installation':
            // Start the installation process
            header('Location: ?step=1');
            exit;

        case 'test_database':
            header('Content-Type: application/json');
            echo json_encode(testDatabaseConnection());
            exit;

        case 'install_database':
            header('Content-Type: application/json');
            echo json_encode(installDatabase());
            exit;

        case 'create_admin':
            header('Content-Type: application/json');
            echo json_encode(createAdminUser());
            exit;

        case 'finalize_installation':
            header('Content-Type: application/json');
            echo json_encode(finalizeInstallation());
            exit;
    }
}

function testDatabaseConnection() {
    $host = $_POST['db_host'] ?? '';
    $username = $_POST['db_username'] ?? '';
    $password = $_POST['db_password'] ?? '';
    $database = $_POST['db_name'] ?? '';

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

function installDatabase() {
    $host = $_POST['db_host'] ?? '';
    $username = $_POST['db_username'] ?? '';
    $password = $_POST['db_password'] ?? '';
    $database = $_POST['db_name'] ?? '';

    try {
        // First, try to create the database
        $pdo = new PDO("mysql:host=$host", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Now connect to the database and create tables
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Create tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('super_admin', 'admin', 'user') DEFAULT 'user',
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_submissions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email_address VARCHAR(100) NOT NULL,
                phone_number VARCHAR(20) NULL,
                domain_name VARCHAR(100) NULL,
                inquiry_subject VARCHAR(250) NOT NULL,
                inquiry_message TEXT NOT NULL,
                department VARCHAR(50) NOT NULL,
                reference_id VARCHAR(20) UNIQUE NOT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                browser_name VARCHAR(100) NULL,
                operating_system VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('new', 'read', 'responded', 'archived') DEFAULT 'new',
                admin_notes TEXT NULL
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key_name VARCHAR(50) PRIMARY KEY,
                value TEXT NOT NULL,
                description TEXT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        // Store database config in session for next steps
        $_SESSION['db_config'] = compact('host', 'username', 'password', 'database');

        return ['success' => true, 'message' => 'Database and tables created successfully'];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database installation failed: ' . $e->getMessage()];
    }
}

function createAdminUser() {
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }

    $db_config = $_SESSION['db_config'] ?? null;
    if (!$db_config) {
        return ['success' => false, 'message' => 'Database configuration not found'];
    }

    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};dbname={$db_config['database']}",
            $db_config['username'],
            $db_config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, status)
            VALUES ('admin', ?, ?, 'super_admin', 'active')
        ");

        $stmt->execute([$email, $password_hash]);

        return ['success' => true, 'message' => 'Admin account created successfully'];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create admin account: ' . $e->getMessage()];
    }
}

function finalizeInstallation() {
    $db_config = $_SESSION['db_config'] ?? null;
    if (!$db_config) {
        return ['success' => false, 'message' => 'Database configuration not found'];
    }

    try {
        // Create config.php file
        $config_content = generateConfigFile($db_config);
        if (!file_put_contents(CONFIG_FILE, $config_content)) {
            throw new Exception('Failed to create config.php file');
        }

        // Create admin directory structure
        if (!createAdminFiles()) {
            throw new Exception('Failed to create admin files');
        }

        // Mark installation as complete
        file_put_contents('.installation_complete', date('Y-m-d H:i:s'));

        return [
            'success' => true,
            'message' => 'Installation completed successfully',
            'admin_url' => ADMIN_DIR . '/',
            'config_created' => true
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Installation failed: ' . $e->getMessage()];
    }
}

function generateConfigFile($db_config) {
    $site_url = getSiteUrl();

    return "<?php
/**
 * GlowHost Contact Form System - Configuration
 * Generated: " . date('Y-m-d H:i:s') . "
 */

// Database Configuration
define('DB_HOST', '" . addslashes($db_config['host']) . "');
define('DB_NAME', '" . addslashes($db_config['database']) . "');
define('DB_USER', '" . addslashes($db_config['username']) . "');
define('DB_PASS', '" . addslashes($db_config['password']) . "');

// System Configuration
define('SITE_URL', '$site_url');
define('ADMIN_URL', '$site_url/" . ADMIN_DIR . "');
define('SYSTEM_VERSION', '" . INSTALLER_VERSION . "');

// Initialize database connection
try {
    \$pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException \$e) {
    die('Database connection failed: ' . \$e->getMessage());
}
";
}

function createAdminFiles() {
    // Create admin directory
    if (!is_dir(ADMIN_DIR)) {
        mkdir(ADMIN_DIR, 0755, true);
    }

    // Create basic admin login page
    $login_content = '<?php
require_once "../config.php";
session_start();

if ($_POST) {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = \"active\"");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["admin_user"] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid credentials";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: system-ui; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; }
        .form-input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .btn { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
        .error { background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-input" name="username" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>';

    // Create basic admin dashboard
    $dashboard_content = '<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION["admin_user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["admin_user"];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_submissions");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: system-ui; margin: 0; background: #f3f4f6; }
        .header { background: #2563eb; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { padding: 8px 16px; background: #6b7280; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div>
            Welcome, <?php echo htmlspecialchars($user["username"]); ?>
            <a href="?logout=1" class="btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h3>Dashboard Overview</h3>
            <p><strong>Total Form Submissions:</strong> <?php echo $stats["total"]; ?></p>
            <p>Contact form system installed successfully!</p>
            <div style="margin-top: 20px;">
                <a href="../" class="btn" style="background: #2563eb; margin-right: 10px;">View Contact Form</a>
                <a href="#" class="btn" style="background: #10b981;">Manage System</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>';

    // Write files
    return file_put_contents(ADMIN_DIR . '/login.php', $login_content) &&
           file_put_contents(ADMIN_DIR . '/index.php', $dashboard_content);
}

function getSiteUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    return rtrim($protocol . $host . $path, '/');
}

// Main execution starts here - determine what to show
$php_check = checkPHPVersion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - Complete Installer</title>
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg: #f9fafb;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: var(--primary);
            color: var(--white);
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .content {
            padding: 32px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 32px;
            gap: 20px;
        }

        .step-item {
            text-align: center;
            opacity: 0.5;
            font-size: 14px;
        }

        .step-item.active {
            opacity: 1;
            color: var(--primary);
            font-weight: 500;
        }

        .step-item.completed {
            opacity: 1;
            color: var(--success);
        }

        .check-results {
            display: grid;
            gap: 16px;
            margin: 24px 0;
        }

        .check-item {
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .check-item.success {
            border-color: var(--success);
            background: #f0fdf4;
        }

        .check-item.warning {
            border-color: var(--warning);
            background: #fffbeb;
        }

        .check-item.error {
            border-color: var(--error);
            background: #fef2f2;
        }

        .check-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .php-requirement {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }

        .actions {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .loading {
            display: none;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
            justify-content: center;
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

        .info-box {
            background: #eff6ff;
            border: 1px solid var(--primary);
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Complete Installation Wizard v<?php echo INSTALLER_VERSION; ?></p>
        </div>

        <div class="content">
            <?php
            // Show PHP version check first if not compatible
            if (!$php_check['status']):
            ?>
                <div class="php-requirement">
                    <h2>⚠️ PHP Version Issue</h2>
                    <p><strong>Current:</strong> <?php echo $php_check['current']; ?></p>
                    <p><strong>Required:</strong> <?php echo MIN_PHP_VERSION; ?>+ (<?php echo RECOMMENDED_PHP_VERSION; ?>+ recommended)</p>
                    <p style="margin-top: 15px;">Please upgrade your PHP version before continuing with installation.</p>

                    <div style="margin-top: 20px; text-align: left;">
                        <h4>How to upgrade PHP:</h4>
                        <ul style="margin: 10px 0 0 20px;">
                            <li><strong>Shared Hosting:</strong> Contact your hosting provider or check cPanel for PHP version settings</li>
                            <li><strong>VPS/Dedicated:</strong> Use your package manager (apt, yum) to update PHP</li>
                            <li><strong>Local Development:</strong> Update your local PHP installation</li>
                        </ul>
                    </div>
                </div>

                <div class="actions">
                    <button onclick="location.reload()" class="btn btn-primary">Check Again After Upgrade</button>
                </div>

            <?php elseif ($step === 0): ?>
                <!-- Environment Check Mode -->
                <div class="step-indicator">
                    <div class="step-item active">Environment Check</div>
                    <div class="step-item">Database Setup</div>
                    <div class="step-item">Admin Account</div>
                    <div class="step-item">Installation</div>
                    <div class="step-item">Complete</div>
                </div>

                <h2>Step 1: Environment Verification</h2>
                <p>Great! Your PHP version (<?php echo $php_check['current']; ?>) meets our requirements. Now checking your server environment automatically...</p>

                <div class="loading active" id="checking">
                    <div class="spinner"></div>
                    <span>Running comprehensive environment checks...</span>
                </div>

                <div id="check-results"></div>

                <form method="POST" id="install-form" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="start_installation">
                    <div class="actions">
                        <button type="submit" class="btn btn-success" style="font-size: 16px; padding: 16px 32px;">
                            ✅ Environment Ready - Start Installation
                        </button>
                    </div>
                </form>

            <?php elseif ($step === 1): ?>
                <!-- Database Setup -->
                <div class="step-indicator">
                    <div class="step-item completed">Environment Check</div>
                    <div class="step-item active">Database Setup</div>
                    <div class="step-item">Admin Account</div>
                    <div class="step-item">Installation</div>
                    <div class="step-item">Complete</div>
                </div>

                <h2>Step 2: Database Configuration</h2>
                <p>Configure your MySQL database connection. The installer will create the database and tables automatically:</p>

                <form id="database-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-input" name="db_host" value="localhost" required>
                        <small style="color: var(--text-light);">Usually 'localhost' for shared hosting</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-input" name="db_name" required>
                        <small style="color: var(--text-light);">Will be created if it doesn't exist</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-input" name="db_username" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-input" name="db_password">
                    </div>

                    <button type="button" class="btn btn-primary" onclick="testDatabase()">
                        Test Connection
                    </button>
                </form>

                <div id="database-messages"></div>

                <div class="actions">
                    <button class="btn btn-success" id="next-step" onclick="installDatabase()" disabled>
                        Create Database & Continue
                    </button>
                </div>

            <?php elseif ($step === 2): ?>
                <!-- Admin Account Creation -->
                <div class="step-indicator">
                    <div class="step-item completed">Environment Check</div>
                    <div class="step-item completed">Database Setup</div>
                    <div class="step-item active">Admin Account</div>
                    <div class="step-item">Installation</div>
                    <div class="step-item">Complete</div>
                </div>

                <h2>Step 3: Create Admin Account</h2>
                <p>Create your administrator account to manage the contact form system:</p>

                <form id="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label class="form-label">Admin Email Address</label>
                        <input type="email" class="form-input" name="admin_email" required>
                        <small style="color: var(--text-light);">Used for notifications and login recovery</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Admin Password</label>
                        <input type="password" class="form-input" name="admin_password" required>
                        <small style="color: var(--text-light);">Minimum 8 characters required</small>
                    </div>

                    <div class="info-box">
                        <p><strong>Important:</strong> Save these credentials safely! Username will be 'admin'.</p>
                    </div>
                </form>

                <div id="admin-messages"></div>

                <div class="actions">
                    <button class="btn btn-success" onclick="createAdmin()">
                        Create Admin Account & Continue
                    </button>
                </div>

            <?php elseif ($step === 3): ?>
                <!-- Final Installation -->
                <div class="step-indicator">
                    <div class="step-item completed">Environment Check</div>
                    <div class="step-item completed">Database Setup</div>
                    <div class="step-item completed">Admin Account</div>
                    <div class="step-item active">Installation</div>
                    <div class="step-item">Complete</div>
                </div>

                <h2>Step 4: Finalizing Installation</h2>
                <p>Creating configuration files and admin interface...</p>

                <div class="loading active" id="installing">
                    <div class="spinner"></div>
                    <span>Setting up your contact form system...</span>
                </div>

                <div id="install-messages"></div>

            <?php elseif ($step === 4): ?>
                <!-- Installation Complete -->
                <div class="step-indicator">
                    <div class="step-item completed">Environment Check</div>
                    <div class="step-item completed">Database Setup</div>
                    <div class="step-item completed">Admin Account</div>
                    <div class="step-item completed">Installation</div>
                    <div class="step-item completed">Complete</div>
                </div>

                <h2>🎉 Installation Complete!</h2>
                <p>Your GlowHost Contact Form System is ready to use.</p>

                <div class="check-item success">
                    <div class="check-icon">✅</div>
                    <div>
                        <h3>Installation Successful</h3>
                        <p>All components have been installed and configured successfully.</p>
                    </div>
                </div>

                <div class="info-box">
                    <h4>What's been created:</h4>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Database tables for users and contact submissions</li>
                        <li>Admin account with username 'admin'</li>
                        <li>Configuration file (config.php)</li>
                        <li>Admin interface in /admin/ directory</li>
                    </ul>
                </div>

                <div class="actions">
                    <a href="<?php echo ADMIN_DIR; ?>/" class="btn btn-primary" style="text-decoration: none; margin-right: 10px;">
                        Access Admin Panel
                    </a>
                    <a href="./" class="btn btn-success" style="text-decoration: none;">
                        View Contact Form
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';

        // Run environment checks on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($step === 0): ?>
                runEnvironmentChecks();
            <?php elseif ($step === 3): ?>
                finalizeInstallation();
            <?php endif; ?>
        });

        async function runEnvironmentChecks() {
            try {
                const response = await fetch('?ajax=check');
                const result = await response.json();

                displayEnvironmentResults(result);
                document.getElementById('install-form').style.display = result.can_proceed ? 'block' : 'none';
            } catch (error) {
                console.error('Environment check failed:', error);
                showMessage('Failed to run environment checks. Please refresh the page.', 'error', 'check-results');
            } finally {
                document.getElementById('checking').classList.remove('active');
            }
        }

        function displayEnvironmentResults(data) {
            let html = '<div class="check-results">';

            // PHP Version
            const php = data.php_version;
            html += '<div class="check-item ' + (php.status ? (php.level === "excellent" ? "success" : "warning") : "error") + '">';
            html += '<div class="check-icon">' + (php.status ? "✅" : "❌") + '</div>';
            html += '<div><h3>PHP Version</h3><p>' + php.message + '</p></div></div>';

            // Extensions
            const hasExtensionIssues = !data.extensions.critical_passed;
            html += '<div class="check-item ' + (hasExtensionIssues ? "error" : "success") + '">';
            html += '<div class="check-icon">' + (hasExtensionIssues ? "❌" : "✅") + '</div>';
            html += '<div><h3>Required Extensions</h3><p>' + (hasExtensionIssues ? "Some critical extensions are missing" : "All required extensions available") + '</p></div></div>';

            // Permissions
            const perm = data.permissions;
            html += '<div class="check-item ' + (perm.status ? "success" : "error") + '">';
            html += '<div class="check-icon">' + (perm.status ? "✅" : "❌") + '</div>';
            html += '<div><h3>Directory Permissions</h3><p>' + perm.message + '</p></div></div>';

            // Connectivity
            const conn = data.connectivity;
            html += '<div class="check-item ' + (conn.status ? "success" : "error") + '">';
            html += '<div class="check-icon">' + (conn.status ? "✅" : "❌") + '</div>';
            html += '<div><h3>Network Connectivity</h3><p>' + conn.message + '</p></div></div>';

            // Existing Installation
            const existing = data.existing_install;
            html += '<div class="check-item ' + (existing.exists ? "warning" : "success") + '">';
            html += '<div class="check-icon">' + (existing.exists ? "⚠️" : "✅") + '</div>';
            html += '<div><h3>Installation Status</h3><p>' + existing.message + '</p></div></div>';

            html += '</div>';

            if (!data.can_proceed) {
                html += '<div class="actions"><p style="color: var(--error); font-weight: 500;">Please resolve the issues above before proceeding with installation.</p></div>';
            }

            document.getElementById('check-results').innerHTML = html;
        }

        async function testDatabase() {
            const formData = new FormData(document.getElementById('database-form'));
            formData.append('action', 'test_database');

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                showMessage(result.success ? "✅ " + result.message : "❌ " + result.message,
                           result.success ? "success" : "error", "database-messages");

                document.getElementById('next-step').disabled = !result.success;
            } catch (error) {
                showMessage("❌ Connection test failed", "error", "database-messages");
            }
        }

        async function installDatabase() {
            const formData = new FormData(document.getElementById('database-form'));
            formData.append('action', 'install_database');

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showMessage("✅ " + result.message, "success", "database-messages");
                    setTimeout(() => window.location.href = '?step=2', 1500);
                } else {
                    showMessage("❌ " + result.message, "error", "database-messages");
                }
            } catch (error) {
                showMessage("❌ Database installation failed", "error", "database-messages");
            }
        }

        async function createAdmin() {
            const formData = new FormData(document.getElementById('admin-form'));
            formData.append('action', 'create_admin');

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showMessage("✅ " + result.message, "success", "admin-messages");
                    setTimeout(() => window.location.href = '?step=3', 1500);
                } else {
                    showMessage("❌ " + result.message, "error", "admin-messages");
                }
            } catch (error) {
                showMessage("❌ Admin account creation failed", "error", "admin-messages");
            }
        }

        async function finalizeInstallation() {
            try {
                const formData = new FormData();
                formData.append('action', 'finalize_installation');
                formData.append('csrf_token', csrfToken);

                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                document.getElementById('installing').classList.remove('active');

                if (result.success) {
                    showMessage("✅ " + result.message, "success", "install-messages");
                    setTimeout(() => window.location.href = '?step=4', 2000);
                } else {
                    showMessage("❌ " + result.message, "error", "install-messages");
                }
            } catch (error) {
                document.getElementById('installing').classList.remove('active');
                showMessage("❌ Installation failed", "error", "install-messages");
            }
        }

        function showMessage(message, type, containerId) {
            const container = document.getElementById(containerId);
            const messageDiv = document.createElement('div');
            messageDiv.className = "check-item " + type;
            messageDiv.innerHTML = '<div class="check-icon">' + (type === "success" ? "✅" : "❌") + '</div><div><p>' + message + '</p></div>';

            container.innerHTML = "";
            container.appendChild(messageDiv);
        }
    </script>
</body>
</html>
