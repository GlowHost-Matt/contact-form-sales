<?php
/**
 * Contact Form Handler for cPanel Deployment
 * Version: 265.1 - Enhanced Security Documentation
 *
 * This file handles form submissions from the contact form
 * Designed for cPanel shared hosting environments
 *
 * SECURITY CONFIGURATION GUIDE:
 * =============================
 *
 * 1. CORS ORIGINS (Line ~35):
 *    - Development: Use * for convenience
 *    - Production: MUST specify exact domains
 *    - Multiple domains: Use validation array (see examples below)
 *
 * 2. EMAIL SETTINGS (Line ~80):
 *    - Update $to with your actual email
 *    - Set $send_email = true to enable notifications
 *    - Configure SMTP if needed
 *
 * 3. FILE PERMISSIONS:
 *    - This file: 644 (-rw-r--r--)
 *    - logs/ directory: 755 (drwxr-xr-x)
 *    - .htaccess: 644 (-rw-r--r--)
 *
 * 4. LOGGING:
 *    - All submissions logged to logs/form_submissions.log
 *    - Rotate logs regularly for performance
 *    - Monitor for suspicious activity
 */

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
// MULTIPLE DOMAINS (Advanced - Use this code):
//   $allowed_origins = [
//       'https://yoursite.com',
//       'https://www.yoursite.com',
//       'https://contact.yoursite.com',
//       'https://staging.yoursite.com'
//   ];
//   $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
//   if (in_array($origin, $allowed_origins)) {
//       header('Access-Control-Allow-Origin: ' . $origin);
//   } else {
//       // Block unauthorized domains
//       http_response_code(403);
//       echo json_encode(['success' => false, 'error' => 'Origin not allowed']);
//       exit();
//   }
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
    $reference_id = 'CPANEL-' . strtoupper(uniqid());

    // Get user agent data
    $user_agent_data = isset($data['userAgentData']) ? $data['userAgentData'] : [];
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    // Prepare submission data
    $submission_data = [
        'reference_id' => $reference_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'department' => $department,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'domain_name' => $domain_name,
        'subject' => $subject,
        'message' => $message,
        'user_ip' => $user_ip,
        'user_agent' => $user_agent,
        'user_agent_data' => $user_agent_data,
        'uploaded_files' => isset($data['uploadedFiles']) ? $data['uploadedFiles'] : [],
        'file_descriptions' => isset($data['fileDescriptions']) ? $data['fileDescriptions'] : []
    ];

    // Save to log file (adjust path as needed for your cPanel setup)
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

    if ($send_email) {
        $to = 'support@yourdomain.com'; // Replace with your email
        $email_subject = "New Contact Form Submission - $reference_id";
        $email_body = "
New contact form submission received:

Reference ID: $reference_id
Department: $department
Name: $name
Email: $email
Phone: $phone
Domain: $domain_name
Subject: $subject

Message:
$message

User IP: $user_ip
Timestamp: " . date('Y-m-d H:i:s') . "
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
    echo json_encode([
        'success' => true,
        'reference_id' => $reference_id,
        'message' => 'Form submitted successfully'
    ]);

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
