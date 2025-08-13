<?php
/**
 * GlowHost Contact Form - React.js API Endpoint
 * Handles form submissions from the Next.js front-end
 *
 * Expected by: /helpdesk/ React.js front-end
 * Method: POST
 * Content-Type: application/json
 */

// CORS headers for React.js front-end
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
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

// Include database configuration
require_once '../config.php';

try {
    // Get JSON input from React.js front-end
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Validate required fields (matching React.js expectations)
    $required_fields = ['name', 'email', 'subject', 'message', 'department'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Extract form data
    $department = trim($data['department']);
    $name = trim($data['name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $domain_name = trim($data['domainName'] ?? '');
    $subject = trim($data['subject']);
    $message = trim($data['message']);

    // Extract user agent data (from React.js front-end)
    $user_agent_data = $data['userAgentData'] ?? [];
    $user_agent = $user_agent_data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipv4_address = $user_agent_data['ipv4Address'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $browser_name = $user_agent_data['browserName'] ?? '';
    $operating_system = $user_agent_data['operatingSystem'] ?? '';

    // Generate unique reference ID (matching React.js expectations)
    $reference_id = 'GH-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('ymd');

    // Split name into first and last name
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

    // Prepare database insertion
    $stmt = $pdo->prepare("
        INSERT INTO contact_submissions (
            first_name, last_name, email_address, phone_number, domain_name,
            inquiry_subject, inquiry_message, department, reference_id,
            ip_address, user_agent, browser_name, operating_system,
            created_at, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'new'
        )
    ");

    $success = $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone,
        $domain_name,
        $subject,
        $message,
        $department,
        $reference_id,
        $ipv4_address,
        $user_agent,
        $browser_name,
        $operating_system
    ]);

    if (!$success) {
        throw new Exception('Failed to save form submission to database');
    }

    // Handle file uploads if present
    $uploaded_files = $data['uploadedFiles'] ?? [];
    $file_descriptions = $data['fileDescriptions'] ?? [];

    // For now, just log file information (file handling can be added later)
    if (!empty($uploaded_files)) {
        error_log("Form submission {$reference_id} included " . count($uploaded_files) . " files");
    }

    // Send email notification (optional - can be configured)
    $send_notifications = true; // Could be pulled from settings table

    if ($send_notifications) {
        $to = 'contact@glowhost.com'; // Could be pulled from settings
        $email_subject = "New Contact Form Submission - {$reference_id}";
        $email_body = "
New contact form submission received:

Reference ID: {$reference_id}
Name: {$name}
Email: {$email}
Phone: {$phone}
Domain: {$domain_name}
Department: {$department}
Subject: {$subject}

Message:
{$message}

Browser: {$browser_name}
OS: {$operating_system}
IP: {$ipv4_address}
Submitted: " . date('Y-m-d H:i:s') . "
        ";

        $headers = "From: noreply@glowhost.com\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Send email (suppress errors to prevent API failure)
        @mail($to, $email_subject, $email_body, $headers);
    }

    // Return success response (matching React.js expectations)
    echo json_encode([
        'success' => true,
        'reference_id' => $reference_id,
        'message' => 'Form submitted successfully',
        'submission_id' => $pdo->lastInsertId(),
        'timestamp' => date('c')
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log("Contact form API error: " . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>