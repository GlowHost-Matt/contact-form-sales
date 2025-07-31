<?php
/**
 * Contact Form Handler with Database Integration
 * Version: 360.1 - Database Field Mapping Implementation
 *
 * This file handles form submissions with automatic field mapping
 * Integrates with database.config.ts for name splitting and field mapping
 *
 * FEATURES:
 * - Automatic "Full Name" → "First Name" + "Last Name" splitting
 * - MySQL database integration with error handling
 * - User-friendly error messages for common database issues
 * - Rate limiting and security validation
 * - File logging as backup to database storage
 */

// Database configuration (matches database.config.ts)
$database_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => intval($_ENV['DB_PORT'] ?? '3306'),
    'database' => $_ENV['DB_NAME'] ?? 'glowhost_contacts',
    'username' => $_ENV['DB_USER'] ?? 'glowhost_user',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4'
];

// Field mapping configuration (matches FIELD_MAPPING in database.config.ts)
$field_mapping = [
    'email' => 'email_address',
    'phone' => 'phone_number',
    'domainName' => 'domain_name',
    'subject' => 'inquiry_subject',
    'message' => 'inquiry_message',
    'department' => 'department',
    'referenceId' => 'reference_id',
    'submissionDate' => 'created_at',
    'ipAddress' => 'ip_address',
    'userAgent' => 'user_agent',
    'browserName' => 'browser_name',
    'operatingSystem' => 'operating_system'
];

// Table configuration
$table_config = [
    'main' => [
        'name' => 'contact_submissions',
        'required' => ['first_name', 'last_name', 'email_address', 'inquiry_subject', 'inquiry_message']
    ]
];

// User-friendly error messages (matches DATABASE_ERRORS in database.config.ts)
$database_errors = [
    'CONNECTION_FAILED' => [
        'title' => 'Connection Error',
        'message' => 'Unable to connect to the database. Please try again in a few moments.',
        'userAction' => 'Please wait a moment and try submitting your form again.'
    ],
    'CONNECTION_TIMEOUT' => [
        'title' => 'Request Timeout',
        'message' => 'The database is taking too long to respond. Your submission may still be processing.',
        'userAction' => 'Please wait 30 seconds before trying again to avoid duplicate submissions.'
    ],
    'ACCESS_DENIED' => [
        'title' => 'Database Access Error',
        'message' => 'Unable to access the database due to authentication issues.',
        'userAction' => 'Please contact support with reference to this error.'
    ],
    'TABLE_NOT_FOUND' => [
        'title' => 'System Configuration Error',
        'message' => 'A required system component is missing.',
        'userAction' => 'Please contact support immediately with this error message.'
    ],
    'DUPLICATE_ENTRY' => [
        'title' => 'Duplicate Submission',
        'message' => 'This appears to be a duplicate submission.',
        'userAction' => 'If you meant to submit again, please modify your message slightly.'
    ],
    'DATA_TOO_LONG' => [
        'title' => 'Message Too Long',
        'message' => 'Your message exceeds the maximum allowed length.',
        'userAction' => 'Please shorten your message and try again.'
    ],
    'INVALID_DATA' => [
        'title' => 'Invalid Information',
        'message' => 'Some of the information provided is not in the correct format.',
        'userAction' => 'Please check your email address and other fields for any errors.'
    ],
    'UNKNOWN_ERROR' => [
        'title' => 'Unexpected Error',
        'message' => 'An unexpected error occurred while saving your submission.',
        'userAction' => 'Please try again. If the problem persists, contact support.'
    ]
];

/**
 * Split full name into first and last name components
 * Matches splitFullName() function from database.config.ts
 */
function splitFullName($fullName) {
    $trimmedName = trim($fullName);

    if (empty($trimmedName)) {
        return ['firstName' => '', 'lastName' => ''];
    }

    $nameParts = preg_split('/\s+/', $trimmedName);

    if (count($nameParts) === 1) {
        return ['firstName' => $nameParts[0], 'lastName' => ''];
    }

    return [
        'firstName' => $nameParts[0],
        'lastName' => implode(' ', array_slice($nameParts, 1))
    ];
}

/**
 * Get user-friendly error message from database error
 * Matches getDatabaseErrorMessage() from database.config.ts
 */
function getDatabaseErrorMessage($error) {
    global $database_errors;

    $errorCode = $error->getCode();
    $errorMessage = $error->getMessage();

    // Map MySQL error codes to user-friendly messages
    if (strpos($errorMessage, 'Access denied') !== false) {
        return $database_errors['ACCESS_DENIED'];
    } elseif (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, 'Can\'t connect') !== false) {
        return $database_errors['CONNECTION_FAILED'];
    } elseif (strpos($errorMessage, 'timeout') !== false) {
        return $database_errors['CONNECTION_TIMEOUT'];
    } elseif (strpos($errorMessage, 'Table') !== false && strpos($errorMessage, 'doesn\'t exist') !== false) {
        return $database_errors['TABLE_NOT_FOUND'];
    } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
        return $database_errors['DUPLICATE_ENTRY'];
    } elseif (strpos($errorMessage, 'Data too long') !== false) {
        return $database_errors['DATA_TOO_LONG'];
    } else {
        return $database_errors['UNKNOWN_ERROR'];
    }
}

/**
 * Map form data to database fields using field mapping
 * Matches mapFormToDatabase() from database.config.ts
 */
function mapFormToDatabase($formData) {
    global $field_mapping;

    // Split the full name
    $nameData = splitFullName($formData['name'] ?? '');

    return [
        'first_name' => $nameData['firstName'],
        'last_name' => $nameData['lastName'],
        $field_mapping['email'] => $formData['email'],
        $field_mapping['phone'] => $formData['phone'] ?? null,
        $field_mapping['domainName'] => $formData['domainName'] ?? null,
        $field_mapping['subject'] => $formData['subject'],
        $field_mapping['message'] => $formData['message'],
        $field_mapping['department'] => $formData['department'],
        $field_mapping['referenceId'] => $formData['referenceId'],
        $field_mapping['ipAddress'] => $_SERVER['REMOTE_ADDR'] ?? null,
        $field_mapping['userAgent'] => $_SERVER['HTTP_USER_AGENT'] ?? null,
        $field_mapping['browserName'] => $formData['userAgentData']['browserName'] ?? null,
        $field_mapping['operatingSystem'] => $formData['userAgentData']['operatingSystem'] ?? null,
        $field_mapping['submissionDate'] => date('Y-m-d H:i:s')
    ];
}

// Enable CORS for the frontend domain
// SECURITY NOTE: * means "allow requests from ANY website" - UNSAFE for production!
//
// DEVELOPMENT OPTIONS:
//   Local testing: header('Access-Control-Allow-Origin: http://localhost:3000');
//   Same.new dev: header('Access-Control-Allow-Origin: https://your-app.preview.same-app.com');
//   Current: * (allows all domains - convenient but insecure)
//
// PRODUCTION (REQUIRED):
//   Single domain: header('Access-Control-Allow-Origin: https://yoursite.com');
//   Subdomain: header('Access-Control-Allow-Origin: https://contact.yoursite.com');
//
header('Access-Control-Allow-Origin: *'); // CHANGE THIS for production deployment!
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Security: Rate limiting (basic implementation)
session_start();
$current_time = time();
$rate_limit_window = 300; // 5 minutes
$max_submissions = 5;

if (!isset($_SESSION['form_submissions'])) {
    $_SESSION['form_submissions'] = [];
}

// Clean old submissions
$_SESSION['form_submissions'] = array_filter(
    $_SESSION['form_submissions'],
    function($timestamp) use ($current_time, $rate_limit_window) {
        return ($current_time - $timestamp) < $rate_limit_window;
    }
);

// Check rate limit
if (count($_SESSION['form_submissions']) >= $max_submissions) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Too many submissions. Please wait before submitting again.'
    ]);
    exit();
}

try {
    // Get and validate input
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['department', 'name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($data[$field]) || !is_string($data[$field])) {
            throw new Exception("Missing or invalid required field: $field");
        }
    }

    // Sanitize and validate data
    $department = filter_var(trim($data['department']), FILTER_SANITIZE_STRING);
    $name = filter_var(trim($data['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    $phone = isset($data['phone']) ? filter_var(trim($data['phone']), FILTER_SANITIZE_STRING) : '';
    $domain_name = isset($data['domainName']) ? filter_var(trim($data['domainName']), FILTER_SANITIZE_STRING) : '';
    $subject = filter_var(trim($data['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($data['message']), FILTER_SANITIZE_STRING);

    if (!$email) {
        throw new Exception('Invalid email address');
    }

    // Validate message length
    if (strlen($message) > 10000) {
        throw new Exception('Message too long (max 10,000 characters)');
    }

    if (strlen($subject) > 250) {
        throw new Exception('Subject too long (max 250 characters)');
    }

    // Generate reference ID
    $reference_id = 'DB-' . strtoupper(uniqid());

    // Prepare form data for mapping
    $form_data_for_mapping = [
        'department' => $department,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'domainName' => $domain_name,
        'subject' => $subject,
        'message' => $message,
        'referenceId' => $reference_id,
        'userAgentData' => $data['userAgentData'] ?? []
    ];

    // Map form data to database fields
    $database_data = mapFormToDatabase($form_data_for_mapping);

    // Attempt database connection and insertion
    $database_success = false;
    $database_error = null;

    try {
        // Create database connection
        $dsn = "mysql:host={$database_config['host']};port={$database_config['port']};dbname={$database_config['database']};charset={$database_config['charset']}";
        $pdo = new PDO($dsn, $database_config['username'], $database_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]);

        // Build INSERT query
        $table_name = $table_config['main']['name'];
        $columns = implode(', ', array_keys($database_data));
        $placeholders = ':' . implode(', :', array_keys($database_data));

        $sql = "INSERT INTO {$table_name} ({$columns}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);

        // Execute the query
        $stmt->execute($database_data);

        $database_success = true;

    } catch (PDOException $e) {
        $database_error = getDatabaseErrorMessage($e);
        error_log("Database Error: " . $e->getMessage());
    }

    // Prepare submission data for file logging (backup)
    $submission_data = [
        'reference_id' => $reference_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'database_saved' => $database_success,
        'database_mapping' => $database_data,
        'original_data' => [
            'department' => $department,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'domain_name' => $domain_name,
            'subject' => $subject,
            'message' => $message
        ],
        'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'user_agent_data' => $data['userAgentData'] ?? [],
        'uploaded_files' => $data['uploadedFiles'] ?? [],
        'file_descriptions' => $data['fileDescriptions'] ?? []
    ];

    // Save to log file (backup to database)
    $log_file = __DIR__ . '/../logs/form_submissions.log';
    $log_dir = dirname($log_file);

    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    // Log the submission
    $log_entry = date('Y-m-d H:i:s') . " - " . json_encode($submission_data) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

    // Optional: Send email notification (configure SMTP settings)
    $send_email = false; // Set to true to enable email notifications

    if ($send_email && $database_success) {
        $to = 'support@yourdomain.com'; // Replace with your email
        $email_subject = "New Contact Form Submission - $reference_id";

        // Extract split name for email
        $nameData = splitFullName($name);
        $first_name = $nameData['firstName'];
        $last_name = $nameData['lastName'];

        $email_body = "
New contact form submission received:

Reference ID: $reference_id
Department: $department
Name: $first_name $last_name
Email: $email
Phone: $phone
Domain: $domain_name
Subject: $subject

Message:
$message

User IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
Timestamp: " . date('Y-m-d H:i:s') . "
Database Status: " . ($database_success ? 'Saved successfully' : 'File backup only') . "
        ";

        $email_headers = "From: noreply@yourdomain.com\r\n";
        $email_headers .= "Reply-To: $email\r\n";
        $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Uncomment to send email
        // mail($to, $email_subject, $email_body, $email_headers);
    }

    // Record successful submission for rate limiting
    $_SESSION['form_submissions'][] = $current_time;

    // Return success response
    if ($database_success) {
        echo json_encode([
            'success' => true,
            'reference_id' => $reference_id,
            'message' => 'Form submitted successfully',
            'database_status' => 'saved'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'reference_id' => $reference_id,
            'message' => 'Form submitted successfully (backup storage)',
            'database_status' => 'backup_only',
            'database_error' => $database_error
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}
?>
