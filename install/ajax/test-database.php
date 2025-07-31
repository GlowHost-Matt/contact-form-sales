<?php
/**
 * AJAX Database Connection Tester
 * Handles real-time database connection testing for the installer
 */

session_start();

// Security check - only allow during installation
if (file_exists('../../config/installed.lock')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Installation already completed']);
    exit;
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['db_host', 'db_port', 'db_name', 'db_user', 'db_pass'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Sanitize input
    $db_host = trim($data['db_host']);
    $db_port = intval($data['db_port']);
    $db_name = trim($data['db_name']);
    $db_user = trim($data['db_user']);
    $db_pass = $data['db_pass']; // Don't trim password in case it has spaces

    // Validate port
    if ($db_port < 1 || $db_port > 65535) {
        throw new Exception('Port must be between 1 and 65535');
    }

    // Test database connection
    $start_time = microtime(true);

    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]);

    // Test the connection with a simple query
    $stmt = $pdo->query('SELECT VERSION() as version, DATABASE() as current_db, NOW() as current_time');
    $info = $stmt->fetch();

    $connection_time = round((microtime(true) - $start_time) * 1000, 2);

    // Test if we can create tables (check permissions)
    $create_test = false;
    try {
        $pdo->exec('CREATE TEMPORARY TABLE test_permissions (id INT)');
        $create_test = true;
    } catch (PDOException $e) {
        // Creation failed - limited permissions
    }

    // Store successful connection details in session
    $_SESSION['install_data']['database'] = [
        'host' => $db_host,
        'port' => $db_port,
        'name' => $db_name,
        'user' => $db_user,
        'pass' => $db_pass,
        'version' => $info['version'],
        'can_create_tables' => $create_test
    ];

    // Return success response
    echo json_encode([
        'success' => true,
        'details' => [
            'host' => $db_host,
            'port' => $db_port,
            'database' => $db_name,
            'time' => $connection_time,
            'mysql_version' => $info['version'],
            'current_database' => $info['current_db'],
            'server_time' => $info['current_time'],
            'can_create_tables' => $create_test,
            'permissions' => $create_test ? 'Full access' : 'Limited (may need manual table creation)'
        ]
    ]);

} catch (PDOException $e) {
    // Database connection error
    $error_message = $e->getMessage();
    $user_friendly_error = getDatabaseErrorMessage($e);

    // Log the actual error for debugging
    error_log("Database connection test failed: " . $error_message);

    echo json_encode([
        'success' => false,
        'error' => $error_message,
        'details' => $user_friendly_error,
        'error_code' => $e->getCode()
    ]);

} catch (Exception $e) {
    // General error
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => [
            'title' => 'Configuration Error',
            'message' => 'Please check your database configuration.',
            'userAction' => 'Verify all fields are filled correctly and try again.'
        ]
    ]);
}

/**
 * Convert PDO exceptions to user-friendly error messages
 */
function getDatabaseErrorMessage($error) {
    $message = $error->getMessage();
    $code = $error->getCode();

    // MySQL error code mapping
    switch ($code) {
        case 1045: // Access denied
            return [
                'title' => 'Access Denied',
                'message' => 'The username or password is incorrect.',
                'userAction' => 'Please check your database username and password.',
                'techHint' => 'Error 1045: Access denied for user'
            ];

        case 2002: // Can't connect through socket
        case 2003: // Can't connect to server
            return [
                'title' => 'Connection Failed',
                'message' => 'Unable to connect to the database server.',
                'userAction' => 'Please check your host and port settings. Ensure the database server is running.',
                'techHint' => "Error {$code}: Connection refused"
            ];

        case 1049: // Unknown database
            return [
                'title' => 'Database Not Found',
                'message' => 'The specified database does not exist.',
                'userAction' => 'Please create the database first or check the database name spelling.',
                'techHint' => 'Error 1049: Unknown database'
            ];

        case 1044: // Access denied for database
            return [
                'title' => 'Database Access Denied',
                'message' => 'The user does not have access to this database.',
                'userAction' => 'Please grant the user access to the database or use a different user.',
                'techHint' => 'Error 1044: Access denied for user to database'
            ];

        case 2006: // MySQL server has gone away
            return [
                'title' => 'Connection Timeout',
                'message' => 'The database server is not responding.',
                'userAction' => 'The server may be overloaded. Please try again in a moment.',
                'techHint' => 'Error 2006: MySQL server has gone away'
            ];

        default:
            // Check for common error patterns
            if (strpos($message, 'Access denied') !== false) {
                return [
                    'title' => 'Access Denied',
                    'message' => 'Authentication failed with the provided credentials.',
                    'userAction' => 'Please verify your username and password.',
                    'techHint' => 'Authentication error'
                ];
            } elseif (strpos($message, 'Connection refused') !== false || strpos($message, 'Can\'t connect') !== false) {
                return [
                    'title' => 'Connection Failed',
                    'message' => 'Unable to reach the database server.',
                    'userAction' => 'Check your host, port, and ensure the server is running.',
                    'techHint' => 'Network connection error'
                ];
            } elseif (strpos($message, 'timeout') !== false) {
                return [
                    'title' => 'Connection Timeout',
                    'message' => 'The database server is taking too long to respond.',
                    'userAction' => 'Try again or check if the server is overloaded.',
                    'techHint' => 'Connection timeout'
                ];
            } elseif (strpos($message, 'Unknown database') !== false) {
                return [
                    'title' => 'Database Not Found',
                    'message' => 'The specified database does not exist.',
                    'userAction' => 'Create the database or check the database name.',
                    'techHint' => 'Database does not exist'
                ];
            } else {
                return [
                    'title' => 'Database Error',
                    'message' => 'An unexpected database error occurred.',
                    'userAction' => 'Please check all your settings and try again.',
                    'techHint' => "Error {$code}: " . substr($message, 0, 100)
                ];
            }
    }
}
?>
