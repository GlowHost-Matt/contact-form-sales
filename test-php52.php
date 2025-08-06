<?php
// PHP 5.2 Test - Minimal version check
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo '<!DOCTYPE html>
<html>
<head>
    <title>PHP Version Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .error-box { background: #fff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1 class="error-title">⚠️ PHP Version Too Old</h1>
        <p><strong>Current:</strong> ' . PHP_VERSION . '</p>
        <p><strong>Required:</strong> 7.4.0 or higher</p>
        <p>Please upgrade your PHP version to continue.</p>
    </div>
</body>
</html>';
    exit;
}

echo 'PHP version is compatible: ' . PHP_VERSION;
?>
