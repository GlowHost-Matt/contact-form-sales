<?php
/**
 * GlowHost Contact Form System - Interactive Environment Diagnostic
 * Version: 3.3 - Always Show Diagnostics UI
 *
 * Interactive diagnostic interface that shows environment status and allows
 * users to refresh checks as they configure their server environment.
 */

// Prevent timeouts during installation
@set_time_limit(300);
@ini_set('max_execution_time', 300);

// Configuration
define('GH_ZIP_URL', 'https://github.com/GlowHost-Matt/contact-form-sales/archive/refs/heads/main.zip');
define('GH_TEST_URL', 'https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/AI-CONTEXT.md');
define('MIN_PHP', '7.4.0');
define('INSTALL_DIR', 'install');

// Handle AJAX requests for real-time checking
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check') {
    header('Content-Type: application/json');
    echo json_encode(runAllChecks());
    exit;
}

// Handle installation trigger
if (isset($_POST['start_installation']) && $_POST['start_installation'] === '1') {
    $checks = runAllChecks();
    if ($checks['can_proceed']) {
        performInstallation();
    } else {
        $error_message = "Cannot proceed - some requirements are not met.";
    }
}

/**
 * Run all environment checks and return results
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
    $meets_min = version_compare($current, MIN_PHP, '>=');
    $meets_recommended = version_compare($current, '8.1.0', '>=');

    return [
        'status' => $meets_min,
        'level' => $meets_recommended ? 'excellent' : ($meets_min ? 'good' : 'error'),
        'current' => $current,
        'required' => MIN_PHP,
        'message' => $meets_min ?
            ($meets_recommended ? "PHP $current (Excellent)" : "PHP $current (Compatible)") :
            "PHP $current - Requires $current+"
    ];
}

function checkExtensions() {
    $required = [
        'ZipArchive' => ['critical' => true, 'check' => 'class_exists', 'name' => 'ZipArchive'],
        'cURL' => ['critical' => true, 'check' => 'function_exists', 'name' => 'curl_init'],
        'allow_url_fopen' => ['critical' => false, 'check' => 'ini_get', 'name' => 'allow_url_fopen'],
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
    $results = [];
    $timeout = 15;

    // Test multiple endpoints for comprehensive diagnostics
    $test_endpoints = [
        'github_raw' => GH_TEST_URL,
        'github_api' => 'https://api.github.com/repos/GlowHost-Matt/contact-form-sales',
        'connectivity_test' => 'https://httpbin.org/status/200'  // Third-party test
    ];

    $overall_success = false;
    $detailed_results = [];

    foreach ($test_endpoints as $name => $url) {
        $endpoint_result = testSingleEndpoint($url, $timeout, $name);
        $detailed_results[$name] = $endpoint_result;

        if ($endpoint_result['success']) {
            $overall_success = true;
        }
    }

    // Determine primary message based on results
    if ($overall_success) {
        $message = 'Network connectivity successful';
        if ($detailed_results['github_raw']['success']) {
            $message = 'GitHub connectivity confirmed - ready for installation';
        }
    } else {
        // Detailed diagnostic message
        $issues = [];
        if (!$detailed_results['connectivity_test']['success']) {
            $issues[] = 'General internet connectivity blocked';
        }
        if (!$detailed_results['github_api']['success'] && !$detailed_results['github_raw']['success']) {
            $issues[] = 'GitHub access blocked (firewall/DNS)';
        }

        $message = 'Connection failed: ' . implode(', ', $issues ?: ['Unknown network issue']);
        $message .= ' | Search: "' . implode(' ', $issues) . ' shared hosting"';
    }

    return [
        'status' => $overall_success,
        'message' => $message,
        'details' => $detailed_results,
        'primary_test' => $detailed_results['github_raw'] ?? null
    ];
}

function testSingleEndpoint($url, $timeout, $name) {
    // Test with cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'GlowHost-Installer/3.3',
            CURLOPT_NOBODY => true  // HEAD request for faster testing
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return [
            'success' => ($result !== false && $http_code >= 200 && $http_code < 400),
            'method' => 'cURL',
            'http_code' => $http_code,
            'error' => $error,
            'url' => $url,
            'response_time' => $info['total_time'] ?? 0,
            'message' => $result !== false && $http_code >= 200 && $http_code < 400
                ? "$name accessible (HTTP $http_code)"
                : "$name failed - HTTP $http_code: $error"
        ];
    }

    // Test with file_get_contents if allow_url_fopen is enabled
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => 'GlowHost-Installer/3.3',
                'method' => 'HEAD'
            ]
        ]);

        $start_time = microtime(true);
        $result = @file_get_contents($url, false, $context);
        $response_time = microtime(true) - $start_time;

        if ($result !== false) {
            return [
                'success' => true,
                'method' => 'file_get_contents',
                'url' => $url,
                'response_time' => $response_time,
                'message' => "$name accessible via file_get_contents"
            ];
        }

        $error = error_get_last();
        return [
            'success' => false,
            'method' => 'file_get_contents',
            'url' => $url,
            'error' => $error['message'] ?? 'Unknown error',
            'message' => "$name failed: " . ($error['message'] ?? 'Unknown error')
        ];
    }

    return [
        'success' => false,
        'method' => 'none',
        'message' => 'No download method available (cURL and allow_url_fopen both disabled)'
    ];
}

function checkExistingInstall() {
    $installer_exists = file_exists('installer.php');
    $install_dir_exists = is_dir(INSTALL_DIR);

    return [
        'exists' => $installer_exists || $install_dir_exists,
        'installer_file' => $installer_exists,
        'install_directory' => $install_dir_exists,
        'message' => $installer_exists ? 'Previous installation detected' : 'Ready for fresh installation'
    ];
}

function performInstallation() {
    // Download and extract logic here
    // This will be the existing download/extract code
    // For now, just redirect to show this is where it would happen
    header('Location: installer.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - Environment Check</title>
    <style>
        :root {
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
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
            background: #2563eb;
            color: white;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
        }

        .content {
            padding: 32px;
        }

        .status-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 32px;
        }

        .check-item {
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
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

        .check-content {
            flex: 1;
        }

        .check-content h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .check-content p {
            color: var(--text-light);
            font-size: 14px;
        }

        .controls {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 24px;
            padding: 20px;
            background: var(--bg);
            border-radius: 8px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--info);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #2563eb;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #059669;
        }

        .btn-outline {
            background: transparent;
            color: var(--info);
            border: 1px solid var(--info);
        }

        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            font-size: 14px;
            color: var(--text-light);
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid var(--border);
            border-top: 2px solid var(--info);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-summary {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-summary.ready {
            background: #f0fdf4;
            border: 1px solid var(--success);
            color: #065f46;
        }

        .status-summary.not-ready {
            background: #fef2f2;
            border: 1px solid var(--error);
            color: #991b1b;
        }

        .sub-checks {
            margin-left: 40px;
            margin-top: 12px;
        }

        .sub-check {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .loading {
            display: none;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
        }

        .loading.active {
            display: flex;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Environment Diagnostics & Preparation</p>
        </div>

        <div class="content">
            <div class="controls">
                <button class="btn btn-outline" id="check-again" onclick="runChecks()">
                    <span id="check-icon">🔄</span>
                    Check Again
                </button>

                <div class="loading" id="checking">
                    <div class="spinner"></div>
                    <span>Checking environment...</span>
                </div>

                <div class="auto-refresh">
                    <label>
                        <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()">
                        Auto-refresh every 5 seconds
                    </label>
                </div>
            </div>

            <div id="status-summary"></div>
            <div id="check-results" class="status-grid"></div>

            <form method="POST" id="install-form" style="display: none;">
                <input type="hidden" name="start_installation" value="1">
                <button type="submit" class="btn btn-success" style="width: 100%; padding: 16px; font-size: 16px;">
                    ✅ Start Installation - All Requirements Met
                </button>
            </form>

            <?php if (isset($error_message)): ?>
                <div class="status-summary not-ready">
                    <span>⚠️</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let autoRefreshInterval = null;
        let isChecking = false;

        // Run initial check on page load
        document.addEventListener('DOMContentLoaded', function() {
            runChecks();
        });

        function runChecks() {
            if (isChecking) return;

            isChecking = true;
            document.getElementById('checking').classList.add('active');
            document.getElementById('check-again').disabled = true;

            fetch('?ajax=check')
                .then(response => response.json())
                .then(data => {
                    displayResults(data);
                })
                .catch(error => {
                    console.error('Check failed:', error);
                    document.getElementById('check-results').innerHTML =
                        '<div class="check-item error"><div class="check-icon">❌</div><div class="check-content"><h3>Check Failed</h3><p>Unable to run environment checks</p></div></div>';
                })
                .finally(() => {
                    isChecking = false;
                    document.getElementById('checking').classList.remove('active');
                    document.getElementById('check-again').disabled = false;
                });
        }

        function displayResults(data) {
            const summaryEl = document.getElementById('status-summary');
            const resultsEl = document.getElementById('check-results');
            const formEl = document.getElementById('install-form');

            // Update auto-refresh availability based on PHP version
            updateAutoRefreshAvailability(data);

            // Status summary
            if (data.can_proceed) {
                summaryEl.innerHTML = '<span>✅</span><strong>Environment Ready!</strong> All requirements are met. You can proceed with installation.';
                summaryEl.className = 'status-summary ready';
                formEl.style.display = 'block';
            } else {
                summaryEl.innerHTML = '<span>⚠️</span><strong>Requirements Not Met</strong> Please resolve the issues below before proceeding.';
                summaryEl.className = 'status-summary not-ready';
                formEl.style.display = 'none';
            }

            // Detailed results
            let html = '';

            // PHP Version
            const php = data.php_version;
            html += `<div class="check-item ${php.status ? (php.level === 'excellent' ? 'success' : 'warning') : 'error'}">
                <div class="check-icon">${php.status ? '✅' : '❌'}</div>
                <div class="check-content">
                    <h3>PHP Version</h3>
                    <p>${php.message}</p>
                </div>
            </div>`;

            // Extensions
            const hasExtensionIssues = !data.extensions.critical_passed;
            html += `<div class="check-item ${hasExtensionIssues ? 'error' : 'success'}">
                <div class="check-icon">${hasExtensionIssues ? '❌' : '✅'}</div>
                <div class="check-content">
                    <h3>Required Extensions</h3>
                    <p>${hasExtensionIssues ? 'Some critical extensions are missing' : 'All required extensions available'}</p>
                    <div class="sub-checks">`;

            for (const [ext, info] of Object.entries(data.extensions)) {
                if (ext === 'critical_passed') continue;
                const icon = info.status ? '✅' : (info.critical ? '❌' : '⚠️');
                html += `<div class="sub-check">${icon} <strong>${ext}:</strong> ${info.message}</div>`;
            }

            html += `</div></div></div>`;

            // Permissions
            const perm = data.permissions;
            html += `<div class="check-item ${perm.status ? 'success' : 'error'}">
                <div class="check-icon">${perm.status ? '✅' : '❌'}</div>
                <div class="check-content">
                    <h3>Directory Permissions</h3>
                    <p>${perm.message}</p>
                </div>
            </div>`;

            // Connectivity
            const conn = data.connectivity;
            html += `<div class="check-item ${conn.status ? 'success' : 'error'}">
                <div class="check-icon">${conn.status ? '✅' : '❌'}</div>
                <div class="check-content">
                    <h3>Network Connectivity</h3>
                    <p>${conn.message}</p>`;

            // Add detailed connectivity results if available
            if (conn.details) {
                html += `<div class="sub-checks">`;
                for (const [name, detail] of Object.entries(conn.details)) {
                    const icon = detail.success ? '✅' : '❌';
                    const responseTime = detail.response_time ? ` (${Math.round(detail.response_time * 1000)}ms)` : '';
                    html += `<div class="sub-check">${icon} <strong>${name}:</strong> ${detail.message}${responseTime}</div>`;
                }
                html += `</div>`;
            }

            html += `</div>
            </div>`;

            // Existing Installation
            const existing = data.existing_install;
            html += `<div class="check-item ${existing.exists ? 'warning' : 'success'}">
                <div class="check-icon">${existing.exists ? '⚠️' : '✅'}</div>
                <div class="check-content">
                    <h3>Installation Status</h3>
                    <p>${existing.message}</p>
                </div>
            </div>`;

            resultsEl.innerHTML = html;
        }

        function toggleAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh');

            if (checkbox.checked) {
                autoRefreshInterval = setInterval(runChecks, 5000);
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
            }
        }

        function updateAutoRefreshAvailability(data) {
            const checkbox = document.getElementById('auto-refresh');
            const label = document.querySelector('.auto-refresh label');

            // Disable auto-refresh if PHP version fails (old PHP likely can't handle AJAX properly)
            if (!data.php_version.status) {
                checkbox.disabled = true;
                checkbox.checked = false;
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
                label.innerHTML = '<input type="checkbox" id="auto-refresh" disabled> Auto-refresh disabled (PHP version too old)';
                label.style.color = '#9ca3af';
            } else {
                checkbox.disabled = false;
                label.innerHTML = '<input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()"> Auto-refresh every 5 seconds';
                label.style.color = '';
            }
        }

        // Clean up interval on page unload
        window.addEventListener('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
</body>
</html>
