<?php
/**
 * Installation Step 4: Admin Account Setup
 * Create the initial admin user account
 */

// Handle admin creation request
if ($_POST['action'] ?? '' === 'create_admin') {
    header('Content-Type: application/json');

    try {
        // Validate input
        $required_fields = ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        // Validation
        if (strlen($username) < 3) {
            throw new Exception('Username must be at least 3 characters long');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        // Check password strength
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter, one lowercase letter, and one number');
        }

        // Get database connection
        $db_config = $_SESSION['install_data']['database'] ?? null;
        if (!$db_config) {
            throw new Exception('Database configuration not found');
        }

        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            throw new Exception('Username or email already exists');
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Create admin user
        $stmt = $pdo->prepare('
            INSERT INTO admin_users (username, email, password_hash, first_name, last_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $username,
            $email,
            $password_hash,
            $first_name,
            $last_name,
            'admin',
            1
        ]);

        $admin_id = $pdo->lastInsertId();

        // Store admin creation success
        $_SESSION['install_data']['admin_created'] = true;
        $_SESSION['install_data']['admin_info'] = [
            'id' => $admin_id,
            'username' => $username,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name
        ];

        echo json_encode([
            'success' => true,
            'admin_id' => $admin_id,
            'message' => 'Admin account created successfully'
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

// Get existing admin info if created
$admin_created = $_SESSION['install_data']['admin_created'] ?? false;
$admin_info = $_SESSION['install_data']['admin_info'] ?? [];
$tables_created = $_SESSION['install_data']['tables_created'] ?? false;
?>

<div class="admin-setup-content">
    <?php if (!$tables_created): ?>
        <!-- No Tables Created -->
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Database tables not found. Please go back and create the required tables first.</span>
        </div>
    <?php else: ?>
        <?php if (!$admin_created): ?>
            <!-- Admin Creation Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Admin Account</h3>
                    <p class="card-subtitle">Set up your secure admin login for managing contact submissions</p>
                </div>

                <form id="admin-form" class="admin-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="form-input"
                                   placeholder="John"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="form-input"
                                   placeholder="Smith"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-input"
                               placeholder="admin"
                               minlength="3"
                               pattern="[a-zA-Z0-9_-]+"
                               required>
                        <div class="form-help">3+ characters, letters, numbers, underscores, and hyphens only</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-input"
                               placeholder="admin@yoursite.com"
                               required>
                        <div class="form-help">Used for login and system notifications</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-input"
                                   placeholder="Enter secure password"
                                   minlength="8"
                                   required>
                            <div class="form-help">Min 8 chars with uppercase, lowercase, and number</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <input type="password"
                                   id="confirm_password"
                                   name="confirm_password"
                                   class="form-input"
                                   placeholder="Confirm password"
                                   required>
                            <div class="form-help">Must match the password above</div>
                        </div>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="password-strength" id="password-strength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <div class="strength-text" id="strength-text">Password strength</div>
                        <div class="strength-requirements">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-times"></i> At least 8 characters
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="fas fa-times"></i> Lowercase letter
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="fas fa-times"></i> Uppercase letter
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-times"></i> Number
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus"></i>
                            Create Admin Account
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Created Successfully -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Admin Account Created</h3>
                    <p class="card-subtitle">Your admin account is ready</p>
                </div>

                <div class="admin-success">
                    <div class="success-icon">
                        <i class="fas fa-user-check"></i>
                    </div>

                    <div class="admin-details">
                        <div class="detail-item">
                            <span class="label">Name:</span>
                            <span class="value"><?php echo htmlspecialchars($admin_info['first_name'] . ' ' . $admin_info['last_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Username:</span>
                            <span class="value"><?php echo htmlspecialchars($admin_info['username']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Email:</span>
                            <span class="value"><?php echo htmlspecialchars($admin_info['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Role:</span>
                            <span class="value">Administrator</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Admin ID:</span>
                            <span class="value">#<?php echo $admin_info['id']; ?></span>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Admin account created successfully! You can now manage contact submissions and system settings.</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Security Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Security Features</h3>
                <p class="card-subtitle">Your admin account includes these security measures</p>
            </div>

            <div class="security-features">
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="security-info">
                        <h4>Password Security</h4>
                        <p>Passwords are hashed using PHP's secure password_hash() function with bcrypt</p>
                    </div>
                </div>

                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="security-info">
                        <h4>Session Management</h4>
                        <p>Secure sessions with IP tracking, auto-expiration, and CSRF protection</p>
                    </div>
                </div>

                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div class="security-info">
                        <h4>Brute Force Protection</h4>
                        <p>Account locking after failed login attempts to prevent unauthorized access</p>
                    </div>
                </div>

                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="security-info">
                        <h4>Activity Logging</h4>
                        <p>Login attempts, admin actions, and system changes are logged for security</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-actions {
    margin-top: 24px;
}

.password-strength {
    margin-top: 16px;
    padding: 16px;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.strength-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.strength-fill {
    height: 100%;
    transition: width 0.3s ease, background-color 0.3s ease;
    border-radius: 4px;
}

.strength-text {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 12px;
}

.strength-requirements {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.requirement.met {
    color: #22543d;
}

.requirement.met i {
    color: #48bb78;
}

.requirement:not(.met) {
    color: #742a2a;
}

.requirement:not(.met) i {
    color: #e53e3e;
}

.admin-success {
    text-align: center;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin: 0 auto 24px;
}

.admin-details {
    max-width: 400px;
    margin: 0 auto 24px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item .label {
    font-weight: 600;
    color: #4a5568;
}

.detail-item .value {
    color: #2d3748;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 14px;
}

.security-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.security-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.security-icon {
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

.security-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.security-info p {
    margin: 0;
    font-size: 14px;
    color: #4a5568;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .strength-requirements {
        grid-template-columns: 1fr;
    }

    .security-features {
        grid-template-columns: 1fr;
    }

    .security-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
let adminCreated = <?php echo json_encode($admin_created); ?>;

// Password strength checking
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthContainer = document.getElementById('password-strength');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (password.length > 0) {
                strengthContainer.style.display = 'block';
                updatePasswordStrength(password);
            } else {
                strengthContainer.style.display = 'none';
            }
        });
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;

            if (confirm.length > 0) {
                if (password === confirm) {
                    this.classList.remove('error');
                    this.classList.add('success');
                } else {
                    this.classList.remove('success');
                    this.classList.add('error');
                }
            } else {
                this.classList.remove('success', 'error');
            }
        });
    }

    // Form submission
    const form = document.getElementById('admin-form');
    if (form) {
        form.addEventListener('submit', handleAdminFormSubmit);
    }

    updateNextButton();
});

function updatePasswordStrength(password) {
    const requirements = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        number: /\d/.test(password)
    };

    const metCount = Object.values(requirements).filter(Boolean).length;
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');

    // Update strength bar
    const percentage = (metCount / 4) * 100;
    strengthFill.style.width = percentage + '%';

    if (metCount === 0) {
        strengthFill.style.backgroundColor = '#e53e3e';
        strengthText.textContent = 'Very Weak';
        strengthText.style.color = '#e53e3e';
    } else if (metCount === 1) {
        strengthFill.style.backgroundColor = '#ed8936';
        strengthText.textContent = 'Weak';
        strengthText.style.color = '#ed8936';
    } else if (metCount === 2) {
        strengthFill.style.backgroundColor = '#ecc94b';
        strengthText.textContent = 'Fair';
        strengthText.style.color = '#ecc94b';
    } else if (metCount === 3) {
        strengthFill.style.backgroundColor = '#48bb78';
        strengthText.textContent = 'Good';
        strengthText.style.color = '#48bb78';
    } else {
        strengthFill.style.backgroundColor = '#38a169';
        strengthText.textContent = 'Strong';
        strengthText.style.color = '#38a169';
    }

    // Update requirements
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(`req-${req}`);
        if (element) {
            if (requirements[req]) {
                element.classList.add('met');
                element.querySelector('i').className = 'fas fa-check';
            } else {
                element.classList.remove('met');
                element.querySelector('i').className = 'fas fa-times';
            }
        }
    });
}

async function handleAdminFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'create_admin');

    // Disable form
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            adminCreated = true;
            window.Installer.showSuccess('Admin account created successfully!');

            // Reload the page to show success state
            setTimeout(() => {
                window.location.reload();
            }, 1500);

        } else {
            window.Installer.showError(result.error);

            // Re-enable form
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }

    } catch (error) {
        window.Installer.showError('Failed to create admin account: ' + error.message);

        // Re-enable form
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }
}

function updateNextButton() {
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        const tablesCreated = <?php echo json_encode($tables_created); ?>;

        nextButtonContainer.innerHTML = `
            <button type="button" class="btn btn-primary"
                    ${(tablesCreated && adminCreated) ? '' : 'disabled'}
                    onclick="window.Installer.nextStep()">
                <i class="fas fa-arrow-right"></i>
                ${adminCreated ? 'Continue to Configuration' : 'Create Admin Account First'}
            </button>
        `;
    }
}
</script>
