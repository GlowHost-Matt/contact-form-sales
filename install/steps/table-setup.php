<?php
/**
 * Installation Step 3: Database Table Setup
 * Create required tables and indexes for the contact form system
 */

// Handle table creation request
if ($_POST['action'] ?? '' === 'create_tables') {
    header('Content-Type: application/json');

    try {
        // Get database connection details from session
        $db_config = $_SESSION['install_data']['database'] ?? null;
        if (!$db_config) {
            throw new Exception('Database configuration not found. Please go back and test your connection.');
        }

        // Connect to database
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $results = [];

        // Create contact_submissions table
        $contact_table_sql = "
            CREATE TABLE IF NOT EXISTS contact_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL COMMENT 'First name from split full name',
                last_name VARCHAR(100) NOT NULL COMMENT 'Last name from split full name',
                email_address VARCHAR(255) NOT NULL COMMENT 'Customer email address',
                inquiry_subject VARCHAR(250) NOT NULL COMMENT 'Subject of the inquiry',
                inquiry_message TEXT NOT NULL COMMENT 'Main message content',
                department VARCHAR(100) NOT NULL COMMENT 'Department selected',
                phone_number VARCHAR(50) NULL COMMENT 'Optional phone number',
                domain_name VARCHAR(255) NULL COMMENT 'Optional domain name',
                reference_id VARCHAR(50) NULL COMMENT 'Unique reference ID',
                ip_address VARCHAR(45) NULL COMMENT 'Customer IP address',
                user_agent TEXT NULL COMMENT 'Browser user agent',
                browser_name VARCHAR(100) NULL COMMENT 'Parsed browser name',
                operating_system VARCHAR(100) NULL COMMENT 'Parsed OS',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_email (email_address),
                INDEX idx_created_at (created_at),
                INDEX idx_reference_id (reference_id),
                INDEX idx_department (department),
                INDEX idx_name (first_name, last_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Contact form submissions with automatic field mapping'
        ";

        $pdo->exec($contact_table_sql);
        $results[] = [
            'table' => 'contact_submissions',
            'status' => 'success',
            'message' => 'Contact submissions table created successfully'
        ];

        // Create contact_attachments table
        $attachments_table_sql = "
            CREATE TABLE IF NOT EXISTS contact_attachments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                submission_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL COMMENT 'Stored filename',
                original_name VARCHAR(255) NOT NULL COMMENT 'Original filename',
                file_size INT NOT NULL COMMENT 'File size in bytes',
                mime_type VARCHAR(100) NOT NULL COMMENT 'File MIME type',
                description TEXT NULL COMMENT 'File description',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (submission_id) REFERENCES contact_submissions(id) ON DELETE CASCADE,
                INDEX idx_submission_id (submission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='File attachments for contact submissions'
        ";

        $pdo->exec($attachments_table_sql);
        $results[] = [
            'table' => 'contact_attachments',
            'status' => 'success',
            'message' => 'Contact attachments table created successfully'
        ];

        // Create admin_users table
        $admin_table_sql = "
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NULL,
                last_name VARCHAR(100) NULL,
                role ENUM('admin', 'manager', 'viewer') DEFAULT 'admin',
                is_active TINYINT(1) DEFAULT 1,
                last_login TIMESTAMP NULL,
                login_attempts INT DEFAULT 0,
                locked_until TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Admin users for contact form management'
        ";

        $pdo->exec($admin_table_sql);
        $results[] = [
            'table' => 'admin_users',
            'status' => 'success',
            'message' => 'Admin users table created successfully'
        ];

        // Create admin_sessions table
        $sessions_table_sql = "
            CREATE TABLE IF NOT EXISTS admin_sessions (
                id VARCHAR(128) PRIMARY KEY,
                user_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,

                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Admin user sessions'
        ";

        $pdo->exec($sessions_table_sql);
        $results[] = [
            'table' => 'admin_sessions',
            'status' => 'success',
            'message' => 'Admin sessions table created successfully'
        ];

        // Get table information
        $table_info = [];
        foreach (['contact_submissions', 'contact_attachments', 'admin_users', 'admin_sessions'] as $table) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = ? AND table_name = ?");
            $stmt->execute([$db_config['name'], $table]);
            $info = $stmt->fetch();
            $table_info[$table] = $info['count'];
        }

        // Store table creation success in session
        $_SESSION['install_data']['tables_created'] = true;
        $_SESSION['install_data']['table_info'] = $table_info;

        echo json_encode([
            'success' => true,
            'results' => $results,
            'table_info' => $table_info
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

// Get database info from session
$db_config = $_SESSION['install_data']['database'] ?? null;
$tables_created = $_SESSION['install_data']['tables_created'] ?? false;
$table_info = $_SESSION['install_data']['table_info'] ?? [];
?>

<div class="table-setup-content">
    <?php if (!$db_config): ?>
        <!-- No Database Configuration -->
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Database configuration not found. Please go back and test your database connection first.</span>
        </div>
    <?php else: ?>
        <!-- Database Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Database Information</h3>
                <p class="card-subtitle">Connected to your MySQL database</p>
            </div>

            <div class="db-info">
                <div class="info-item">
                    <span class="label">Host:</span>
                    <span class="value"><?php echo htmlspecialchars($db_config['host']); ?>:<?php echo $db_config['port']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Database:</span>
                    <span class="value"><?php echo htmlspecialchars($db_config['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">User:</span>
                    <span class="value"><?php echo htmlspecialchars($db_config['user']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">MySQL Version:</span>
                    <span class="value"><?php echo htmlspecialchars($db_config['version'] ?? 'Unknown'); ?></span>
                </div>
            </div>
        </div>

        <!-- Table Creation -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Database Tables</h3>
                <p class="card-subtitle">Create required tables for the contact form system</p>
            </div>

            <div class="tables-to-create">
                <div class="table-item">
                    <div class="table-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="table-info">
                        <h4>contact_submissions</h4>
                        <p>Stores contact form submissions with automatic name field mapping</p>
                        <div class="table-details">
                            <span class="detail">14 columns</span>
                            <span class="detail">5 indexes</span>
                            <span class="detail">UTF8MB4 charset</span>
                        </div>
                    </div>
                </div>

                <div class="table-item">
                    <div class="table-icon">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div class="table-info">
                        <h4>contact_attachments</h4>
                        <p>File attachments linked to contact submissions</p>
                        <div class="table-details">
                            <span class="detail">8 columns</span>
                            <span class="detail">Foreign key</span>
                            <span class="detail">Cascade delete</span>
                        </div>
                    </div>
                </div>

                <div class="table-item">
                    <div class="table-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="table-info">
                        <h4>admin_users</h4>
                        <p>Admin accounts for managing the contact form system</p>
                        <div class="table-details">
                            <span class="detail">12 columns</span>
                            <span class="detail">Role-based access</span>
                            <span class="detail">Login security</span>
                        </div>
                    </div>
                </div>

                <div class="table-item">
                    <div class="table-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="table-info">
                        <h4>admin_sessions</h4>
                        <p>Secure session management for admin users</p>
                        <div class="table-details">
                            <span class="detail">7 columns</span>
                            <span class="detail">Auto-expire</span>
                            <span class="detail">IP tracking</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$tables_created): ?>
                <div class="table-actions">
                    <button type="button"
                            class="btn btn-primary btn-block"
                            onclick="createDatabaseTables()">
                        <i class="fas fa-database"></i>
                        Create Database Tables
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Table Creation Results -->
        <div class="card" id="creation-results" style="<?php echo $tables_created ? '' : 'display: none;'; ?>">
            <div class="card-header">
                <h3 class="card-title">Table Creation Results</h3>
                <p class="card-subtitle">Database setup status</p>
            </div>

            <div id="table-results-container">
                <?php if ($tables_created && $table_info): ?>
                    <div class="results-summary">
                        <?php foreach ($table_info as $table => $columns): ?>
                            <div class="result-item success">
                                <i class="fas fa-check-circle"></i>
                                <span><strong><?php echo $table; ?></strong> - <?php echo $columns; ?> columns</span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>All database tables created successfully! Your database is ready.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.db-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f7fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
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

.tables-to-create {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.table-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f7fafc;
}

.table-icon {
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

.table-info h4 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
    font-family: 'Monaco', 'Menlo', monospace;
}

.table-info p {
    margin: 0 0 8px 0;
    color: #4a5568;
    line-height: 1.5;
}

.table-details {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.table-details .detail {
    font-size: 12px;
    background: #e2e8f0;
    color: #4a5568;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.table-actions {
    margin-top: 24px;
    text-align: center;
}

.results-summary {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    font-weight: 500;
}

.result-item.success {
    background: #f0fff4;
    color: #22543d;
}

.result-item.error {
    background: #fed7d7;
    color: #742a2a;
}

@media (max-width: 768px) {
    .db-info {
        grid-template-columns: 1fr;
    }

    .table-item {
        flex-direction: column;
        text-align: center;
    }

    .table-details {
        justify-content: center;
    }
}
</style>

<script>
let tablesCreated = <?php echo json_encode($tables_created); ?>;

async function createDatabaseTables() {
    const resultsCard = document.getElementById('creation-results');
    const resultsContainer = document.getElementById('table-results-container');

    // Show loading state
    resultsCard.style.display = 'block';
    resultsContainer.innerHTML = `
        <div class="result-item testing">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Creating database tables...</span>
        </div>
    `;

    try {
        const formData = new FormData();
        formData.append('action', 'create_tables');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            tablesCreated = true;

            let resultsHtml = '<div class="results-summary">';
            result.results.forEach(table => {
                resultsHtml += `
                    <div class="result-item success">
                        <i class="fas fa-check-circle"></i>
                        <span><strong>${table.table}</strong> - ${table.message}</span>
                    </div>
                `;
            });
            resultsHtml += '</div>';

            resultsHtml += `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>All database tables created successfully! Your database is ready.</span>
                </div>
            `;

            resultsContainer.innerHTML = resultsHtml;

            // Hide the create button
            const createButton = document.querySelector('.table-actions');
            if (createButton) {
                createButton.style.display = 'none';
            }

            // Update next button
            updateNextButton();

        } else {
            resultsContainer.innerHTML = `
                <div class="result-item error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Table creation failed: ${result.error}</span>
                </div>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Please check the error above and try again.</span>
                </div>
            `;
        }

    } catch (error) {
        resultsContainer.innerHTML = `
            <div class="result-item error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Connection error: ${error.message}</span>
            </div>
        `;
    }
}

function updateNextButton() {
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        const dbConfigExists = <?php echo json_encode($db_config !== null); ?>;

        nextButtonContainer.innerHTML = `
            <button type="button" class="btn btn-primary"
                    ${(dbConfigExists && tablesCreated) ? '' : 'disabled'}
                    onclick="window.Installer.nextStep()">
                <i class="fas fa-arrow-right"></i>
                ${tablesCreated ? 'Continue to Admin Setup' : 'Create Tables First'}
            </button>
        `;
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateNextButton();

    // Auto-create tables if not created yet and database is configured
    const dbConfigExists = <?php echo json_encode($db_config !== null); ?>;
    if (dbConfigExists && !tablesCreated) {
        // Auto-start table creation after a short delay
        setTimeout(() => {
            createDatabaseTables();
        }, 1000);
    }
});
</script>
