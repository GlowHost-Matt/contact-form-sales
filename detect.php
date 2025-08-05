<?php
/**
 * GlowHost Contact Form - System Requirements Qualification Gateway
 * Version: 5.0 - Comprehensive Environment Analysis
 * 
 * This file serves as a qualification gateway that ensures the system
 * meets all requirements before allowing access to the installer.
 */

// Prevent timeouts during testing
set_time_limit(120);

// Configuration
define('MIN_PHP_VERSION', '7.4.0');
define('RECOMMENDED_PHP_VERSION', '8.1.0');
define('GITHUB_TEST_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/AI-CONTEXT.md');
define('INSTALLER_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php');

// Get current directory info
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$base_url = rtrim($protocol . $host . $path, '/');

// Handle AJAX requests for real-time checking
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check') {
    header('Content-Type: application/json');
    echo json_encode(runSystemQualification());
    exit;
}

/**
 * QUALIFICATION SYSTEM FUNCTIONS
 */
function runSystemQualification() {
    $checks = [
        'php_version' => checkPHPVersion(),
        'extensions' => checkRequiredExtensions(),
        'permissions' => checkDirectoryPermissions(),
        'connectivity' => checkNetworkConnectivity(),
        'existing_install' => checkExistingInstallation()
    ];

    // Calculate overall qualification status
    $checks['qualified'] = 
        $checks['php_version']['status'] &&
        $checks['extensions']['qualified'] &&
        $checks['permissions']['status'] &&
        $checks['connectivity']['status'] &&
        !$checks['existing_install']['blocks_install'];

    return $checks;
}

function checkPHPVersion() {
    $current = PHP_VERSION;
    $meets_min = version_compare($current, MIN_PHP_VERSION, '>=');
    $meets_recommended = version_compare($current, RECOMMENDED_PHP_VERSION, '>=');

    $level = 'error';
    $message = '';
    $instructions = [];

    if ($meets_recommended) {
        $level = 'excellent';
        $message = "PHP $current (Excellent - Latest Features)";
        $instructions = ['✅ Your PHP version is perfect for modern web development'];
    } elseif ($meets_min) {
        $level = 'warning';
        $message = "PHP $current (Compatible - Upgrade Recommended)";
        $instructions = [
            'Your PHP version will work but is not recommended',
            'PHP 7.4 reached end-of-life in November 2022',
            'Consider upgrading to PHP 8.1+ for security and performance'
        ];
    } else {
        $level = 'error';
        $message = "PHP $current (Incompatible - Critical Issue)";
        $instructions = [
            'Your PHP version is too old and poses security risks',
            'Access a hosting control panel (cPanel, Plesk, etc.)',
            'Look for "PHP Version", "MultiPHP Manager", or "PHP Selector"',
            'Select PHP 8.1 or higher',
            'Save changes and wait 2-3 minutes for activation',
            'Refresh this page to verify the update'
        ];
    }

    return [
        'status' => $meets_min,
        'level' => $level,
        'current' => $current,
        'required' => MIN_PHP_VERSION,
        'recommended' => RECOMMENDED_PHP_VERSION,
        'message' => $message,
        'instructions' => $instructions,
        'context' => 'PHP version determines available features, security level, and compatibility with modern web standards.'
    ];
}

function checkRequiredExtensions() {
    $extensions = [
        'cURL or allow_url_fopen' => [
            'critical' => true,
            'check' => function() {
                return function_exists('curl_init') || ini_get('allow_url_fopen');
            },
            'context' => 'Required for downloading files and connecting to external services'
        ],
        'PDO' => [
            'critical' => true,
            'check' => function() {
                return class_exists('PDO');
            },
            'context' => 'Essential for secure database connections and operations'
        ],
        'ZipArchive' => [
            'critical' => true,
            'check' => function() {
                return class_exists('ZipArchive');
            },
            'context' => 'Needed for extracting and creating compressed files during installation'
        ],
        'mbstring' => [
            'critical' => false,
            'check' => function() {
                return extension_loaded('mbstring');
            },
            'context' => 'Recommended for proper handling of international characters'
        ],
        'OpenSSL' => [
            'critical' => false,
            'check' => function() {
                return extension_loaded('openssl');
            },
            'context' => 'Recommended for secure HTTPS connections and encryption'
        ]
    ];

    $results = [];
    $qualified = true;
    $critical_missing = [];

    foreach ($extensions as $name => $config) {
        $check_func = $config['check'];
        $status = $check_func();

        $results[$name] = [
            'status' => $status,
            'critical' => $config['critical'],
            'context' => $config['context'],
            'level' => $status ? 'success' : ($config['critical'] ? 'error' : 'warning')
        ];

        if ($config['critical'] && !$status) {
            $qualified = false;
            $critical_missing[] = $name;
        }
    }

    $instructions = [];
    if (!empty($critical_missing)) {
        $instructions = [
            'Contact your hosting provider to enable missing extensions',
            'For cPanel: Look for "PHP Extensions" or "MultiPHP Extensions"',
            'For VPS/Dedicated: Install via package manager (apt, yum)',
            'Missing critical extensions: ' . implode(', ', $critical_missing)
        ];
    } else {
        $instructions = ['✅ All required PHP extensions are available'];
    }

    return [
        'qualified' => $qualified,
        'extensions' => $results,
        'critical_missing' => $critical_missing,
        'instructions' => $instructions
    ];
}

function checkDirectoryPermissions() {
    $current_dir = __DIR__;
    $writable = is_writable($current_dir);

    $instructions = [];
    if ($writable) {
        $instructions = ['✅ Directory has proper write permissions'];
    } else {
        $instructions = [
            'The current directory is not writable',
            'For shared hosting: Contact support to fix permissions',
            'For cPanel: Use File Manager to set directory permissions to 755',
            'For SSH access: Run "chmod 755 ' . basename($current_dir) . '"',
            'Ensure the web server can write to this directory'
        ];
    }

    return [
        'status' => $writable,
        'directory' => $current_dir,
        'level' => $writable ? 'success' : 'error',
        'message' => $writable ? 'Directory is writable' : 'Directory is not writable',
        'instructions' => $instructions,
        'context' => 'Write permissions are needed to create configuration files and download components.'
    ];
}

function checkNetworkConnectivity() {
    $test_url = GITHUB_TEST_URL;
    $timeout = 15;

    // Try cURL first
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $test_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // More lenient for testing
            CURLOPT_USERAGENT => 'GlowHost-Detector/5.0'
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result !== false && $http_code === 200) {
            return [
                'status' => true,
                'method' => 'cURL',
                'level' => 'success',
                'message' => 'GitHub connectivity successful via cURL',
                'instructions' => ['✅ Can download installer and updates from GitHub'],
                'context' => 'Network connectivity ensures the installer can download necessary files.'
            ];
        } else {
            return [
                'status' => false,
                'method' => 'cURL',
                'level' => 'error',
                'message' => "Connection failed: $error (HTTP $http_code)",
                'instructions' => [
                    'Check if your server blocks outbound connections',
                    'Contact hosting provider about firewall restrictions',
                    'Verify that GitHub.com is accessible from your server',
                    'Some shared hosts block external connections for security'
                ],
                'context' => 'Network connectivity ensures the installer can download necessary files.'
            ];
        }
    }

    // Fallback to file_get_contents
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => 'GlowHost-Detector/5.0'
            ]
        ]);

        $result = @file_get_contents($test_url, false, $context);
        if ($result !== false) {
            return [
                'status' => true,
                'method' => 'file_get_contents',
                'level' => 'success',
                'message' => 'GitHub connectivity successful',
                'instructions' => ['✅ Can download installer and updates from GitHub'],
                'context' => 'Network connectivity ensures the installer can download necessary files.'
            ];
        }
    }

    return [
        'status' => false,
        'method' => 'none',
        'level' => 'error',
        'message' => 'No download method available or connection blocked',
        'instructions' => [
            'Both cURL and allow_url_fopen are disabled or blocked',
            'Contact your hosting provider to enable external connections',
            'This is required for downloading the installer files'
        ],
        'context' => 'Network connectivity ensures the installer can download necessary files.'
    ];
}

function checkExistingInstallation() {
    $config_exists = file_exists('config.php');
    $admin_exists = is_dir('admin');
    $installer_exists = file_exists('install.php');

    $blocks_install = $config_exists && $admin_exists;
    $level = 'success';
    $message = 'Ready for fresh installation';
    $instructions = ['✅ No existing installation detected'];

    if ($blocks_install) {
        $level = 'warning';
        $message = 'Complete installation already exists';
        $instructions = [
            'A full installation already exists in this directory',
            'config.php and admin directory are present',
            'To reinstall: Remove config.php and admin/ directory first',
            'Consider backing up your data before reinstalling'
        ];
    } elseif ($config_exists || $admin_exists || $installer_exists) {
        $level = 'warning';
        $message = 'Partial installation detected';
        $instructions = [
            'Some installation files exist:',
            $config_exists ? '• config.php found' : '',
            $admin_exists ? '• admin/ directory found' : '',
            $installer_exists ? '• install.php found' : '',
            'You may proceed, but consider cleaning up old files first'
        ];
        $instructions = array_filter($instructions); // Remove empty strings
    }

    return [
        'blocks_install' => $blocks_install,
        'config_exists' => $config_exists,
        'admin_exists' => $admin_exists,
        'installer_exists' => $installer_exists,
        'level' => $level,
        'message' => $message,
        'instructions' => $instructions,
        'context' => 'Existing installations may conflict with new installations.'
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Requirements Check - GlowHost Contact Form</title>
    <style>
        /* Official GlowHost Brand Colors */
        :root {
            --glowhost-navy: #061c63;
            --glowhost-blue: #1e3b97;
            --glowhost-bright: #4164dd;
            --glowhost-light: #7b95f1;
            --glowhost-navy-light: #1a2b5c;
            --glowhost-blue-light: #2d4ba3;
            --glowhost-bg-light: #f0f4ff;
            --glowhost-bg-subtle: #e8efff;
            --cyan-accent: #52cfe5;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            --green-50: #f0fdf4;
            --green-100: #dcfce7;
            --green-600: #16a34a;
            --green-700: #15803d;
            --yellow-50: #fffbeb;
            --yellow-100: #fef3c7;
            --yellow-600: #d97706;
            --yellow-700: #b45309;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--glowhost-bg-subtle) 100%);
            min-height: 100vh;
            line-height: 1.6;
            font-size: 16px;
            color: var(--gray-800);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Modern GlowHost Header */
        .glowhost-header {
            background: linear-gradient(135deg, var(--glowhost-blue) 0%, var(--glowhost-navy) 100%);
            color: white;
            padding: 1.5rem 0;
            position: relative;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .glowhost-logo {
            height: 40px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transition: transform 0.2s ease;
        }

        .glowhost-logo:hover {
            transform: scale(1.02);
        }

        .support-info {
            text-align: right;
            font-size: 0.875rem;
        }

        .support-hours {
            color: var(--cyan-accent);
            margin-bottom: 0.25rem;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .support-phone {
            font-weight: 600;
        }

        .support-phone a {
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .support-phone a:hover {
            color: var(--cyan-accent);
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            color: var(--glowhost-navy);
            font-size: 2.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, var(--glowhost-navy) 0%, var(--glowhost-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: var(--gray-600);
            font-size: 1.25rem;
            font-weight: 400;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Dashboard Container */
        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .dashboard-container:hover {
            transform: translateY(-2px);
            box-shadow:
                0 32px 64px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.3);
        }

        /* Status Overview */
        .status-overview {
            background: linear-gradient(135deg, var(--glowhost-bg-light) 0%, var(--glowhost-bg-subtle) 100%);
            padding: 2rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .overview-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .overall-status {
            flex: 1;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .status-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .status-qualified {
            background: var(--green-100);
            color: var(--green-700);
        }

        .status-unqualified {
            background: var(--red-100);
            color: var(--red-700);
        }

        .status-text h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-qualified h2 {
            color: var(--green-700);
        }

        .status-unqualified h2 {
            color: var(--red-700);
        }

        .status-text p {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .qualification-actions {
            text-align: right;
        }

        .loading-container {
            text-align: center;
            padding: 3rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 3rem;
            height: 3rem;
            border: 4px solid var(--gray-200);
            border-radius: 50%;
            border-top-color: var(--glowhost-bright);
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 1rem;
        }

        .loading-text {
            font-size: 1.125rem;
            color: var(--gray-600);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Check Results Grid */
        .check-results {
            padding: 2rem;
        }

        .checks-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .check-item {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .check-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gray-200);
            transition: all 0.3s ease;
        }

        .check-item.excellent {
            background: var(--green-50);
            border-color: var(--green-600);
        }

        .check-item.excellent::before {
            background: linear-gradient(90deg, var(--green-600), var(--green-700));
        }

        .check-item.success {
            background: var(--green-50);
            border-color: var(--green-600);
        }

        .check-item.success::before {
            background: linear-gradient(90deg, var(--green-600), var(--green-700));
        }

        .check-item.warning {
            background: var(--yellow-50);
            border-color: var(--yellow-600);
        }

        .check-item.warning::before {
            background: linear-gradient(90deg, var(--yellow-600), var(--yellow-700));
        }

        .check-item.error {
            background: var(--red-50);
            border-color: var(--red-600);
        }

        .check-item.error::before {
            background: linear-gradient(90deg, var(--red-600), var(--red-700));
        }

        .check-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .check-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .check-item.excellent .check-icon,
        .check-item.success .check-icon {
            background: var(--green-100);
            color: var(--green-700);
        }

        .check-item.warning .check-icon {
            background: var(--yellow-100);
            color: var(--yellow-700);
        }

        .check-item.error .check-icon {
            background: var(--red-100);
            color: var(--red-700);
        }

        .check-title {
            flex: 1;
        }

        .check-title h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .check-title .check-status {
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0.8;
        }

        .check-context {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            font-style: italic;
        }

        .check-instructions {
            margin-top: 1rem;
        }

        .check-instructions h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-800);
        }

        .check-instructions ul {
            list-style: none;
            padding: 0;
        }

        .check-instructions li {
            padding: 0.25rem 0;
            font-size: 0.875rem;
            color: var(--gray-700);
            position: relative;
            padding-left: 1.5rem;
        }

        .check-instructions li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--glowhost-bright);
            font-weight: bold;
        }

        .check-instructions li:first-child::before {
            content: '→';
        }

        /* Extension Details */
        .extension-details {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .extension-grid {
            display: grid;
            gap: 0.75rem;
        }

        .extension-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.7);
        }

        .extension-name {
            font-weight: 500;
        }

        .extension-status {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 600;
        }

        .extension-status.available {
            color: var(--green-700);
        }

        .extension-status.missing {
            color: var(--red-700);
        }

        .extension-status.optional {
            color: var(--yellow-700);
        }

        /* Action Buttons */
        .qualification-actions {
            padding: 2rem;
            background: var(--gray-50);
            text-align: center;
            border-top: 1px solid var(--gray-200);
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--glowhost-bright) 0%, var(--glowhost-blue) 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--green-600) 0%, var(--green-700) 100%);
            color: white;
        }

        .btn-refresh {
            background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
            color: white;
        }

        .download-instructions {
            background: var(--glowhost-bg-light);
            border: 2px solid var(--glowhost-bright);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .download-instructions h3 {
            color: var(--glowhost-navy);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .command-box {
            background: var(--gray-900);
            color: var(--green-600);
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875rem;
            font-weight: bold;
            margin: 1rem 0;
            border-left: 4px solid var(--glowhost-bright);
            overflow-x: auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .support-info {
                text-align: center;
            }

            .main-container {
                margin: 1rem auto;
                padding: 0 1rem;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .overview-content {
                flex-direction: column;
                text-align: center;
            }

            .qualification-actions {
                text-align: center;
            }

            .checks-grid {
                gap: 1rem;
            }

            .check-item {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Official GlowHost Header -->
    <header class="glowhost-header">
        <div class="header-container">
            <img src="https://glowhost.com/wp-content/uploads/logo-sans-tagline.png"
                 alt="GlowHost" class="glowhost-logo" />
            <div class="support-info">
                <div class="support-hours">24 / 7 / 365 Support</div>
                <div class="support-phone">
                    Toll Free Sales <a href="tel:+18882934678">1 (888) 293-HOST</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <div class="page-title">
            <h1>System Requirements Check</h1>
            <p>Comprehensive qualification gateway ensuring your server meets all requirements for the GlowHost Contact Form installation</p>
        </div>

        <div class="dashboard-container">
            <!-- Status Overview -->
            <div class="status-overview">
                <div class="loading-container" id="loading-container">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Running comprehensive system analysis...</div>
                </div>

                <div class="overview-content" id="overview-content" style="display: none;">
                    <div class="overall-status">
                        <div class="status-indicator">
                            <div class="status-icon" id="status-icon">?</div>
                            <div class="status-text">
                                <h2 id="status-title">Analyzing...</h2>
                                <p id="status-description">Please wait while we check your system</p>
                            </div>
                        </div>
                    </div>
                    <div class="qualification-actions">
                        <button class="btn btn-refresh" onclick="refreshChecks()">
                            🔄 Refresh Checks
                        </button>
                    </div>
                </div>
            </div>

            <!-- Check Results -->
            <div class="check-results">
                <div class="checks-grid" id="checks-grid">
                    <!-- Results will be populated here -->
                </div>

                <!-- Qualification Actions -->
                <div class="qualification-actions" id="qualification-actions">
                    <!-- Actions will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Run checks on page load
        document.addEventListener('DOMContentLoaded', function() {
            runQualificationChecks();
        });

        async function runQualificationChecks() {
            try {
                const response = await fetch('?ajax=check');
                const results = await response.json();
                
                displayResults(results);
            } catch (error) {
                console.error('Qualification check failed:', error);
                showError('Failed to run system checks. Please refresh the page.');
            } finally {
                hideLoading();
            }
        }

        function displayResults(data) {
            updateOverviewStatus(data.qualified);
            displayCheckResults(data);
            displayQualificationActions(data.qualified);
        }

        function updateOverviewStatus(qualified) {
            const statusIcon = document.getElementById('status-icon');
            const statusTitle = document.getElementById('status-title');
            const statusDescription = document.getElementById('status-description');

            if (qualified) {
                statusIcon.textContent = '✅';
                statusIcon.className = 'status-icon status-qualified';
                statusTitle.textContent = 'System Qualified';
                statusTitle.className = '';
                statusDescription.textContent = 'Your server meets all requirements and is ready for installation';
            } else {
                statusIcon.textContent = '❌';
                statusIcon.className = 'status-icon status-unqualified';
                statusTitle.textContent = 'System Not Qualified';
                statusTitle.className = '';
                statusDescription.textContent = 'Please address the issues below before proceeding with installation';
            }
        }

        function displayCheckResults(data) {
            const container = document.getElementById('checks-grid');
            let html = '';

            // PHP Version Check
            const php = data.php_version;
            html += createCheckItem(
                'PHP Version',
                php.level,
                getStatusIcon(php.level),
                php.message,
                php.context,
                php.instructions
            );

            // Extensions Check
            const ext = data.extensions;
            const extStatus = ext.qualified ? 'success' : 'error';
            let extDetails = '<div class="extension-details"><div class="extension-grid">';
            
            for (const [name, details] of Object.entries(ext.extensions)) {
                const statusClass = details.status ? 'available' : (details.critical ? 'missing' : 'optional');
                const statusText = details.status ? '✅ Available' : (details.critical ? '❌ Missing' : '⚠️ Optional');
                
                extDetails += `
                    <div class="extension-item">
                        <div class="extension-name">${name}${details.critical ? ' (Critical)' : ''}</div>
                        <div class="extension-status ${statusClass}">${statusText}</div>
                    </div>
                `;
            }
            extDetails += '</div></div>';

            html += createCheckItem(
                'PHP Extensions',
                extStatus,
                getStatusIcon(extStatus),
                ext.qualified ? 'All required extensions available' : 'Missing critical extensions',
                'PHP extensions provide additional functionality required by the installer.',
                ext.instructions,
                extDetails
            );

            // Permissions Check
            const perm = data.permissions;
            html += createCheckItem(
                'Directory Permissions',
                perm.level,
                getStatusIcon(perm.level),
                perm.message,
                perm.context,
                perm.instructions
            );

            // Connectivity Check
            const conn = data.connectivity;
            html += createCheckItem(
                'Network Connectivity',
                conn.level,
                getStatusIcon(conn.level),
                conn.message,
                conn.context,
                conn.instructions
            );

            // Existing Installation Check
            const existing = data.existing_install;
            html += createCheckItem(
                'Installation Status',
                existing.level,
                getStatusIcon(existing.level),
                existing.message,
                existing.context,
                existing.instructions
            );

            container.innerHTML = html;
        }

        function createCheckItem(title, level, icon, message, context, instructions, additionalContent = '') {
            const instructionsList = instructions.map(instruction => 
                `<li>${instruction}</li>`
            ).join('');

            return `
                <div class="check-item ${level}">
                    <div class="check-header">
                        <div class="check-icon">${icon}</div>
                        <div class="check-title">
                            <h3>${title}</h3>
                            <div class="check-status">${message}</div>
                        </div>
                    </div>
                    <div class="check-context">${context}</div>
                    ${additionalContent}
                    <div class="check-instructions">
                        <h4>${level === 'success' || level === 'excellent' ? 'Status' : 'Resolution Steps'}:</h4>
                        <ul>${instructionsList}</ul>
                    </div>
                </div>
            `;
        }

        function getStatusIcon(level) {
            switch (level) {
                case 'excellent': return '🌟';
                case 'success': return '✅';
                case 'warning': return '⚠️';
                case 'error': return '❌';
                default: return '❓';
            }
        }

        function displayQualificationActions(qualified) {
            const container = document.getElementById('qualification-actions');
            
            if (qualified) {
                container.innerHTML = `
                    <div class="download-instructions">
                        <h3>🎉 Congratulations! Your System is Qualified</h3>
                        <p>All requirements have been met. You can now proceed with downloading and installing the Contact Form system.</p>
                        
                        <h4>Step 1: Download the Installer</h4>
                        <p>Run this command in your terminal/SSH to download the installer:</p>
                        <div class="command-box">wget ${INSTALLER_URL} -O install.php</div>
                        
                        <h4>Step 2: Run the Installation</h4>
                        <p>After the download completes, visit <strong>install.php</strong> in your browser to begin the installation process.</p>
                    </div>
                    
                    <a href="install.php" class="btn btn-success" style="font-size: 1.125rem; padding: 1.25rem 2.5rem;">
                        🚀 Start Installation (After Download)
                    </a>
                `;
            } else {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem; background: var(--red-50); border-radius: 12px; border: 2px solid var(--red-600);">
                        <h3 style="color: var(--red-700); margin-bottom: 1rem;">⛔ Installation Blocked</h3>
                        <p style="color: var(--red-600); margin-bottom: 1.5rem;">
                            Please resolve all critical issues above before proceeding with installation.
                            The installer will not function properly with the current system configuration.
                        </p>
                        <button class="btn btn-refresh" onclick="refreshChecks()">
                            🔄 Re-check After Fixes
                        </button>
                    </div>
                `;
            }
        }

        function hideLoading() {
            document.getElementById('loading-container').style.display = 'none';
            document.getElementById('overview-content').style.display = 'flex';
        }

        function refreshChecks() {
            // Show loading again
            document.getElementById('loading-container').style.display = 'block';
            document.getElementById('overview-content').style.display = 'none';
            
            // Clear results
            document.getElementById('checks-grid').innerHTML = '';
            document.getElementById('qualification-actions').innerHTML = '';
            
            // Re-run checks
            runQualificationChecks();
        }

        function showError(message) {
            document.getElementById('checks-grid').innerHTML = `
                <div class="check-item error">
                    <div class="check-header">
                        <div class="check-icon">❌</div>
                        <div class="check-title">
                            <h3>System Check Failed</h3>
                            <div class="check-status">${message}</div>
                        </div>
                    </div>
                </div>
            `;
        }
    </script>
</body>
</html>