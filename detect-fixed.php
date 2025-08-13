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
            border-radius: 0.25rem;
        }

        .support-phone a:hover {
            background: rgba(255, 255, 255, 0.1);
            text-decoration: underline;
        }

        /* Main Content */
        .main-content {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            background: linear-gradient(135deg, var(--glowhost-bright) 0%, var(--glowhost-blue) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        }

        .card-header > * {
            position: relative;
            z-index: 1;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .card-body {
            padding: 2rem;
        }

        /* Status Summary */
        .status-summary {
            background: var(--glowhost-bg-light);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--glowhost-bright);
        }

        .status-summary.qualified {
            background: var(--green-50);
            border-left-color: var(--green-600);
        }

        .status-summary.issues {
            background: var(--red-50);
            border-left-color: var(--red-600);
        }

        .status-text {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .status-description {
            color: var(--gray-600);
        }

        /* Check Results Grid */
        .checks-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .check-card {
            background: var(--gray-50);
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .check-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .check-card.success {
            border-color: var(--green-600);
            background: var(--green-50);
        }

        .check-card.warning {
            border-color: var(--yellow-600);
            background: var(--yellow-50);
        }

        .check-card.error {
            border-color: var(--red-600);
            background: var(--red-50);
        }

        .check-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .check-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .check-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .check-message {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .check-context {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-style: italic;
            margin-bottom: 1rem;
        }

        .check-instructions {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid var(--gray-200);
        }

        .check-instructions h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .check-instructions ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .check-instructions li {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
            padding-left: 1rem;
            position: relative;
        }

        .check-instructions li::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: var(--glowhost-bright);
            font-weight: bold;
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 2rem 0;
            border-top: 1px solid var(--gray-200);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--glowhost-bright);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--glowhost-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(65, 100, 221, 0.3);
        }

        .btn-secondary {
            background: var(--gray-600);
            color: white;
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--gray-700);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--green-600);
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: var(--green-700);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        /* Loading States */
        .spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .main-content {
                margin: 1rem auto;
                padding: 0 1rem;
            }

            .card-header {
                padding: 1.5rem 1rem;
            }

            .card-title {
                font-size: 1.5rem;
            }

            .card-subtitle {
                font-size: 1rem;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        /* Enhanced Visual Elements */
        .system-info {
            background: var(--glowhost-bg-light);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 2rem;
            border: 1px solid var(--glowhost-bright);
        }

        .system-info h4 {
            color: var(--glowhost-navy);
            margin-bottom: 0.5rem;
        }

        .system-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .system-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: white;
            border-radius: 0.25rem;
        }

        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background: var(--gray-200);
            border-radius: 0.25rem;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--glowhost-bright) 0%, var(--cyan-accent) 100%);
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- GlowHost Header -->
    <header class="glowhost-header">
        <div class="header-container">
            <div class="logo-section">
                <div style="font-size: 1.5rem; font-weight: bold;">
                    🌟 GlowHost
                </div>
                <div style="font-size: 0.875rem; opacity: 0.8;">
                    Premium Web Hosting
                </div>
            </div>
            <div class="support-info">
                <div class="support-hours">24/7 Expert Support</div>
                <div class="support-phone">
                    <a href="tel:+1-888-293-4678">📞 1-888-GLOW-HOST</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">🔍 System Requirements Check</h1>
                <p class="card-subtitle">
                    Comprehensive environment analysis for GlowHost Contact Form System v5.0
                </p>
            </div>

            <div class="card-body">
                <!-- Status Summary -->
                <div id="status-summary" class="status-summary">
                    <div class="status-text">🔄 Initializing System Check...</div>
                    <div class="status-description">
                        Please wait while we analyze your server environment for compatibility.
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 0%" id="progress-fill"></div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="system-info">
                    <h4>📋 Current System Information</h4>
                    <div class="system-info-grid">
                        <div class="system-info-item">
                            <span>PHP Version:</span>
                            <strong><?php echo PHP_VERSION; ?></strong>
                        </div>
                        <div class="system-info-item">
                            <span>Server Software:</span>
                            <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong>
                        </div>
                        <div class="system-info-item">
                            <span>Current Directory:</span>
                            <strong><?php echo basename(__DIR__); ?></strong>
                        </div>
                        <div class="system-info-item">
                            <span>Check Time:</span>
                            <strong id="check-timestamp"><?php echo date('Y-m-d H:i:s'); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Check Results -->
                <div id="checks-grid" class="checks-grid">
                    <!-- Check results will be populated here -->
                </div>

                <!-- Actions -->
                <div class="actions">
                    <button id="refresh-btn" class="btn btn-secondary" onclick="runQualificationCheck()">
                        🔄 Refresh Check
                    </button>
                    <button id="download-installer-btn" class="btn btn-primary" onclick="downloadInstaller()" disabled>
                        ⬇️ Download Installer
                    </button>
                    <button id="proceed-btn" class="btn btn-success" onclick="proceedToInstaller()" disabled>
                        🚀 Proceed to Installation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let qualificationResults = null;

        // Run qualification check on page load
        document.addEventListener('DOMContentLoaded', function() {
            runQualificationCheck();
        });

        async function runQualificationCheck() {
            const refreshBtn = document.getElementById('refresh-btn');
            const statusSummary = document.getElementById('status-summary');
            const progressFill = document.getElementById('progress-fill');
            const timestamp = document.getElementById('check-timestamp');
            
            // Update UI
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<div class="spinner"></div> Checking...';
            statusSummary.className = 'status-summary';
            statusSummary.innerHTML = `
                <div class="status-text">🔄 Running System Qualification...</div>
                <div class="status-description">Analyzing server environment and requirements...</div>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: 0%" id="progress-fill"></div>
                </div>
            `;

            // Simulate progress
            animateProgress();

            try {
                const response = await fetch('?ajax=check');
                qualificationResults = await response.json();
                
                displayQualificationResults(qualificationResults);
                updateActionButtons(qualificationResults);
                
                // Update timestamp
                timestamp.textContent = new Date().toLocaleString();
                
            } catch (error) {
                console.error('Qualification check failed:', error);
                statusSummary.className = 'status-summary issues';
                statusSummary.innerHTML = `
                    <div class="status-text">❌ Check Failed</div>
                    <div class="status-description">Unable to perform qualification check. Please try again.</div>
                `;
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '🔄 Refresh Check';
            }
        }

        function animateProgress() {
            const progressFill = document.getElementById('progress-fill');
            let width = 0;
            const interval = setInterval(() => {
                width += Math.random() * 20;
                if (width >= 100) {
                    width = 100;
                    clearInterval(interval);
                }
                progressFill.style.width = width + '%';
            }, 200);
        }

        function displayQualificationResults(results) {
            const statusSummary = document.getElementById('status-summary');
            const checksGrid = document.getElementById('checks-grid');
            
            // Update status summary
            if (results.qualified) {
                statusSummary.className = 'status-summary qualified';
                statusSummary.innerHTML = `
                    <div class="status-text">✅ System Qualified!</div>
                    <div class="status-description">Your server meets all requirements. Ready to proceed with installation.</div>
                `;
            } else {
                const issueCount = Object.values(results).filter(check => 
                    check && typeof check === 'object' && (check.status === false || check.qualified === false)
                ).length;
                
                statusSummary.className = 'status-summary issues';
                statusSummary.innerHTML = `
                    <div class="status-text">⚠️ ${issueCount} Issue${issueCount > 1 ? 's' : ''} Found</div>
                    <div class="status-description">Please resolve the issues below before proceeding.</div>
                `;
            }

            // Display individual checks
            let checksHtml = '';

            // PHP Version Check
            if (results.php_version) {
                checksHtml += createCheckCard(
                    'PHP Version',
                    results.php_version.message,
                    results.php_version.context,
                    results.php_version.instructions,
                    results.php_version.level,
                    getIconForLevel(results.php_version.level)
                );
            }

            // Extensions Check
            if (results.extensions) {
                checksHtml += createCheckCard(
                    'PHP Extensions',
                    results.extensions.qualified ? 'All required extensions available' : 'Missing critical extensions',
                    'PHP extensions provide additional functionality needed by the system.',
                    results.extensions.instructions,
                    results.extensions.qualified ? 'success' : 'error',
                    results.extensions.qualified ? '✅' : '❌'
                );
            }

            // Permissions Check
            if (results.permissions) {
                checksHtml += createCheckCard(
                    'Directory Permissions',
                    results.permissions.message,
                    results.permissions.context,
                    results.permissions.instructions,
                    results.permissions.level,
                    getIconForLevel(results.permissions.level)
                );
            }

            // Connectivity Check
            if (results.connectivity) {
                checksHtml += createCheckCard(
                    'Network Connectivity',
                    results.connectivity.message,
                    results.connectivity.context,
                    results.connectivity.instructions,
                    results.connectivity.level,
                    getIconForLevel(results.connectivity.level)
                );
            }

            // Existing Installation Check
            if (results.existing_install) {
                checksHtml += createCheckCard(
                    'Installation Status',
                    results.existing_install.message,
                    results.existing_install.context,
                    results.existing_install.instructions,
                    results.existing_install.level,
                    getIconForLevel(results.existing_install.level)
                );
            }

            checksGrid.innerHTML = checksHtml;
        }

        function createCheckCard(title, message, context, instructions, level, icon) {
            const instructionsList = instructions.map(instruction => 
                `<li>${instruction}</li>`
            ).join('');

            return `
                <div class="check-card ${level}">
                    <div class="check-header">
                        <div class="check-icon">${icon}</div>
                        <div class="check-title">${title}</div>
                    </div>
                    <div class="check-message">${message}</div>
                    <div class="check-context">${context}</div>
                    <div class="check-instructions">
                        <h4>Instructions:</h4>
                        <ul>${instructionsList}</ul>
                    </div>
                </div>
            `;
        }

        function getIconForLevel(level) {
            switch (level) {
                case 'success':
                case 'excellent':
                    return '✅';
                case 'warning':
                    return '⚠️';
                case 'error':
                    return '❌';
                default:
                    return '❓';
            }
        }

        function updateActionButtons(results) {
            const downloadBtn = document.getElementById('download-installer-btn');
            const proceedBtn = document.getElementById('proceed-btn');
            
            if (results.qualified) {
                downloadBtn.disabled = false;
                proceedBtn.disabled = false;
            } else {
                downloadBtn.disabled = true;
                proceedBtn.disabled = true;
            }
        }

        async function downloadInstaller() {
            const downloadBtn = document.getElementById('download-installer-btn');
            downloadBtn.disabled = true;
            downloadBtn.innerHTML = '<div class="spinner"></div> Downloading...';

            try {
                const link = document.createElement('a');
                link.href = '<?php echo INSTALLER_URL; ?>';
                link.download = 'install.php';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                setTimeout(() => {
                    downloadBtn.innerHTML = '✅ Downloaded';
                    downloadBtn.style.background = 'var(--green-600)';
                }, 1000);
                
            } catch (error) {
                downloadBtn.innerHTML = '❌ Download Failed';
                downloadBtn.style.background = 'var(--red-600)';
            }
        }

        function proceedToInstaller() {
            if (confirm('Ready to proceed with installation?\n\nThis will redirect you to the installer.')) {
                window.location.href = 'install.php';
            }
        }
    </script>
</body>
</html>