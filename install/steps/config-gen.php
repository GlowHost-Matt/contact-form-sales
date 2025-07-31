<?php
/**
 * Installation Step 5: Configuration Generation
 * Generate config files and security settings
 */

// Handle configuration generation
if ($_POST['action'] ?? '' === 'generate_config') {
    header('Content-Type: application/json');

    try {
        // Get installation data
        $db_config = $_SESSION['install_data']['database'] ?? null;
        $admin_info = $_SESSION['install_data']['admin_info'] ?? null;

        if (!$db_config || !$admin_info) {
            throw new Exception('Missing installation data. Please complete previous steps.');
        }

        $results = [];

        // 1. Create .env file
        $env_content = generateEnvFile($db_config);
        $env_written = file_put_contents('../.env', $env_content);

        if ($env_written !== false) {
            $results[] = [
                'file' => '.env',
                'status' => 'success',
                'message' => 'Environment configuration created',
                'path' => realpath('../.env')
            ];
        } else {
            throw new Exception('Failed to create .env file');
        }

        // 2. Create database config PHP file
        $db_config_content = generateDatabaseConfigFile($db_config);
        if (!is_dir('../config')) {
            mkdir('../config', 0755, true);
        }
        $db_config_written = file_put_contents('../config/database.php', $db_config_content);

        if ($db_config_written !== false) {
            $results[] = [
                'file' => 'config/database.php',
                'status' => 'success',
                'message' => 'Database configuration created',
                'path' => realpath('../config/database.php')
            ];
        } else {
            throw new Exception('Failed to create database config file');
        }

        // 3. Create admin config file
        $admin_config_content = generateAdminConfigFile($admin_info);
        $admin_config_written = file_put_contents('../config/admin.php', $admin_config_content);

        if ($admin_config_written !== false) {
            $results[] = [
                'file' => 'config/admin.php',
                'status' => 'success',
                'message' => 'Admin configuration created',
                'path' => realpath('../config/admin.php')
            ];
        } else {
            throw new Exception('Failed to create admin config file');
        }

        // 4. Create security keys file
        $security_content = generateSecurityConfigFile();
        $security_written = file_put_contents('../config/security.php', $security_content);

        if ($security_written !== false) {
            $results[] = [
                'file' => 'config/security.php',
                'status' => 'success',
                'message' => 'Security configuration created',
                'path' => realpath('../config/security.php')
            ];
        } else {
            throw new Exception('Failed to create security config file');
        }

        // 5. Create .htaccess for security
        $htaccess_content = generateHtaccessFile();
        $htaccess_written = file_put_contents('../.htaccess', $htaccess_content);

        if ($htaccess_written !== false) {
            $results[] = [
                'file' => '.htaccess',
                'status' => 'success',
                'message' => 'Security rules created',
                'path' => realpath('../.htaccess')
            ];
        } else {
            $results[] = [
                'file' => '.htaccess',
                'status' => 'warning',
                'message' => 'Could not create .htaccess (may need manual creation)',
                'path' => '../.htaccess'
            ];
        }

        // 6. Set file permissions
        $permission_results = setFilePermissions($results);
        $results = array_merge($results, $permission_results);

        // Store success in session
        $_SESSION['install_data']['config_generated'] = true;
        $_SESSION['install_data']['config_files'] = $results;

        echo json_encode([
            'success' => true,
            'results' => $results,
            'message' => 'Configuration files generated successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

// Helper functions
function generateEnvFile($db_config) {
    return "# Contact Form System Environment Configuration
# Generated on " . date('Y-m-d H:i:s') . "

# Database Configuration
DB_HOST={$db_config['host']}
DB_PORT={$db_config['port']}
DB_NAME={$db_config['name']}
DB_USER={$db_config['user']}
DB_PASSWORD={$db_config['pass']}
DB_CHARSET=utf8mb4

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.com

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_EXPIRY=3600

# Email Configuration (optional)
MAIL_ENABLED=false
MAIL_FROM_ADDRESS=noreply@yoursite.com
MAIL_FROM_NAME=\"Contact Form System\"

# File Upload Settings
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,txt,doc,docx

# Contact Form Settings
CONTACT_FORM_ENABLED=true
AUTO_SAVE_ENABLED=true
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=300
";
}

function generateDatabaseConfigFile($db_config) {
    return "<?php
/**
 * Database Configuration
 * Generated by Contact Form Installation Wizard
 * Created: " . date('Y-m-d H:i:s') . "
 */

// Prevent direct access
if (!defined('CF_SYSTEM')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

return [
    'host' => '{$db_config['host']}',
    'port' => {$db_config['port']},
    'database' => '{$db_config['name']}',
    'username' => '{$db_config['user']}',
    'password' => '{$db_config['pass']}',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ]
];
";
}

function generateAdminConfigFile($admin_info) {
    return "<?php
/**
 * Admin Configuration
 * Generated by Contact Form Installation Wizard
 * Created: " . date('Y-m-d H:i:s') . "
 */

// Prevent direct access
if (!defined('CF_SYSTEM')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

return [
    'session' => [
        'name' => 'CF_ADMIN_SESSION',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset(\$_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ],

    'login' => [
        'max_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'remember_me_duration' => 2592000, // 30 days
        'password_reset_expiry' => 3600 // 1 hour
    ],

    'security' => [
        'csrf_token_expiry' => 3600,
        'require_ssl' => false, // Set to true in production with SSL
        'ip_whitelist' => [], // Optional IP whitelist
        'two_factor_enabled' => false
    ],

    'ui' => [
        'items_per_page' => 25,
        'date_format' => 'Y-m-d H:i:s',
        'timezone' => 'UTC',
        'theme' => 'default'
    ],

    'notifications' => [
        'email_on_login' => false,
        'email_on_new_submission' => true,
        'email_on_failed_login' => true
    ]
];
";
}

function generateSecurityConfigFile() {
    $session_key = bin2hex(random_bytes(32));
    $csrf_key = bin2hex(random_bytes(32));
    $encryption_key = bin2hex(random_bytes(32));

    return "<?php
/**
 * Security Configuration
 * Generated by Contact Form Installation Wizard
 * Created: " . date('Y-m-d H:i:s') . "
 *
 * WARNING: Keep these keys secret and secure!
 */

// Prevent direct access
if (!defined('CF_SYSTEM')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

return [
    'keys' => [
        'session' => '{$session_key}',
        'csrf' => '{$csrf_key}',
        'encryption' => '{$encryption_key}'
    ],

    'password' => [
        'algorithm' => PASSWORD_ARGON2ID,
        'options' => [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]
    ],

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 5,
        'time_window' => 300, // 5 minutes
        'cleanup_interval' => 3600 // 1 hour
    ],

    'file_upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'scan_for_malware' => false,
        'quarantine_suspicious' => true
    ]
];
";
}

function generateHtaccessFile() {
    return "# Contact Form System Security Rules
# Generated: " . date('Y-m-d H:i:s') . "

# Deny access to sensitive files
<Files \".env\">
    Order allow,deny
    Deny from all
</Files>

<FilesMatch \"\\.(log|sql|bak|backup|old|tmp)$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Deny access to config directory
<Directory \"config\">
    Order allow,deny
    Deny from all
</Directory>

# Deny access to installation directory after setup
<Directory \"install\">
    Order allow,deny
    Deny from all
</Directory>

# Prevent directory browsing
Options -Indexes

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
    Header always set Permissions-Policy \"geolocation=(), microphone=(), camera=()\"
</IfModule>

# Hide server information
ServerTokens Prod
ServerSignature Off

# Prevent access to PHP files in upload directory
<Directory \"uploads\">
    <FilesMatch \"\\.php$\">
        Order allow,deny
        Deny from all
    </FilesMatch>
</Directory>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css \"access plus 1 year\"
    ExpiresByType application/javascript \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
</IfModule>
";
}

function setFilePermissions($files) {
    $results = [];

    $permissions = [
        '.env' => 0600,
        'config/database.php' => 0600,
        'config/admin.php' => 0644,
        'config/security.php' => 0600,
        '.htaccess' => 0644
    ];

    foreach ($permissions as $file => $perm) {
        $full_path = '../' . $file;
        if (file_exists($full_path)) {
            if (chmod($full_path, $perm)) {
                $results[] = [
                    'file' => $file,
                    'status' => 'success',
                    'message' => sprintf('Permissions set to %o', $perm),
                    'action' => 'chmod'
                ];
            } else {
                $results[] = [
                    'file' => $file,
                    'status' => 'warning',
                    'message' => 'Could not set file permissions (may need manual adjustment)',
                    'action' => 'chmod'
                ];
            }
        }
    }

    return $results;
}

// Get existing data
$config_generated = $_SESSION['install_data']['config_generated'] ?? false;
$config_files = $_SESSION['install_data']['config_files'] ?? [];
$admin_created = $_SESSION['install_data']['admin_created'] ?? false;
?>

<div class="config-gen-content">
    <?php if (!$admin_created): ?>
        <!-- No Admin Created -->
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Admin account not found. Please go back and create an admin account first.</span>
        </div>
    <?php else: ?>
        <?php if (!$config_generated): ?>
            <!-- Configuration Generation -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Generate Configuration Files</h3>
                    <p class="card-subtitle">Create secure configuration and environment files</p>
                </div>

                <div class="config-preview">
                    <div class="config-item">
                        <div class="config-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="config-info">
                            <h4>.env</h4>
                            <p>Environment variables for database, security, and application settings</p>
                        </div>
                    </div>

                    <div class="config-item">
                        <div class="config-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="config-info">
                            <h4>config/database.php</h4>
                            <p>Database connection configuration with PDO options</p>
                        </div>
                    </div>

                    <div class="config-item">
                        <div class="config-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="config-info">
                            <h4>config/admin.php</h4>
                            <p>Admin panel settings, sessions, and security preferences</p>
                        </div>
                    </div>

                    <div class="config-item">
                        <div class="config-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="config-info">
                            <h4>config/security.php</h4>
                            <p>Security keys, encryption settings, and rate limiting configuration</p>
                        </div>
                    </div>

                    <div class="config-item">
                        <div class="config-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="config-info">
                            <h4>.htaccess</h4>
                            <p>Web server security rules and access controls</p>
                        </div>
                    </div>
                </div>

                <div class="config-actions">
                    <button type="button"
                            class="btn btn-primary btn-block"
                            onclick="generateConfigFiles()">
                        <i class="fas fa-file-code"></i>
                        Generate Configuration Files
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Configuration Generated -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configuration Generated</h3>
                    <p class="card-subtitle">All configuration files have been created</p>
                </div>

                <div class="config-results">
                    <?php foreach ($config_files as $file): ?>
                        <div class="result-item <?php echo $file['status']; ?>">
                            <div class="result-icon">
                                <?php if ($file['status'] === 'success'): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php elseif ($file['status'] === 'warning'): ?>
                                    <i class="fas fa-exclamation-triangle"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="result-info">
                                <h4><?php echo htmlspecialchars($file['file']); ?></h4>
                                <p><?php echo htmlspecialchars($file['message']); ?></p>
                                <?php if (isset($file['path'])): ?>
                                    <div class="file-path"><?php echo htmlspecialchars($file['path']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Configuration files generated successfully! Your system is ready for final setup.</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Security Notice -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Important Security Notice</h3>
                <p class="card-subtitle">Please review these security recommendations</p>
            </div>

            <div class="security-checklist">
                <div class="checklist-item">
                    <div class="checklist-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="checklist-info">
                        <h4>.env File Security</h4>
                        <p>The .env file contains sensitive database credentials. Ensure it's not accessible via web browser and has restrictive permissions (600).</p>
                    </div>
                </div>

                <div class="checklist-item">
                    <div class="checklist-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="checklist-info">
                        <h4>Security Keys</h4>
                        <p>Randomly generated encryption keys have been created. Keep the security.php file secure and never share these keys.</p>
                    </div>
                </div>

                <div class="checklist-item">
                    <div class="checklist-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="checklist-info">
                        <h4>Web Server Configuration</h4>
                        <p>The .htaccess file provides basic security rules. For enhanced security, consider implementing these rules at the server level.</p>
                    </div>
                </div>

                <div class="checklist-item">
                    <div class="checklist-icon">
                        <i class="fas fa-trash"></i>
                    </div>
                    <div class="checklist-info">
                        <h4>Installation Cleanup</h4>
                        <p>After installation completes, the install directory will be protected. You can manually delete it for additional security.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.config-preview {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.config-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f7fafc;
}

.config-icon {
    width: 48px;
    height: 48px;
    background: #4299e1;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.config-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
    font-family: 'Monaco', 'Menlo', monospace;
}

.config-info p {
    margin: 0;
    color: #4a5568;
    font-size: 14px;
    line-height: 1.5;
}

.config-actions {
    text-align: center;
}

.config-results {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.result-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid;
}

.result-item.success {
    background: #f0fff4;
    border-color: #9ae6b4;
}

.result-item.warning {
    background: #fffbeb;
    border-color: #f6e05e;
}

.result-item.error {
    background: #fed7d7;
    border-color: #feb2b2;
}

.result-icon {
    flex-shrink: 0;
    width: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.result-item.success .result-icon {
    color: #22543d;
}

.result-item.warning .result-icon {
    color: #744210;
}

.result-item.error .result-icon {
    color: #742a2a;
}

.result-info h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Monaco', 'Menlo', monospace;
}

.result-info p {
    margin: 0 0 4px 0;
    font-size: 14px;
}

.file-path {
    font-size: 12px;
    opacity: 0.7;
    font-family: 'Monaco', 'Menlo', monospace;
}

.security-checklist {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.checklist-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.checklist-icon {
    width: 40px;
    height: 40px;
    background: #ed8936;
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.checklist-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.checklist-info p {
    margin: 0;
    color: #4a5568;
    font-size: 14px;
    line-height: 1.5;
}
</style>

<script>
let configGenerated = <?php echo json_encode($config_generated); ?>;

async function generateConfigFiles() {
    const resultsContainer = document.querySelector('.config-results');

    // Show loading state
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="result-item testing">
                <div class="result-icon">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="result-info">
                    <h4>Generating configuration files...</h4>
                    <p>Please wait while we create your system configuration.</p>
                </div>
            </div>
        `;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'generate_config');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            configGenerated = true;

            // Reload page to show generated state
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } else {
            if (resultsContainer) {
                resultsContainer.innerHTML = `
                    <div class="result-item error">
                        <div class="result-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="result-info">
                            <h4>Configuration generation failed</h4>
                            <p>${result.error}</p>
                        </div>
                    </div>
                `;
            }
        }

    } catch (error) {
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="result-item error">
                    <div class="result-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="result-info">
                        <h4>Connection error</h4>
                        <p>${error.message}</p>
                    </div>
                </div>
            `;
        }
    }
}

function updateNextButton() {
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        const adminCreated = <?php echo json_encode($admin_created); ?>;

        nextButtonContainer.innerHTML = `
            <button type="button" class="btn btn-primary"
                    ${(adminCreated && configGenerated) ? '' : 'disabled'}
                    onclick="window.Installer.nextStep()">
                <i class="fas fa-arrow-right"></i>
                ${configGenerated ? 'Complete Installation' : 'Generate Configuration First'}
            </button>
        `;
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateNextButton();

    // Auto-generate config if not generated yet
    const adminCreated = <?php echo json_encode($admin_created); ?>;
    if (adminCreated && !configGenerated) {
        setTimeout(() => {
            generateConfigFiles();
        }, 1000);
    }
});
</script>
