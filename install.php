<?php
// install.php - The "Face" of the Installer
// Its first and only job is to load the brain.
require_once 'logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GlowHost Modular Installer</title>
    <style>
        :root { --primary: #0d6efd; --success: #198754; --danger: #dc3545; --warning: #ffc107; --background: #f8f9fa; --text: #212529; --card-bg: #ffffff; --input-border: #ced4da; --light-grey: #6c757d; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: var(--background); color: var(--text); margin: 0; padding: 2rem; display: flex; justify-content: center; align-items: flex-start; }
        .container { width: 100%; max-width: 750px; background: var(--card-bg); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background-color: var(--primary); color: white; padding: 1.5rem; }
        .header h1 { margin: 0; font-size: 1.75rem; }
        .content { padding: 2rem; }
        h2 { color: var(--primary); border-bottom: 1px solid #dee2e6; padding-bottom: 0.75rem; margin-top: 0; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-group input { display: block; width: 100%; padding: 0.75rem; font-size: 1rem; border: 1px solid var(--input-border); border-radius: 4px; box-sizing: border-box; }
        .form-group small { color: var(--light-grey); font-size: 0.875rem; margin-top: 0.25rem; display: block; }
        .btn { display: inline-block; font-weight: 600; text-align: center; vertical-align: middle; cursor: pointer; border: 1px solid transparent; padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 0.25rem; text-decoration: none; }
        .btn-primary { color: #fff; background-color: var(--primary); border-color: var(--primary); }
        .pre-flight-checks { list-style: none; padding: 0; margin-bottom: 2rem; }
        .pre-flight-checks li { display: flex; align-items: center; padding: 0.75rem; border: 1px solid #eee; border-radius: 4px; margin-bottom: 0.5rem; }
        .pre-flight-checks .status-icon { font-size: 1.5rem; margin-right: 1rem; }
        .pre-flight-checks .status-ok .status-icon { color: var(--success); }
        .pre-flight-checks .status-fail .status-icon { color: var(--danger); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GlowHost Application Installer</h1>
        </div>
        <div class="content">

            <h2>1. Pre-Flight Checks</h2>
            <ul class="pre-flight-checks">
                <?php foreach ($pre_flight_results as $key => $result): if ($key === 'overall_status') continue; ?>
                    <li class="<?php echo $result['status'] ? 'status-ok' : 'status-fail'; ?>">
                        <span class="status-icon"><?php echo $result['status'] ? '✅' : '❌'; ?></span>
                        <span><?php echo htmlspecialchars($result['message']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($pre_flight_results['overall_status']): ?>
                <h2>2. Database Configuration</h2>
                <p>All checks passed. Please provide a password for your database user.</p>

                <form action="install.php?step=2" method="POST">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?php echo $db_prefix; ?>contact_form" required>
                        <small>The prefix '<?php echo $db_prefix; ?>' was detected from your server environment.</small>
                    </div>

                    <div class="form-group">
                        <label for="db_user">Database User</label>
                        <input type="text" id="db_user" name="db_user" value="<?php echo $db_prefix; ?>user" required>
                         <small>The prefix '<?php echo $db_prefix; ?>' was detected from your server environment.</small>
                    </div>

                     <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" placeholder="Enter a strong password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Database & Continue</button>
                </form>
            <?php else: ?>
                <p style="color: var(--danger); font-weight: bold;">One or more pre-flight checks failed. Please resolve the issues above before proceeding.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>