<?php
/**
 * GlowHost Contact Form System - Configuration (Same Development Environment)
 * Generated: 2025-08-08 01:30:00
 */

// Database Configuration - Same Development Environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'glowhost_contact_dev');
define('DB_USER', 'root');
define('DB_PASS', '');

// System Configuration
define('SITE_URL', 'http://localhost:3000');
define('ADMIN_URL', 'http://localhost:3000/admin');
define('SYSTEM_VERSION', '4.0');

// Initialize database connection (TEMPORARILY DISABLED FOR TESTING)
/*
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
*/

// Temporary: Set up a dummy $pdo variable for testing
$pdo = null;
?>