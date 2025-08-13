<?php
/**
 * GlowHost Contact Form - API Configuration
 * Configuration settings for React.js front-end integration
 */

// Prevent direct access
if (!defined('API_ACCESS')) {
    http_response_code(403);
    die('Direct access not allowed');
}

// API Configuration
define('API_VERSION', '1.0.0');
define('API_CORS_ORIGIN', '*'); // In production, set to specific domain
define('API_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('API_UPLOAD_DIR', dirname(__DIR__) . '/uploads/');

// Allowed file types for uploads (matching React.js front-end)
$ALLOWED_FILE_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',  // Images
    'pdf', 'txt', 'log',                          // Documents
    'zip', 'rar', '7z'                            // Archives
];

$ALLOWED_MIME_TYPES = [
    // Images
    'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
    // Documents
    'application/pdf', 'text/plain',
    // Archives
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'
];

// Email notification settings
$EMAIL_CONFIG = [
    'enabled' => true,
    'to' => 'contact@glowhost.com',
    'from' => 'noreply@glowhost.com',
    'subject_prefix' => 'Contact Form Submission'
];

// Rate limiting (requests per minute per IP)
define('API_RATE_LIMIT', 10);

// Response headers for security
$SECURITY_HEADERS = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
];

/**
 * Apply security headers
 */
function applySecurityHeaders() {
    global $SECURITY_HEADERS;
    foreach ($SECURITY_HEADERS as $header => $value) {
        header($header . ': ' . $value);
    }
}

/**
 * Apply CORS headers for React.js front-end
 */
function applyCorsHeaders() {
    header('Access-Control-Allow-Origin: ' . API_CORS_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); // 24 hours
}

/**
 * Simple rate limiting based on IP address
 */
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = sys_get_temp_dir() . '/glowhost_api_' . md5($ip);
    $current_time = time();
    $window_start = $current_time - 60; // 1 minute window

    // Get existing requests
    $requests = [];
    if (file_exists($cache_file)) {
        $data = file_get_contents($cache_file);
        $requests = $data ? json_decode($data, true) : [];
    }

    // Filter requests within the time window
    $requests = array_filter($requests, function($timestamp) use ($window_start) {
        return $timestamp > $window_start;
    });

    // Check if limit exceeded
    if (count($requests) >= API_RATE_LIMIT) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => 60
        ]);
        exit();
    }

    // Add current request
    $requests[] = $current_time;

    // Save updated requests
    file_put_contents($cache_file, json_encode($requests));
}

/**
 * Log API request for debugging
 */
function logApiRequest($endpoint, $method, $data = null) {
    $log_entry = [
        'timestamp' => date('c'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'endpoint' => $endpoint,
        'method' => $method,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    if ($data) {
        $log_entry['data_summary'] = [
            'size' => strlen(json_encode($data)),
            'fields' => array_keys($data)
        ];
    }

    error_log('GlowHost API: ' . json_encode($log_entry));
}

/**
 * Validate JSON input
 */
function validateJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit();
    }

    return $data;
}

/**
 * Send JSON response with proper headers
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Generate unique reference ID
 */
function generateReferenceId($prefix = 'GH') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('ymd');
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }

    // Remove null bytes and trim whitespace
    $data = str_replace("\0", '', $data);
    $data = trim($data);
    return $data;
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Initialize API request
 */
function initializeApi($endpoint) {
    // Set API access flag
    define('API_ACCESS', true);

    // Apply security headers
    applySecurityHeaders();
    applyCorsHeaders();

    // Check rate limiting
    checkRateLimit();

    // Log request
    logApiRequest($endpoint, $_SERVER['REQUEST_METHOD']);

    // Handle OPTIONS requests (CORS preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
?>