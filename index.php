<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - Pre-Installation Check</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f9fafb;
            color: #1f2937;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
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
            margin: 0 0 8px 0;
            font-size: 24px;
        }
        .content {
            padding: 32px;
        }
        .step {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .step h3 {
            margin-top: 0;
            color: #1f2937;
        }
        .code-block {
            background: #1f2937;
            color: #f9fafb;
            padding: 12px 16px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #059669;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .checklist {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .checklist h4 {
            margin-top: 0;
            color: #1e40af;
        }
        .checklist ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .checklist li {
            margin: 8px 0;
        }
        .actions {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        /* Large Interactive Checkboxes */
        .confirmation-checkboxes {
            margin: 20px 0;
        }

        .checkbox-large {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 16px 0;
            padding: 16px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .checkbox-large:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .checkbox-large input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 24px;
            height: 24px;
            border: 2px solid #94a3b8;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .checkbox-large input[type="checkbox"]:checked + .checkmark {
            background: #10b981;
            border-color: #10b981;
        }

        .checkbox-large input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .checkbox-text {
            font-size: 16px;
            font-weight: 500;
            color: #374151;
            flex: 1;
        }

        .checkbox-large input[type="checkbox"]:checked ~ .checkbox-text {
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Pre-Installation Compatibility Check</p>
        </div>

        <div class="content">
            <div class="warning">
                <strong>⚠️ Important:</strong> Before proceeding with the automated installer, please manually verify your PHP version to prevent installation issues.
            </div>

            <div class="step">
                <h3>Step 1: Check Your PHP Version</h3>
                <p>Run this command in your web hosting control panel, terminal, or create a temporary PHP file:</p>

                <p><strong>Option A: Command Line</strong></p>
                <div class="code-block">php -v</div>

                <p><strong>Option B: Create a temporary PHP file</strong></p>
                <div class="code-block">&lt;?php echo 'PHP Version: ' . PHP_VERSION; ?&gt;</div>

                <p><strong>Option C: In hosting control panel</strong></p>
                <p>Look for "PHP Version" or "PHP Settings" in your hosting control panel (cPanel, Plesk, etc.)</p>
            </div>

            <div class="checklist">
                <h4>📋 System Requirements Checklist</h4>
                <ul>
                    <li><strong>PHP Version:</strong> 7.4.0 or higher (8.1+ recommended)</li>
                    <li><strong>Required Extensions:</strong> ZipArchive, cURL or allow_url_fopen, PDO, MySQLi</li>
                    <li><strong>Database:</strong> MySQL 5.7+ or MariaDB 10.2+</li>
                    <li><strong>Permissions:</strong> Web directory must be writable</li>
                    <li><strong>Network:</strong> Outbound HTTPS connections allowed</li>
                </ul>
            </div>

            <div class="step">
                <h3>Step 2: Confirm Your Readiness</h3>
                <p><strong>⚠️ REQUIRED:</strong> You must check all 3 boxes below to proceed with installation.</p>

                <div class="confirmation-checkboxes">
                    <label class="checkbox-large">
                        <input type="checkbox" id="php-version">
                        <span class="checkmark"></span>
                        <span class="checkbox-text">I have confirmed my PHP version is 7.4.0 or higher</span>
                    </label>

                    <label class="checkbox-large">
                        <input type="checkbox" id="hosting-access">
                        <span class="checkmark"></span>
                        <span class="checkbox-text">I have web hosting with MySQL database access</span>
                    </label>

                    <label class="checkbox-large">
                        <input type="checkbox" id="admin-access">
                        <span class="checkmark"></span>
                        <span class="checkbox-text">I have administrative access to this web directory</span>
                    </label>
                </div>

                <p style="margin-top: 15px; font-style: italic; color: #6b7280;">
                    💡 <strong>Note:</strong> Click each checkbox above to confirm you meet the requirements.
                </p>
            </div>

            <div class="actions">
                <p><strong>Ready to proceed with automated installation?</strong></p>

                <button id="continue-btn" class="btn" disabled style="background: #9ca3af; cursor: not-allowed;">
                    Continue to Automated Installer
                </button>
            </div>

            <div id="troubleshooting" style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <h3>📚 Troubleshooting Guide</h3>

                <div class="step">
                    <h4>🔧 PHP Version Too Old?</h4>
                    <ul>
                        <li><strong>Shared Hosting:</strong> Contact your hosting provider to upgrade PHP or check if multiple PHP versions are available in your control panel</li>
                        <li><strong>VPS/Dedicated:</strong> Update PHP using your package manager (yum, apt, etc.)</li>
                        <li><strong>cPanel:</strong> Look for "PHP Version" or "MultiPHP Manager" in your control panel</li>
                    </ul>
                </div>

                <div class="step">
                    <h4>🗄️ Database Access Issues?</h4>
                    <ul>
                        <li><strong>Create Database:</strong> Use your hosting control panel to create a new MySQL database</li>
                        <li><strong>Database User:</strong> Create a user with full privileges to the database</li>
                        <li><strong>Connection Info:</strong> Note your database hostname, username, password, and database name</li>
                    </ul>
                </div>

                <div class="step">
                    <h4>🔒 Permission Issues?</h4>
                    <ul>
                        <li><strong>File Permissions:</strong> Set directory permissions to 755 or 775</li>
                        <li><strong>Web Server User:</strong> Ensure the web server can write to this directory</li>
                        <li><strong>Contact Support:</strong> If using shared hosting, contact your provider for assistance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to properly manage continue button state
        function updateContinueButton() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const continueBtn = document.getElementById('continue-btn');

            let allChecked = true;
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });

            continueBtn.disabled = !allChecked;
            if (allChecked) {
                continueBtn.style.background = '#10b981';
                continueBtn.style.cursor = 'pointer';
            } else {
                continueBtn.style.background = '#9ca3af';
                continueBtn.style.cursor = 'not-allowed';
            }
        }

        function proceedToInstaller() {
            // Only proceed if button is enabled
            const continueBtn = document.getElementById('continue-btn');
            if (!continueBtn.disabled) {
                window.location.href = 'installer.php';
            }
        }

        // Initialize page functionality
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const continueBtn = document.getElementById('continue-btn');

            // Add event listeners to checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateContinueButton);
            });

            // Add click event to continue button
            continueBtn.addEventListener('click', proceedToInstaller);

            // Set initial disabled state
            updateContinueButton();
        });
    </script>
</body>
</html>
