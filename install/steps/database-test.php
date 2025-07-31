<?php
/**
 * Installation Step 2: Database Connection Test
 * Test MySQL database credentials and connectivity
 */

// Handle form submission
if ($_POST['action'] ?? '' === 'test_connection') {
    header('Content-Type: application/json');

    $db_host = $_POST['db_host'] ?? '';
    $db_port = $_POST['db_port'] ?? '3306';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        $start_time = microtime(true);

        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]);

        // Test the connection
        $pdo->query('SELECT 1');

        $connection_time = round((microtime(true) - $start_time) * 1000, 2);

        // Store connection details in session
        $_SESSION['install_data']['database'] = [
            'host' => $db_host,
            'port' => $db_port,
            'name' => $db_name,
            'user' => $db_user,
            'pass' => $db_pass
        ];

        echo json_encode([
            'success' => true,
            'details' => [
                'host' => $db_host,
                'port' => $db_port,
                'database' => $db_name,
                'time' => $connection_time
            ]
        ]);

    } catch (PDOException $e) {
        $error_message = $e->getMessage();
        $user_friendly_error = getDatabaseErrorMessage($e);

        echo json_encode([
            'success' => false,
            'error' => $error_message,
            'details' => $user_friendly_error
        ]);
    }

    exit;
}

// Get previously saved data
$db_data = $_SESSION['install_data']['database'] ?? [];

function getDatabaseErrorMessage($error) {
    $message = $error->getMessage();

    if (strpos($message, 'Access denied') !== false) {
        return [
            'title' => 'Access Denied',
            'message' => 'The username or password is incorrect.',
            'userAction' => 'Please check your database username and password.'
        ];
    } elseif (strpos($message, 'Connection refused') !== false || strpos($message, 'Can\'t connect') !== false) {
        return [
            'title' => 'Connection Failed',
            'message' => 'Unable to connect to the database server.',
            'userAction' => 'Please check your host and port settings.'
        ];
    } elseif (strpos($message, 'Unknown database') !== false) {
        return [
            'title' => 'Database Not Found',
            'message' => 'The specified database does not exist.',
            'userAction' => 'Please create the database or check the database name.'
        ];
    } else {
        return [
            'title' => 'Connection Error',
            'message' => 'An unexpected database error occurred.',
            'userAction' => 'Please check all your database settings and try again.'
        ];
    }
}
?>

<div class="database-test-content">
    <!-- Database Configuration Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Database Configuration</h3>
            <p class="card-subtitle">Enter your MySQL database connection details</p>
        </div>

        <form id="database-form" class="database-form">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="db_host">Database Host</label>
                    <input type="text"
                           id="db_host"
                           name="db_host"
                           class="form-input"
                           value="<?php echo htmlspecialchars($db_data['host'] ?? 'localhost'); ?>"
                           placeholder="localhost"
                           required>
                    <div class="form-help">Usually 'localhost' for cPanel hosting</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="db_port">Port</label>
                    <input type="number"
                           id="db_port"
                           name="db_port"
                           class="form-input"
                           value="<?php echo htmlspecialchars($db_data['port'] ?? '3306'); ?>"
                           placeholder="3306"
                           min="1"
                           max="65535"
                           required>
                    <div class="form-help">Standard MySQL port is 3306</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="db_name">Database Name</label>
                <input type="text"
                       id="db_name"
                       name="db_name"
                       class="form-input"
                       value="<?php echo htmlspecialchars($db_data['name'] ?? ''); ?>"
                       placeholder="your_database_name"
                       required>
                <div class="form-help">The name of your MySQL database</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="db_user">Database Username</label>
                <input type="text"
                       id="db_user"
                       name="db_user"
                       class="form-input"
                       value="<?php echo htmlspecialchars($db_data['user'] ?? ''); ?>"
                       placeholder="database_username"
                       required>
                <div class="form-help">MySQL user with access to the database</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="db_pass">Database Password</label>
                <input type="password"
                       id="db_pass"
                       name="db_pass"
                       class="form-input"
                       placeholder="Enter password"
                       required>
                <div class="form-help">Password for the database user</div>
            </div>

            <div class="form-actions">
                <button type="button"
                        class="btn btn-secondary"
                        onclick="testDatabaseConnection()">
                    <i class="fas fa-plug"></i>
                    Test Connection
                </button>
            </div>
        </form>
    </div>

    <!-- Connection Test Results -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Connection Test</h3>
            <p class="card-subtitle">Real-time database connectivity testing</p>
        </div>

        <div class="connection-test" id="connection-test-results">
            <div class="test-result testing">
                <i class="fas fa-info-circle"></i>
                <span>Fill in your database details above and click "Test Connection"</span>
            </div>
        </div>
    </div>

    <!-- Common Database Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Common Hosting Settings</h3>
            <p class="card-subtitle">Typical database configurations for popular hosts</p>
        </div>

        <div class="hosting-examples">
            <div class="hosting-example">
                <h4><i class="fas fa-server"></i> cPanel Hosting</h4>
                <ul>
                    <li><strong>Host:</strong> localhost</li>
                    <li><strong>Port:</strong> 3306</li>
                    <li><strong>Database:</strong> username_dbname</li>
                    <li><strong>User:</strong> username_dbuser</li>
                </ul>
            </div>

            <div class="hosting-example">
                <h4><i class="fas fa-cloud"></i> Shared Hosting</h4>
                <ul>
                    <li><strong>Host:</strong> localhost or provided hostname</li>
                    <li><strong>Port:</strong> 3306 (usually default)</li>
                    <li><strong>Database:</strong> Check your hosting panel</li>
                    <li><strong>User:</strong> Usually different from cPanel user</li>
                </ul>
            </div>

            <div class="hosting-example">
                <h4><i class="fas fa-database"></i> Remote MySQL</h4>
                <ul>
                    <li><strong>Host:</strong> IP address or hostname</li>
                    <li><strong>Port:</strong> 3306 or custom port</li>
                    <li><strong>Database:</strong> Database name</li>
                    <li><strong>User:</strong> Remote access user</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.form-actions {
    margin-top: 24px;
    text-align: center;
}

.hosting-examples {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.hosting-example {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
}

.hosting-example h4 {
    margin: 0 0 12px 0;
    color: #2d3748;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.hosting-example ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.hosting-example li {
    padding: 4px 0;
    font-size: 14px;
    color: #4a5568;
}

.test-details {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.test-details p {
    margin: 4px 0;
    font-size: 14px;
    opacity: 0.9;
}

.error-details {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.error-details p {
    margin: 4px 0;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .hosting-examples {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let connectionTestPassed = false;

async function testDatabaseConnection() {
    const form = document.getElementById('database-form');
    const formData = new FormData(form);

    // Add test action
    formData.append('action', 'test_connection');

    const testContainer = document.getElementById('connection-test-results');

    // Show testing state
    testContainer.innerHTML = `
        <div class="test-result testing">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Testing database connection...</span>
        </div>
    `;

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            connectionTestPassed = true;

            testContainer.innerHTML = `
                <div class="test-result success">
                    <i class="fas fa-check-circle"></i>
                    <span>Database connection successful!</span>
                </div>
                <div class="test-details">
                    <p><strong>Server:</strong> ${result.details.host}:${result.details.port}</p>
                    <p><strong>Database:</strong> ${result.details.database}</p>
                    <p><strong>Connection Time:</strong> ${result.details.time}ms</p>
                </div>
            `;

            // Update next button
            updateNextButton();

        } else {
            connectionTestPassed = false;

            testContainer.innerHTML = `
                <div class="test-result error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Connection failed</span>
                </div>
                <div class="error-details">
                    <p><strong>Error:</strong> ${result.details?.title || 'Unknown error'}</p>
                    <p><strong>Message:</strong> ${result.details?.message || result.error}</p>
                    <p><strong>Action:</strong> ${result.details?.userAction || 'Please check your settings and try again.'}</p>
                </div>
            `;

            // Update next button
            updateNextButton();
        }

    } catch (error) {
        connectionTestPassed = false;

        testContainer.innerHTML = `
            <div class="test-result error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Connection test failed: ${error.message}</span>
            </div>
        `;

        updateNextButton();
    }
}

function updateNextButton() {
    const nextButtonContainer = document.getElementById('next-button-container');
    if (nextButtonContainer) {
        nextButtonContainer.innerHTML = `
            <button type="button" class="btn btn-primary"
                    ${connectionTestPassed ? '' : 'disabled'}
                    onclick="window.Installer.nextStep()">
                <i class="fas fa-arrow-right"></i>
                ${connectionTestPassed ? 'Continue to Database Setup' : 'Test Connection First'}
            </button>
        `;
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Auto-test if all fields are filled
    const form = document.getElementById('database-form');
    const inputs = form.querySelectorAll('input[required]');

    let allFilled = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            allFilled = false;
        }
    });

    if (allFilled) {
        setTimeout(testDatabaseConnection, 500);
    }

    // Enable auto-testing
    window.DatabaseTester?.enableAutoTest();

    // Initial next button state
    updateNextButton();

    // Add enter key handler for testing
    form.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            testDatabaseConnection();
        }
    });
});
</script>
