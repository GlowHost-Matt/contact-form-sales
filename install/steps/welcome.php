<?php
/**
 * Installation Step 1: Welcome
 * Introduction and system requirements check
 */

// Check PHP version and extensions
$php_version = PHP_VERSION;
$php_version_ok = version_compare($php_version, '7.4.0', '>=');

$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

$requirements_met = $php_version_ok && empty($missing_extensions);
?>

<div class="welcome-content">
    <!-- Welcome Message -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Welcome to the Contact Form System</h3>
            <p class="card-subtitle">Professional contact management with automatic field mapping</p>
        </div>

        <div class="feature-list">
            <li>
                <div class="icon"><i class="fas fa-magic"></i></div>
                <div>
                    <strong>Automatic Field Mapping</strong>
                    <p>Converts "Full Name" to separate "First Name" + "Last Name" database fields</p>
                </div>
            </li>
            <li>
                <div class="icon"><i class="fas fa-database"></i></div>
                <div>
                    <strong>MySQL Integration</strong>
                    <p>Seamless database storage with error handling and fallback logging</p>
                </div>
            </li>
            <li>
                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                <div>
                    <strong>Admin Dashboard</strong>
                    <p>Secure interface to manage submissions, users, and system settings</p>
                </div>
            </li>
            <li>
                <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                <div>
                    <strong>Responsive Design</strong>
                    <p>Professional contact form with auto-save and file upload support</p>
                </div>
            </li>
            <li>
                <div class="icon"><i class="fas fa-cog"></i></div>
                <div>
                    <strong>Easy Configuration</strong>
                    <p>One-click installation with guided setup and automatic cleanup</p>
                </div>
            </li>
        </div>
    </div>

    <!-- System Requirements -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">System Requirements</h3>
            <p class="card-subtitle">Checking your server compatibility</p>
        </div>

        <div class="requirements-check">
            <!-- PHP Version -->
            <div class="test-result <?php echo $php_version_ok ? 'success' : 'error'; ?>">
                <i class="fas <?php echo $php_version_ok ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <div>
                    <strong>PHP Version</strong>
                    <p>Current: <?php echo $php_version; ?> | Required: 7.4.0+</p>
                </div>
            </div>

            <!-- PHP Extensions -->
            <?php foreach ($required_extensions as $ext): ?>
                <?php $loaded = extension_loaded($ext); ?>
                <div class="test-result <?php echo $loaded ? 'success' : 'error'; ?>">
                    <i class="fas <?php echo $loaded ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                    <div>
                        <strong>PHP Extension: <?php echo $ext; ?></strong>
                        <p><?php echo $loaded ? 'Available' : 'Missing - Please install this extension'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Directory Permissions -->
            <?php
            $writable_dirs = ['../config', '../logs', '../uploads'];
            $permission_issues = [];

            foreach ($writable_dirs as $dir) {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }

                if (!is_writable($dir)) {
                    $permission_issues[] = $dir;
                }
            }
            ?>

            <div class="test-result <?php echo empty($permission_issues) ? 'success' : 'error'; ?>">
                <i class="fas <?php echo empty($permission_issues) ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <div>
                    <strong>Directory Permissions</strong>
                    <?php if (empty($permission_issues)): ?>
                        <p>All directories are writable</p>
                    <?php else: ?>
                        <p>Please make these directories writable: <?php echo implode(', ', $permission_issues); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Overall Status -->
            <?php if ($requirements_met && empty($permission_issues)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><strong>All requirements met!</strong> Your server is ready for installation.</span>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><strong>Requirements not met.</strong> Please resolve the issues above before proceeding.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Installation Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">What This Installer Will Do</h3>
        </div>

        <div class="installation-steps">
            <div class="step-info">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Test Database Connection</h4>
                    <p>Verify your MySQL credentials and ensure proper connectivity</p>
                </div>
            </div>

            <div class="step-info">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Create Database Tables</h4>
                    <p>Set up contact_submissions and admin tables with proper indexes</p>
                </div>
            </div>

            <div class="step-info">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Configure Admin Account</h4>
                    <p>Create your secure admin login for managing submissions</p>
                </div>
            </div>

            <div class="step-info">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Generate Configuration</h4>
                    <p>Create config files and set up security settings</p>
                </div>
            </div>

            <div class="step-info">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Cleanup & Secure</h4>
                    <p>Remove installation files and finalize your setup</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.requirements-check .test-result {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 8px;
}

.requirements-check .test-result div {
    flex: 1;
}

.requirements-check .test-result p {
    margin: 4px 0 0 0;
    font-size: 14px;
    opacity: 0.8;
}

.installation-steps {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.step-info {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.step-number {
    width: 32px;
    height: 32px;
    background: #4299e1;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.step-content p {
    margin: 0;
    font-size: 14px;
    color: #718096;
    line-height: 1.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create next button
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        const requirementsMet = <?php echo json_encode($requirements_met && empty($permission_issues)); ?>;

        nextButtonContainer.innerHTML = `
            <button type="button" class="btn btn-primary btn-block"
                    ${requirementsMet ? '' : 'disabled'}
                    onclick="window.Installer.nextStep()">
                <i class="fas fa-arrow-right"></i>
                ${requirementsMet ? 'Begin Installation' : 'Requirements Not Met'}
            </button>
        `;
    }
});
</script>
