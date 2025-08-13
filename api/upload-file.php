<?php
/**
 * GlowHost Contact Form - File Upload API Endpoint
 * Handles file uploads from the Next.js front-end
 *
 * Expected by: /helpdesk/ React.js front-end
 * Method: POST (multipart/form-data)
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

// Configuration
$upload_dir = '../uploads/';
$max_file_size = 10 * 1024 * 1024; // 10MB (matching React.js front-end)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'pdf', 'txt', 'log', 'zip', 'rar', '7z'];
$allowed_mime_types = [
    'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
    'application/pdf', 'text/plain', 'text/log',
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'
];

try {
    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Check if files were uploaded
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        throw new Exception('No files uploaded');
    }

    $uploaded_files = [];
    $errors = [];

    // Process each uploaded file
    $file_count = count($_FILES['files']['name']);
    for ($i = 0; $i < $file_count; $i++) {
        $file_name = $_FILES['files']['name'][$i];
        $file_tmp = $_FILES['files']['tmp_name'][$i];
        $file_size = $_FILES['files']['size'][$i];
        $file_error = $_FILES['files']['error'][$i];
        $file_type = $_FILES['files']['type'][$i];

        // Skip empty files
        if (empty($file_name)) {
            continue;
        }

        try {
            // Check for upload errors
            if ($file_error !== UPLOAD_ERR_OK) {
                throw new Exception("Upload error for {$file_name}: " . getUploadErrorMessage($file_error));
            }

            // Validate file size
            if ($file_size > $max_file_size) {
                throw new Exception("File {$file_name} exceeds maximum size of " . ($max_file_size / 1024 / 1024) . "MB");
            }

            // Validate file extension
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("File type .{$file_extension} not allowed for {$file_name}");
            }

            // Validate MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_mime = finfo_file($finfo, $file_tmp);
            finfo_close($finfo);

            if (!in_array($detected_mime, $allowed_mime_types)) {
                throw new Exception("MIME type {$detected_mime} not allowed for {$file_name}");
            }

            // Generate secure filename
            $safe_filename = generateSafeFilename($file_name);
            $full_path = $upload_dir . $safe_filename;

            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $full_path)) {
                throw new Exception("Failed to save {$file_name}");
            }

            // Add to successful uploads
            $uploaded_files[] = [
                'original_name' => $file_name,
                'safe_filename' => $safe_filename,
                'size' => $file_size,
                'type' => $detected_mime,
                'path' => $full_path,
                'url' => '/uploads/' . $safe_filename
            ];

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Return response
    if (empty($uploaded_files) && !empty($errors)) {
        // All uploads failed
        throw new Exception('All file uploads failed: ' . implode(', ', $errors));
    }

    // Some or all uploads succeeded
    $response = [
        'success' => true,
        'uploaded_files' => $uploaded_files,
        'upload_count' => count($uploaded_files),
        'timestamp' => date('c')
    ];

    if (!empty($errors)) {
        $response['partial_success'] = true;
        $response['errors'] = $errors;
    }

    echo json_encode($response);

} catch (Exception $e) {
    // Log error for debugging
    error_log("File upload API error: " . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

/**
 * Generate a safe filename to prevent directory traversal and conflicts
 */
function generateSafeFilename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $basename = pathinfo($original_name, PATHINFO_FILENAME);

    // Sanitize basename
    $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    $safe_basename = substr($safe_basename, 0, 50); // Limit length

    // Add timestamp to prevent conflicts
    $timestamp = date('YmdHis');
    $random = substr(bin2hex(random_bytes(4)), 0, 6);

    return $safe_basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * Get human-readable upload error message
 */
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds upload_max_filesize directive';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds MAX_FILE_SIZE directive';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
?>