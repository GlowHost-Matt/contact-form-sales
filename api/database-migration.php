<?php
/**
 * GlowHost Contact Form - Database Migration for React.js Integration
 * Updates existing database schema to support React.js front-end features
 *
 * Run this script ONCE after installing the React.js front-end
 */

// Include database configuration
require_once '../config.php';

// Migration version tracking
define('MIGRATION_VERSION', '2.0.0');
define('MIGRATION_NAME', 'React.js Integration');

try {
    echo "<h1>GlowHost Contact Form - Database Migration</h1>\n";
    echo "<h2>Migration: " . MIGRATION_NAME . " (v" . MIGRATION_VERSION . ")</h2>\n";
    echo "<pre>\n";

    // Check if migration was already applied
    $migration_check = $pdo->query("
        SELECT COUNT(*) as count
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'contact_submissions'
        AND COLUMN_NAME = 'file_attachments'
    ");
    $already_migrated = $migration_check->fetch()['count'] > 0;

    if ($already_migrated) {
        echo "✅ Migration already applied - database is up to date!\n";
        echo "</pre>\n";
        exit();
    }

    echo "🔍 Starting database migration...\n\n";

    // Begin transaction
    $pdo->beginTransaction();

    // 1. Add file attachments support
    echo "1. Adding file attachments support...\n";
    $pdo->exec("
        ALTER TABLE contact_submissions
        ADD COLUMN file_attachments JSON NULL COMMENT 'JSON array of uploaded file information'
    ");
    echo "   ✅ Added file_attachments column\n";

    // 2. Add enhanced user agent tracking
    echo "\n2. Enhancing user agent tracking...\n";
    // Check if columns already exist before adding them
    $columns_to_add = [
        'ipv4_address' => "VARCHAR(45) NULL COMMENT 'IPv4 address from front-end detection'",
        'session_data' => "JSON NULL COMMENT 'Additional session data from React.js'",
        'form_version' => "VARCHAR(20) DEFAULT 'react-1.0' COMMENT 'Frontend version identifier'"
    ];

    foreach ($columns_to_add as $column => $definition) {
        $column_exists = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'contact_submissions'
            AND COLUMN_NAME = '$column'
        ");

        if ($column_exists->fetch()['count'] == 0) {
            $pdo->exec("ALTER TABLE contact_submissions ADD COLUMN $column $definition");
            echo "   ✅ Added $column column\n";
        } else {
            echo "   ℹ️  Column $column already exists\n";
        }
    }

    // 3. Create file attachments table for detailed tracking
    echo "\n3. Creating file attachments tracking table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS file_attachments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            submission_id INT NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            safe_filename VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_description TEXT NULL,
            upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('uploaded', 'processed', 'deleted') DEFAULT 'uploaded',
            FOREIGN KEY (submission_id) REFERENCES contact_submissions(id) ON DELETE CASCADE,
            INDEX idx_submission_id (submission_id),
            INDEX idx_status (status),
            INDEX idx_upload_timestamp (upload_timestamp)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        COMMENT='File attachments for contact form submissions'
    ");
    echo "   ✅ Created file_attachments table\n";

    // 4. Add API tracking table
    echo "\n4. Creating API usage tracking table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            endpoint VARCHAR(100) NOT NULL,
            method VARCHAR(10) NOT NULL,
            user_agent TEXT NULL,
            request_data JSON NULL,
            response_status INT NOT NULL,
            response_time_ms INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_address (ip_address),
            INDEX idx_endpoint (endpoint),
            INDEX idx_created_at (created_at),
            INDEX idx_status (response_status)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        COMMENT='API request tracking for rate limiting and analytics'
    ");
    echo "   ✅ Created api_requests table\n";

    // 5. Update settings table for React.js configuration
    echo "\n5. Adding React.js configuration settings...\n";
    $react_settings = [
        ['react_frontend_enabled', '1', 'Enable React.js front-end integration'],
        ['react_api_version', MIGRATION_VERSION, 'Current React.js API version'],
        ['file_upload_enabled', '1', 'Enable file upload functionality'],
        ['max_file_size', '10485760', 'Maximum file upload size in bytes (10MB)'],
        ['allowed_file_types', 'jpg,jpeg,png,gif,bmp,webp,pdf,txt,log,zip,rar,7z', 'Allowed file upload extensions'],
        ['api_rate_limit', '10', 'API requests per minute per IP'],
        ['email_notifications_api', '1', 'Send email notifications for API submissions']
    ];

    $settings_stmt = $pdo->prepare("
        INSERT INTO settings (key_name, value, description)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        value = VALUES(value),
        description = VALUES(description),
        updated_at = CURRENT_TIMESTAMP
    ");

    foreach ($react_settings as $setting) {
        $settings_stmt->execute($setting);
        echo "   ✅ Added/updated setting: {$setting[0]}\n";
    }

    // 6. Create migration log table
    echo "\n6. Creating migration tracking...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migration_log (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version VARCHAR(20) NOT NULL,
            name VARCHAR(100) NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            description TEXT NULL,
            UNIQUE KEY unique_version (version)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        COMMENT='Database migration tracking'
    ");

    // Log this migration
    $log_stmt = $pdo->prepare("
        INSERT INTO migration_log (version, name, description)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE applied_at = CURRENT_TIMESTAMP
    ");
    $log_stmt->execute([
        MIGRATION_VERSION,
        MIGRATION_NAME,
        'Database schema updates for React.js front-end integration including file attachments, enhanced tracking, and API support'
    ]);
    echo "   ✅ Logged migration\n";

    // 7. Update existing data for compatibility
    echo "\n7. Updating existing data for compatibility...\n";
    $pdo->exec("
        UPDATE contact_submissions
        SET form_version = 'php-legacy'
        WHERE form_version IS NULL
    ");
    echo "   ✅ Updated existing submissions with version tags\n";

    // Commit transaction
    $pdo->commit();

    echo "\n🎉 Migration completed successfully!\n\n";
    echo "Database is now ready for React.js front-end integration.\n\n";

    // Show summary
    echo "MIGRATION SUMMARY:\n";
    echo "==================\n";
    echo "• Added file attachments support\n";
    echo "• Enhanced user agent tracking\n";
    echo "• Created file attachments table\n";
    echo "• Added API request tracking\n";
    echo "• Updated configuration settings\n";
    echo "• Added migration logging\n";
    echo "• Updated existing data compatibility\n\n";

    echo "Next steps:\n";
    echo "1. Deploy React.js front-end to /helpdesk/ directory\n";
    echo "2. Test API endpoints: /api/submit-form.php\n";
    echo "3. Verify file upload functionality\n";
    echo "4. Configure email notification settings in admin panel\n\n";

    echo "✅ Ready for React.js integration!\n";

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }

    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\nDatabase has been rolled back to previous state.\n";
    echo "Please check the error and try again.\n\n";

    // Log error
    error_log("GlowHost migration error: " . $e->getMessage());
}

echo "</pre>\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - GlowHost Contact Form</title>
    <style>
        body {
            font-family: monospace;
            background: #f5f5f5;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        pre {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            overflow-x: auto;
        }
        h1, h2 {
            color: #333;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <p><strong>Important:</strong> This migration script should only be run once. After successful completion, you can delete this file for security.</p>
    <p><a href="../admin/">← Back to Admin Panel</a></p>
</body>
</html>