<?php
/**
 * Contact Form Installation Wizard
 * Professional one-click installer for the GlowHost Contact Form System
 *
 * Version: 1.0
 * Author: Contact Form Installation System
 */

session_start();

// Security: Prevent installation if already completed
if (file_exists('../config/installed.lock')) {
    header('Location: ../admin/login.php');
    exit('Installation already completed. Please delete config/installed.lock to reinstall.');
}

// Initialize session variables
if (!isset($_SESSION['install_step'])) {
    $_SESSION['install_step'] = 1;
    $_SESSION['install_data'] = [];
}

$current_step = $_SESSION['install_step'];
$install_data = $_SESSION['install_data'];

// Handle step navigation
if ($_POST['action'] ?? '' === 'next_step') {
    $current_step++;
    $_SESSION['install_step'] = $current_step;
} elseif ($_POST['action'] ?? '' === 'prev_step') {
    $current_step--;
    $_SESSION['install_step'] = $current_step;
}

// Step configuration
$steps = [
    1 => [
        'title' => 'Welcome',
        'subtitle' => 'GlowHost Contact Form System',
        'description' => 'Professional contact management with automatic field mapping',
        'file' => 'welcome.php'
    ],
    2 => [
        'title' => 'Database Connection',
        'subtitle' => 'Test your MySQL database settings',
        'description' => 'We\'ll verify your database credentials and connection',
        'file' => 'database-test.php'
    ],
    3 => [
        'title' => 'Database Setup',
        'subtitle' => 'Create required tables',
        'description' => 'Automatically create contact_submissions and admin tables',
        'file' => 'table-setup.php'
    ],
    4 => [
        'title' => 'Admin Account',
        'subtitle' => 'Create your admin login',
        'description' => 'Set up secure access to manage contact submissions',
        'file' => 'admin-setup.php'
    ],
    5 => [
        'title' => 'Configuration',
        'subtitle' => 'Generate system settings',
        'description' => 'Create configuration files and security settings',
        'file' => 'config-gen.php'
    ],
    6 => [
        'title' => 'Complete',
        'subtitle' => 'Installation finished',
        'description' => 'Your contact form system is ready to use',
        'file' => 'completion.php'
    ]
];

$total_steps = count($steps);
$progress_percent = (($current_step - 1) / ($total_steps - 1)) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Installation Wizard</title>
    <link rel="stylesheet" href="assets/installer.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="installer-container">
        <!-- Header -->
        <header class="installer-header">
            <div class="header-content">
                <div class="logo-section">
                    <img src="https://same-assets.com/logos/glowhost-logo-blue.png" alt="GlowHost" class="logo">
                    <div class="title-section">
                        <h1>Contact Form System</h1>
                        <p>Professional Installation Wizard</p>
                    </div>
                </div>
                <div class="step-indicator">
                    <span class="step-current">Step <?php echo $current_step; ?></span>
                    <span class="step-total">of <?php echo $total_steps; ?></span>
                </div>
            </div>
        </header>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
            </div>
            <div class="step-dots">
                <?php for ($i = 1; $i <= $total_steps; $i++): ?>
                    <div class="step-dot <?php echo $i <= $current_step ? 'active' : ''; ?> <?php echo $i < $current_step ? 'completed' : ''; ?>">
                        <?php if ($i < $current_step): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <?php echo $i; ?>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Main Content -->
        <main class="installer-main">
            <div class="step-content">
                <!-- Step Header -->
                <div class="step-header">
                    <h2><?php echo $steps[$current_step]['title']; ?></h2>
                    <h3><?php echo $steps[$current_step]['subtitle']; ?></h3>
                    <p><?php echo $steps[$current_step]['description']; ?></p>
                </div>

                <!-- Step Content -->
                <div class="step-body">
                    <?php
                    $step_file = __DIR__ . '/steps/' . $steps[$current_step]['file'];
                    if (file_exists($step_file)) {
                        include $step_file;
                    } else {
                        echo '<div class="error-message">Step file not found: ' . htmlspecialchars($steps[$current_step]['file']) . '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>

        <!-- Footer Navigation -->
        <footer class="installer-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <?php if ($current_step > 1): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="prev_step">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="footer-center">
                    <span class="version-info">v1.0 | Field Mapping System</span>
                </div>

                <div class="footer-right">
                    <!-- Next button will be rendered by individual step files -->
                    <div id="next-button-container"></div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="assets/installer.js"></script>
    <script>
        // Global installer object
        window.Installer = {
            currentStep: <?php echo $current_step; ?>,
            totalSteps: <?php echo $total_steps; ?>,
            installData: <?php echo json_encode($install_data); ?>,

            // Navigate to next step
            nextStep: function() {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = '<input type="hidden" name="action" value="next_step">';
                document.body.appendChild(form);
                form.submit();
            },

            // Show loading state
            showLoading: function(message = 'Processing...') {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay';
                overlay.innerHTML = `
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <p>${message}</p>
                    </div>
                `;
                document.body.appendChild(overlay);
            },

            // Hide loading state
            hideLoading: function() {
                const overlay = document.querySelector('.loading-overlay');
                if (overlay) {
                    overlay.remove();
                }
            },

            // Show error message
            showError: function(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-error';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>${message}</span>
                `;

                const container = document.querySelector('.step-body');
                container.insertBefore(errorDiv, container.firstChild);

                // Auto-remove after 5 seconds
                setTimeout(() => errorDiv.remove(), 5000);
            },

            // Show success message
            showSuccess: function(message) {
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <span>${message}</span>
                `;

                const container = document.querySelector('.step-body');
                container.insertBefore(successDiv, container.firstChild);

                // Auto-remove after 3 seconds
                setTimeout(() => successDiv.remove(), 3000);
            }
        };

        // Auto-advance progress bar animation
        document.addEventListener('DOMContentLoaded', function() {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                setTimeout(() => {
                    progressFill.style.transition = 'width 0.8s ease-in-out';
                }, 100);
            }
        });
    </script>
</body>
</html>
