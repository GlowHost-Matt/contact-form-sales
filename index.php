<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowHost Contact Form - PHP Version Check</title>
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
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .php-requirement {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            padding: 20px;
            margin: 24px 32px;
            border-radius: 8px;
            text-align: center;
        }
        .php-requirement h2 {
            margin: 0 0 12px 0;
            font-size: 20px;
            color: #92400e;
        }
        .php-requirement .version {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            background: white;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            margin: 8px 0;
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
            padding: 16px 32px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        .btn:hover {
            background: #059669;
        }
        .actions {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h4 {
            margin-top: 0;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Contact Form System</h1>
            <p>Pre-Installation PHP Version Check</p>
        </div>

        <div class="php-requirement">
            <h2>⚠️ PHP Version Requirement</h2>
            <div class="version">PHP 7.4.0 or higher (8.1+ recommended)</div>
            <p>Please verify your server meets this requirement before proceeding</p>
        </div>

        <div class="content">
            <div class="step">
                <h3>Step 1: Check Your PHP Version</h3>
                <p>Use one of these methods to verify your current PHP version:</p>

                <p><strong>Option A: Command Line</strong></p>
                <div class="code-block">php -v</div>

                <p><strong>Option B: Create a temporary PHP file</strong></p>
                <div class="code-block">&lt;?php echo 'PHP Version: ' . PHP_VERSION; ?&gt;</div>

                <p><strong>Option C: Check your hosting control panel</strong></p>
                <p>Look for "PHP Version" or "PHP Settings" in your hosting control panel (cPanel, Plesk, etc.)</p>
            </div>

            <div class="info-box">
                <h4>💡 What Happens Next?</h4>
                <p>After confirming your PHP version, the automated installer will:</p>
                <ul style="margin: 10px 0; text-align: left;">
                    <li>✅ Test your server environment automatically</li>
                    <li>✅ Check for required PHP extensions</li>
                    <li>✅ Verify database connectivity</li>
                    <li>✅ Set up the contact form system</li>
                </ul>
                <p><strong>No manual verification needed for these items!</strong></p>
            </div>

            <div class="actions">
                <p style="margin-bottom: 16px;"><strong>Ready to proceed?</strong></p>
                <button class="btn" onclick="proceedToInstaller()">
                    Continue to Automated Installer
                </button>
            </div>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
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
                    <h4>❓ Not Sure About Your PHP Version?</h4>
                    <ul>
                        <li><strong>Contact Support:</strong> Your hosting provider can tell you the current PHP version</li>
                        <li><strong>File Manager:</strong> Create a file called <code>phpinfo.php</code> with content <code>&lt;?php phpinfo(); ?&gt;</code> and visit it in your browser</li>
                        <li><strong>SSH Access:</strong> Run <code>php -v</code> in your terminal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function proceedToInstaller() {
            // Direct redirect to working installer
            window.location.href = 'installer.php';
        }
    </script>
</body>
</html>
