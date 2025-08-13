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
            color: #059669;
        }

        .status-summary.issues {
            background: #fef2f2;
            border: 1px solid var(--error);
            color: #dc2626;
        }

        .status-summary.checking {
            background: #eff6ff;
            border: 1px solid var(--info);
            color: #2563eb;
        }

        .summary-icon {
            font-size: 20px;
        }

        .diagnostic-section {
            margin-bottom: 32px;
        }

        .diagnostic-section h3 {
            margin-bottom: 16px;
            color: var(--text);
        }

        .tips {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 24px;
        }

        .tips h4 {
            color: #0c4a6e;
            margin-bottom: 12px;
        }

        .tips ul {
            color: #0369a1;
            margin-left: 20px;
        }

        .tips li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 GlowHost Contact Form</h1>
            <p>Interactive Environment Diagnostic Tool v3.3</p>
        </div>

        <div class="content">
            <!-- Status Summary -->
            <div id="status-summary" class="status-summary checking">
                <div class="summary-icon">⚡</div>
                <div>
                    <strong>Initializing Environment Check...</strong>
                    <div style="font-size: 14px; margin-top: 4px;">Please wait while we analyze your server environment</div>
                </div>
            </div>

            <!-- Controls -->
            <div class="controls">
                <button id="refresh-btn" class="btn btn-primary" onclick="runChecks()">
                    <span>🔄</span> Refresh Checks
                </button>
                
                <button id="install-btn" class="btn btn-success" onclick="startInstallation()" disabled>
                    <span>🚀</span> Start Installation
                </button>

                <div class="auto-refresh">
                    <input type="checkbox" id="auto-refresh" checked>
                    <label for="auto-refresh">Auto-refresh (30s)</label>
                </div>
            </div>

            <!-- Diagnostic Results -->
            <div class="diagnostic-section">
                <h3>📊 Environment Analysis</h3>
                <div id="diagnostic-results" class="status-grid">
                    <!-- Results will be populated here -->
                </div>
            </div>

            <!-- Installation Tips -->
            <div class="tips">
                <h4>💡 Installation Tips</h4>
                <ul>
                    <li><strong>First time?</strong> This tool checks if your server meets all requirements</li>
                    <li><strong>Issues found?</strong> Contact your hosting provider or follow the specific instructions shown</li>
                    <li><strong>Auto-refresh:</strong> The page updates every 30 seconds to track configuration changes</li>
                    <li><strong>Need help?</strong> Each check provides detailed instructions for resolution</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            runChecks();
            setupAutoRefresh();
        });

        async function runChecks() {
            const refreshBtn = document.getElementById('refresh-btn');
            const installBtn = document.getElementById('install-btn');
            const summary = document.getElementById('status-summary');
            
            // Update UI to show checking
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<div class="spinner"></div> Checking...';
            installBtn.disabled = true;
            
            summary.className = 'status-summary checking';
            summary.innerHTML = `
                <div class="summary-icon">⚡</div>
                <div>
                    <strong>Running Environment Checks...</strong>
                    <div style="font-size: 14px; margin-top: 4px;">Analyzing server configuration</div>
                </div>
            `;

            try {
                const response = await fetch('?ajax=check');
                const results = await response.json();
                
                displayResults(results);
                updateSummary(results);
                
                // Enable install button if all checks pass
                installBtn.disabled = !results.can_proceed;
                
            } catch (error) {
                console.error('Check failed:', error);
                
                summary.className = 'status-summary issues';
                summary.innerHTML = `
                    <div class="summary-icon">❌</div>
                    <div>
                        <strong>Connection Error</strong>
                        <div style="font-size: 14px; margin-top: 4px;">Could not perform environment checks</div>
                    </div>
                `;
            } finally {
                // Reset refresh button
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<span>🔄</span> Refresh Checks';
            }
        }

        function displayResults(results) {
            const container = document.getElementById('diagnostic-results');
            let html = '';

            // PHP Version Check
            const php = results.php_version;
            html += createCheckItem(
                php.status ? (php.level === 'excellent' ? '✅' : '⚠️') : '❌',
                'PHP Version',
                php.message,
                php.status ? (php.level === 'excellent' ? 'success' : 'warning') : 'error'
            );

            // Extensions Check
            const ext = results.extensions;
            html += createCheckItem(
                ext.critical_passed ? '✅' : '❌',
                'Required Extensions',
                ext.critical_passed ? 'All critical extensions available' : 'Missing critical extensions',
                ext.critical_passed ? 'success' : 'error'
            );

            // Permissions Check
            const perm = results.permissions;
            html += createCheckItem(
                perm.status ? '✅' : '❌',
                'Directory Permissions',
                perm.message,
                perm.status ? 'success' : 'error'
            );

            // Connectivity Check
            const conn = results.connectivity;
            html += createCheckItem(
                conn.status ? '✅' : '❌',
                'Network Connectivity',
                conn.message,
                conn.status ? 'success' : 'error'
            );

            // Existing Installation Check
            const existing = results.existing_install;
            html += createCheckItem(
                !existing.exists ? '✅' : '⚠️',
                'Installation Status',
                existing.message,
                !existing.exists ? 'success' : 'warning'
            );

            container.innerHTML = html;
        }

        function createCheckItem(icon, title, message, status) {
            return `
                <div class="check-item ${status}">
                    <div class="check-icon">${icon}</div>
                    <div class="check-content">
                        <h3>${title}</h3>
                        <p>${message}</p>
                    </div>
                </div>
            `;
        }

        function updateSummary(results) {
            const summary = document.getElementById('status-summary');
            
            if (results.can_proceed) {
                summary.className = 'status-summary ready';
                summary.innerHTML = `
                    <div class="summary-icon">🎉</div>
                    <div>
                        <strong>Environment Ready!</strong>
                        <div style="font-size: 14px; margin-top: 4px;">All requirements met - ready to install</div>
                    </div>
                `;
            } else {
                const issueCount = Object.values(results).filter(check => 
                    check && typeof check === 'object' && check.status === false
                ).length;
                
                summary.className = 'status-summary issues';
                summary.innerHTML = `
                    <div class="summary-icon">⚠️</div>
                    <div>
                        <strong>${issueCount} Issue${issueCount > 1 ? 's' : ''} Found</strong>
                        <div style="font-size: 14px; margin-top: 4px;">Please resolve the issues above before installation</div>
                    </div>
                `;
            }
        }

        function setupAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh');
            
            function updateAutoRefresh() {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
                
                if (checkbox.checked) {
                    autoRefreshInterval = setInterval(runChecks, 30000); // 30 seconds
                }
            }
            
            checkbox.addEventListener('change', updateAutoRefresh);
            updateAutoRefresh(); // Initialize
        }

        async function startInstallation() {
            const installBtn = document.getElementById('install-btn');
            
            if (confirm('Start the installation process? This will download and set up the contact form system.')) {
                installBtn.disabled = true;
                installBtn.innerHTML = '<div class="spinner"></div> Starting...';
                
                try {
                    const formData = new FormData();
                    formData.append('start_installation', '1');
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        throw new Error('Installation start failed');
                    }
                } catch (error) {
                    alert('Failed to start installation. Please try again.');
                    installBtn.disabled = false;
                    installBtn.innerHTML = '<span>🚀</span> Start Installation';
                }
            }
        }
    </script>
</body>
</html>