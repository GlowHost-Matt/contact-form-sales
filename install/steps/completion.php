<?php
/**
 * Installation Step 6: Installation Complete
 * Finalize installation and provide access information
 */

// Handle installation completion
if ($_POST['action'] ?? '' === 'complete_installation') {
    header('Content-Type: application/json');

    try {
        // Create installation lock file
        $lock_content = "<?php\n// Installation completed on " . date('Y-m-d H:i:s') . "\n// Do not delete this file\ndefine('CF_INSTALLED', true);\n";
        $lock_created = file_put_contents('../config/installed.lock', $lock_content);

        if ($lock_created === false) {
            throw new Exception('Failed to create installation lock file');
        }

        // Set lock file permissions
        chmod('../config/installed.lock', 0644);

        // Create admin dashboard index file if it doesn't exist
        if (!file_exists('../admin/index.php')) {
            $admin_index_content = generateAdminIndexFile();
            if (!is_dir('../admin')) {
                mkdir('../admin', 0755, true);
            }
            file_put_contents('../admin/index.php', $admin_index_content);
            chmod('../admin/index.php', 0644);
        }

        // Clear installation session data
        unset($_SESSION['install_step']);
        unset($_SESSION['install_data']);

        // Store completion info
        $_SESSION['installation_completed'] = true;
        $_SESSION['completion_time'] = time();

        echo json_encode([
            'success' => true,
            'message' => 'Installation completed successfully',
            'admin_url' => '../admin/',
            'contact_form_url' => '../',
            'completion_time' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

function generateAdminIndexFile() {
    return "<?php
/**
 * Admin Dashboard Index
 * Redirects to login if not authenticated
 */

// Check if system is installed
if (!file_exists('../config/installed.lock')) {
    header('Location: ../install/');
    exit('Installation not completed');
}

// Start session
session_start();

// Check if user is logged in
if (!isset(\$_SESSION['admin_logged_in']) || \$_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Redirect to dashboard
header('Location: dashboard.php');
exit;
";
}

// Get installation data
$config_generated = $_SESSION['install_data']['config_generated'] ?? false;
$admin_info = $_SESSION['install_data']['admin_info'] ?? [];
$db_config = $_SESSION['install_data']['database'] ?? [];
$installation_completed = $_SESSION['installation_completed'] ?? false;
?>

<div class="completion-content">
    <?php if (!$config_generated): ?>
        <!-- Configuration Not Generated -->
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Configuration not found. Please go back and generate the configuration files first.</span>
        </div>
    <?php else: ?>
        <?php if (!$installation_completed): ?>
            <!-- Ready to Complete -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ready to Complete Installation</h3>
                    <p class="card-subtitle">Finalize your contact form system setup</p>
                </div>

                <div class="completion-summary">
                    <div class="summary-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div class="summary-content">
                        <h4>Installation Summary</h4>
                        <p>Your contact form system is ready to go live. Click "Complete Installation" to finalize the setup and secure your system.</p>
                    </div>
                </div>

                <div class="installation-review">
                    <div class="review-section">
                        <h4><i class="fas fa-database"></i> Database Configuration</h4>
                        <div class="review-details">
                            <div class="detail">
                                <span class="label">Host:</span>
                                <span class="value"><?php echo htmlspecialchars($db_config['host'] ?? 'N/A'); ?>:<?php echo $db_config['port'] ?? 'N/A'; ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Database:</span>
                                <span class="value"><?php echo htmlspecialchars($db_config['name'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Tables:</span>
                                <span class="value">contact_submissions, contact_attachments, admin_users, admin_sessions</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-user-shield"></i> Admin Account</h4>
                        <div class="review-details">
                            <div class="detail">
                                <span class="label">Name:</span>
                                <span class="value"><?php echo htmlspecialchars(($admin_info['first_name'] ?? '') . ' ' . ($admin_info['last_name'] ?? '')); ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Username:</span>
                                <span class="value"><?php echo htmlspecialchars($admin_info['username'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo htmlspecialchars($admin_info['email'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-cog"></i> System Configuration</h4>
                        <div class="review-details">
                            <div class="detail">
                                <span class="label">Environment:</span>
                                <span class="value">Production (.env file created)</span>
                            </div>
                            <div class="detail">
                                <span class="label">Security:</span>
                                <span class="value">Encryption keys generated</span>
                            </div>
                            <div class="detail">
                                <span class="label">Protection:</span>
                                <span class="value">.htaccess rules applied</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="completion-actions">
                    <button type="button"
                            class="btn btn-success btn-block"
                            onclick="completeInstallation()">
                        <i class="fas fa-flag-checkered"></i>
                        Complete Installation
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Installation Completed -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🎉 Installation Complete!</h3>
                    <p class="card-subtitle">Your contact form system is now ready for use</p>
                </div>

                <div class="success-content">
                    <div class="success-icon">
                        <i class="fas fa-trophy"></i>
                    </div>

                    <div class="success-message">
                        <h4>Congratulations!</h4>
                        <p>Your professional contact form system with automatic field mapping has been successfully installed and configured.</p>
                    </div>

                    <div class="access-links">
                        <div class="link-item">
                            <div class="link-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="link-info">
                                <h4>Admin Dashboard</h4>
                                <p>Manage contact submissions and system settings</p>
                                <a href="../admin/" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    Access Admin Panel
                                </a>
                            </div>
                        </div>

                        <div class="link-item">
                            <div class="link-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="link-info">
                                <h4>Contact Form</h4>
                                <p>Your public contact form with auto-save functionality</p>
                                <a href="../" class="btn btn-secondary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    View Contact Form
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Next Steps</h3>
                <p class="card-subtitle">Recommended actions after installation</p>
            </div>

            <div class="next-steps">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Test Your Contact Form</h4>
                        <p>Submit a test message to ensure the automatic field mapping and database storage are working correctly.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Customize Settings</h4>
                        <p>Use the admin dashboard to configure email notifications, form options, and security settings.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Update Security Settings</h4>
                        <p>Review the .env file and update the APP_URL setting to match your domain. Enable SSL in production.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Remove Installation Files</h4>
                        <p>For enhanced security, you can manually delete the /install directory after completing the setup.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h4>Set Up Backups</h4>
                        <p>Configure regular database backups to protect your contact submissions and system data.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Information</h3>
                <p class="card-subtitle">Important details about your installation</p>
            </div>

            <div class="system-info">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Installation Date:</span>
                        <span class="value"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">PHP Version:</span>
                        <span class="value"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">System Version:</span>
                        <span class="value">Contact Form System v1.0</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Database Engine:</span>
                        <span class="value">MySQL with UTF8MB4</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Security:</span>
                        <span class="value">Encryption keys generated</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Features:</span>
                        <span class="value">Auto-save, Field mapping, Admin panel</span>
                    </div>
                </div>

                <div class="feature-highlights">
                    <h4>Key Features Installed:</h4>
                    <ul>
                        <li><strong>Automatic Field Mapping:</strong> "Full Name" → "First Name" + "Last Name"</li>
                        <li><strong>Real-time Auto-save:</strong> Form data saved as users type</li>
                        <li><strong>Admin Dashboard:</strong> Manage submissions and system settings</li>
                        <li><strong>Database Integration:</strong> MySQL storage with error handling</li>
                        <li><strong>Security Features:</strong> CSRF protection, rate limiting, encryption</li>
                        <li><strong>File Uploads:</strong> Attachment support with validation</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.completion-summary {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border-radius: 12px;
    margin-bottom: 24px;
}

.summary-icon {
    font-size: 48px;
    flex-shrink: 0;
}

.summary-content h4 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 700;
}

.summary-content p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.5;
}

.installation-review {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-bottom: 32px;
}

.review-section h4 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 8px;
}

.review-details {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
}

.detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.detail:last-child {
    border-bottom: none;
}

.detail .label {
    font-weight: 600;
    color: #4a5568;
}

.detail .value {
    color: #2d3748;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 14px;
}

.completion-actions {
    text-align: center;
}

.success-content {
    text-align: center;
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    margin: 0 auto 24px;
}

.success-message h4 {
    margin: 0 0 12px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
}

.success-message p {
    margin: 0 0 32px 0;
    font-size: 16px;
    color: #4a5568;
    line-height: 1.6;
}

.access-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.link-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 24px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: #f7fafc;
}

.link-icon {
    width: 60px;
    height: 60px;
    background: #4299e1;
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.link-info h4 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
}

.link-info p {
    margin: 0 0 16px 0;
    color: #4a5568;
    font-size: 14px;
    line-height: 1.5;
}

.next-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step-item {
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
    color: #4a5568;
    font-size: 14px;
    line-height: 1.5;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.info-item .label {
    font-weight: 600;
    color: #4a5568;
}

.info-item .value {
    color: #2d3748;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 14px;
}

.feature-highlights h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.feature-highlights ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.feature-highlights li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px 0;
    font-size: 14px;
    color: #4a5568;
    line-height: 1.5;
}

.feature-highlights li::before {
    content: "✓";
    color: #48bb78;
    font-weight: bold;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .completion-summary {
        flex-direction: column;
        text-align: center;
    }

    .access-links {
        grid-template-columns: 1fr;
    }

    .link-item {
        flex-direction: column;
        text-align: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let installationCompleted = <?php echo json_encode($installation_completed); ?>;

async function completeInstallation() {
    try {
        window.Installer.showLoading('Completing installation...');

        const formData = new FormData();
        formData.append('action', 'complete_installation');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        window.Installer.hideLoading();

        if (result.success) {
            installationCompleted = true;
            window.Installer.showSuccess('Installation completed successfully!');

            // Reload page to show completion state
            setTimeout(() => {
                window.location.reload();
            }, 2000);

        } else {
            window.Installer.showError(result.error || 'Failed to complete installation');
        }

    } catch (error) {
        window.Installer.hideLoading();
        window.Installer.showError('Error completing installation: ' + error.message);
    }
}

function updateNextButton() {
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        if (installationCompleted) {
            nextButtonContainer.innerHTML = `
                <div style="text-align: center;">
                    <p style="color: #48bb78; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-check-circle"></i> Installation Complete!
                    </p>
                    <a href="../admin/" class="btn btn-success" target="_blank">
                        <i class="fas fa-tachometer-alt"></i>
                        Access Admin Dashboard
                    </a>
                </div>
            `;
        } else {
            nextButtonContainer.innerHTML = `
                <div style="text-align: center; color: #4a5568;">
                    <p>Click "Complete Installation" above to finish setup</p>
                </div>
            `;
        }
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateNextButton();
});
</script>
