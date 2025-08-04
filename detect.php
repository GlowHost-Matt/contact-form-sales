<?php
/**
 * GlowHost Contact Form - Minimal PHP Version Detector
 * Version: 5.2 Compatible - No modern syntax
 */

// Get current directory info (PHP 5.2 compatible)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$base_url = rtrim($protocol . $host . $path, '/');

// Step parameter (PHP 5.2 compatible)
$step = isset($_GET['step']) ? $_GET['step'] : 'deploy';

/**
 * Deploy phpinfo.php if needed
 */
function deployPhpInfo() {
    if (!file_exists('phpinfo.php')) {
        $content = '<?php phpinfo(); ?>';
        return file_put_contents('phpinfo.php', $content) !== false;
    }
    return true;
}

/**
 * Get PHP version from phpinfo
 */
function getPhpVersion($base_url) {
    $phpinfo_url = $base_url . '/phpinfo.php';

    // Try file_get_contents first
    $content = @file_get_contents($phpinfo_url);

    if ($content === false) {
        return false;
    }

    // Look for PHP Version in the output
    if (preg_match('/PHP Version ([0-9]+\.[0-9]+\.[0-9]+[^\s<]*)/i', $content, $matches)) {
        return $matches[1];
    }

    return false;
}

// Deploy phpinfo.php first
deployPhpInfo();

?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Version Check - GlowHost Contact Form</title>
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
            --red-100: #fee2e2;
            --red-600: #dc2626;
            --green-50: #f0fdf4;
            --green-600: #16a34a;
            --green-700: #15803d;
            --yellow-50: #fffbeb;
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
            padding: 1.25rem 0;
            position: relative;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            height: 36px;
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

        .simple-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Modern Main Container */
        .main-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            color: var(--glowhost-navy);
            font-size: 2.5rem;
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
            font-size: 1.125rem;
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
        }

        .detection-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow:
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .detection-card:hover {
            transform: translateY(-2px);
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.3);
        }



        .card-content {
            padding: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: -0.025em;
        }

        /* Modern Status Boxes */
        .status-box {
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .status-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
        }

        .version-box {
            background: var(--glowhost-bg-light);
            border-color: var(--glowhost-light);
            color: var(--gray-800);
        }

        .success-box {
            background: var(--green-50);
            border-color: var(--green-600);
            color: var(--gray-800);
        }

        .warning-box {
            background: var(--yellow-50);
            border-color: var(--yellow-600);
            color: var(--gray-800);
        }

        .error-box {
            background: #fee2e2;
            border: 3px solid #dc2626;
            color: #7f1d1d;
        }

        .error-box h3 {
            color: #dc2626;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .status-box h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .error-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #dc2626;
        }

        .status-box h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            margin-top: 1rem;
        }

        .status-box p {
            margin-bottom: 0.5rem;
        }

        .status-box strong {
            font-weight: 600;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0.5rem 0.25rem;
        }

        .btn-primary {
            background: var(--glowhost-bright);
            color: white;
        }

        .btn-primary:hover {
            background: var(--glowhost-blue);
        }

        .btn-success {
            background: var(--green-600);
            color: white;
        }

        .btn-success:hover {
            background: var(--green-700);
        }

        .btn-warning {
            background: var(--yellow-600);
            color: white;
        }

        .btn-warning:hover {
            background: var(--yellow-700);
        }

        .btn-actions {
            text-align: center;
            margin-top: 2rem;
        }

        /* Back Button */
        .back-btn {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }



        /* Lists */
        ol {
            margin: 0.75rem 0 0 1.25rem;
            color: var(--gray-700);
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            border-top-color: var(--glowhost-bright);
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
                padding: 0 0.5rem;
            }

            .card-content {
                padding: 1.5rem;
            }

            .back-btn {
                position: static;
                display: block;
                margin-bottom: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Official GlowHost Header -->
    <header class="glowhost-header">
        <?php if ($step === 'check'): ?>
            <a href="?step=deploy" class="back-btn">← Back</a>
        <?php elseif ($step === 'install'): ?>
            <a href="?step=check" class="back-btn">← Back</a>
        <?php endif; ?>
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
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: var(--glowhost-navy); font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">
                Contact Form System - Environment Check
            </h1>
            <p style="color: var(--gray-600);">
                Verifying your server compatibility for the GlowHost Contact Form installation
            </p>
        </div>

        <div class="detection-card">
            <div class="card-content">

                <?php if ($step === 'deploy'): ?>
                    <h2 class="section-title">🔍 Analyzing Your PHP Environment</h2>
                    <div class="version-box">
                        <div style="display: flex; align-items: center; justify-content: center;">
                            <div class="loading-spinner"></div>
                            <span>Deploying diagnostic tools and analyzing server compatibility...</span>
                        </div>
                    </div>
                    <script>
                    setTimeout(function() {
                        window.location.href = '?step=check';
                    }, 2000);
                    </script>

        <?php elseif ($step === 'check'): ?>
            <?php
            $detected_version = getPhpVersion($base_url);

            if ($detected_version === false):
            ?>
                <h2 class="section-title">❌ Could Not Detect PHP Version</h2>
                <div class="error-box">
                    <p><strong>Automatic detection failed.</strong></p>
                    <p>This could mean your server has restrictions on accessing phpinfo.</p>
                    <p>Please check your hosting control panel for PHP version information.</p>
                </div>

                <div class="warning-box">
                    <h3>Manual Steps:</h3>
                    <ol>
                        <li>Log into your hosting control panel (cPanel)</li>
                        <li>Look for "PHP Version" or "MultiPHP Manager"</li>
                        <li>Ensure PHP is set to 8.1 or higher</li>
                        <li>Contact support if you need help upgrading</li>
                    </ol>
                </div>

            <?php else: ?>
                <h2 class="section-title">📊 PHP Version Analysis</h2>

                <div class="version-box">
                    <h3>Detected: PHP <?php echo htmlspecialchars($detected_version); ?></h3>
                </div>

                <?php if (version_compare($detected_version, '8.1.0', '>=')):  ?>
                    <!-- PHP 8.1+ Perfect -->
                    <div class="success-box">
                        <h3>✅ Excellent PHP Version!</h3>
                        <p><strong>Status:</strong> Perfect for modern web applications</p>
                        <p><strong>Industry Context:</strong> PHP 8.1+ is the current standard for modern development with active security support and performance improvements.</p>
                        <p><strong>Compatibility:</strong> Full installer support with optimal performance</p>
                    </div>

                    <div class="btn-actions">
                        <a href="?step=install" class="btn btn-success">🚀 Download & Run Installer</a>
                    </div>

                <?php elseif (version_compare($detected_version, '7.4.0', '>=')):  ?>
                    <!-- PHP 7.4-8.0 Compatible but recommend upgrade -->
                    <div class="warning-box">
                        <h3>⚠️ PHP Version Compatible</h3>
                        <p><strong>Status:</strong> Will work</p>
                        <p><strong>Recommendation:</strong> Upgrade to PHP 8.4 strongly recommended</p>
                        <p><strong>Industry Context:</strong> PHP 7.4 reached end-of-life in November 2022 and no longer receives security updates. Modern development standards require PHP 8.1+ for new projects.</p>
                        <p>While functional, this version poses security risks in production environments.</p>
                    </div>

                    <div class="version-box">
                        <h4>Upgrade Instructions (Recommended):</h4>
                        <ol>
                            <li>Access your hosting control panel (cPanel)</li>
                            <li>Find "MultiPHP Manager" or "PHP Version"</li>
                            <li>Change PHP version to <strong>8.4</strong></li>
                            <li>Apply changes and wait a few minutes</li>
                        </ol>
                    </div>

                    <div class="btn-actions">
                        <a href="?step=check" class="btn btn-success">🔄 Check Again After Upgrade</a>
                        <a href="?step=install" class="btn btn-warning">⚠️ Proceed at Own Risk</a>
                    </div>

                <?php else: ?>
                    <!-- PHP < 7.4 Too old -->
                    <div class="error-box">
                        <h3>🚨 PHP Version Incompatible - Installation Blocked</h3>
                        <p><strong>Current:</strong> <?php echo htmlspecialchars($detected_version); ?></p>
                        <p><strong>Required:</strong> 7.4+ (8.4 recommended)</p>
                        <p><strong>Industry Context:</strong> PHP 5.x reached end-of-life in 2018 and contains critical security vulnerabilities. No modern web applications support this version.</p>
                        <p>Installation is blocked to protect your server security.</p>
                    </div>

                    <div class="warning-box">
                        <h4>🛠️ Required: Upgrade Your PHP Version:</h4>
                        <p>Use PHP Selector to change your PHP version.</p>
                        <p>We can adopt modules, extensions or whatever the new "BS Bingo" is called later, but you need a modern PHP working.</p>
                    </div>

                    <div class="btn-actions">
                        <a href="?step=check" class="btn btn-primary">🔄 Check Again After Upgrade</a>
                        <a href="phpinfo.php" class="btn btn-primary" target="_blank">📋 View Full PHP Info</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif ($step === 'install'): ?>
                <h2 class="section-title">📥 Installer Download</h2>

            <div class="success-box">
                <h3>✅ PHP Version Compatible!</h3>
                <p>Downloading the full installation wizard...</p>
            </div>

            <div class="version-box">
                <h4>Manual Download Command:</h4>
                <pre style="background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto;">wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php -O install.php</pre>

                <p><strong>Then visit:</strong> <code>install.php</code> in your browser</p>
            </div>

            <div class="warning-box">
                <h4>🧹 Cleanup (After Successful Installation):</h4>
                <p>You can safely delete these diagnostic files:</p>
                <ul>
                    <li><code>detect.php</code> (this file)</li>
                    <li><code>phpinfo.php</code> (diagnostic file)</li>
                </ul>
            </div>

                <div class="btn-actions">
                    <a href="install.php" class="btn btn-success">🏁 Start Installation</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


</body>
</html>