<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
$required_fields = ['name', 'email', 'message'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit();
    }
}

// Sanitize data
$name = htmlspecialchars(trim($data['name']));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$company = htmlspecialchars(trim($data['company'] ?? ''));
$phone = htmlspecialchars(trim($data['phone'] ?? ''));
$message = htmlspecialchars(trim($data['message']));
$budget = htmlspecialchars(trim($data['budget'] ?? ''));
$timeline = htmlspecialchars(trim($data['timeline'] ?? ''));

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Prepare email content
$to = 'sales@yourdomain.com'; // Change this to your actual email
$subject = 'New Contact Form Submission from ' . $name;

$email_body = "
New contact form submission:

Name: $name
Email: $email
Company: $company
Phone: $phone
Budget: $budget
Timeline: $timeline

Message:
$message

---
Submitted on: " . date('Y-m-d H:i:s') . "
IP Address: " . $_SERVER['REMOTE_ADDR'] . "
";

$headers = [
    'From: noreply@yourdomain.com',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion()
];

// Send email
if (mail($to, $subject, $email_body, implode("\r\n", $headers))) {
    // Log the submission (optional)
    $log_entry = date('Y-m-d H:i:s') . " - Contact form submission from $name ($email)\n";
    file_put_contents('contact_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    error_log('Failed to send contact form email');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>