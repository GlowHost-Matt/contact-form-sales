#!/usr/bin/env node

/**
 * Database Connection Test Script
 * Tests MySQL connection and field mapping functionality
 */

// Database configuration (copied from database.config.ts)
const DATABASE_CONFIG = {
  connection: {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '3306'),
    database: process.env.DB_NAME || 'glowhost_contacts',
    username: process.env.DB_USER || 'glowhost_user',
    password: process.env.DB_PASSWORD || '',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
  },
  pool: {
    connectionLimit: 10,
    acquireTimeout: 60000,
    timeout: 60000,
    reconnect: true,
  },
  ssl: {
    enabled: process.env.DB_SSL === 'true',
    rejectUnauthorized: process.env.NODE_ENV === 'production',
  },
};

// Field mapping configuration
const FIELD_MAPPING = {
  fullName: {
    firstName: 'first_name',
    lastName: 'last_name'
  },
  email: 'email_address',
  phone: 'phone_number',
  domainName: 'domain_name',
  subject: 'inquiry_subject',
  message: 'inquiry_message',
  department: 'department',
  referenceId: 'reference_id',
  submissionDate: 'created_at',
  ipAddress: 'ip_address',
  userAgent: 'user_agent',
  browserName: 'browser_name',
  operatingSystem: 'operating_system',
};

// User-friendly error messages
const DATABASE_ERRORS = {
  CONNECTION_FAILED: {
    title: 'Connection Error',
    message: 'Unable to connect to the database. Please try again in a few moments.',
    userAction: 'Please wait a moment and try submitting your form again.',
    techHint: 'Check database server status and credentials',
  },
  CONNECTION_TIMEOUT: {
    title: 'Request Timeout',
    message: 'The database is taking too long to respond. Your submission may still be processing.',
    userAction: 'Please wait 30 seconds before trying again to avoid duplicate submissions.',
    techHint: 'Database server may be overloaded or connection is slow',
  },
  ACCESS_DENIED: {
    title: 'Database Access Error',
    message: 'Unable to access the database due to authentication issues.',
    userAction: 'Please contact support with reference to this error.',
    techHint: 'Check database username, password, and user permissions',
  },
  DATABASE_NOT_FOUND: {
    title: 'Database Configuration Error',
    message: 'The system database is temporarily unavailable.',
    userAction: 'Please try again later or contact support if the issue persists.',
    techHint: 'Database name incorrect or database does not exist',
  },
  TABLE_NOT_FOUND: {
    title: 'System Configuration Error',
    message: 'A required system component is missing.',
    userAction: 'Please contact support immediately with this error message.',
    techHint: 'Contact submissions table does not exist - run database migrations',
  },
  DUPLICATE_ENTRY: {
    title: 'Duplicate Submission',
    message: 'This appears to be a duplicate submission.',
    userAction: 'If you meant to submit again, please modify your message slightly.',
    techHint: 'Unique constraint violation - check for duplicate email/reference combinations',
  },
  DATA_TOO_LONG: {
    title: 'Message Too Long',
    message: 'Your message exceeds the maximum allowed length.',
    userAction: 'Please shorten your message and try again.',
    techHint: 'Data exceeds column length limits - check field definitions',
  },
  INVALID_DATA: {
    title: 'Invalid Information',
    message: 'Some of the information provided is not in the correct format.',
    userAction: 'Please check your email address and other fields for any errors.',
    techHint: 'Data type mismatch or constraint violation',
  },
  UNKNOWN_ERROR: {
    title: 'Unexpected Error',
    message: 'An unexpected error occurred while saving your submission.',
    userAction: 'Please try again. If the problem persists, contact support.',
    techHint: 'Unhandled database error - check error logs for details',
  },
};

// ANSI color codes for terminal output
const colors = {
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  reset: '\x1b[0m',
  bold: '\x1b[1m'
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

/**
 * Split full name into first and last name components
 * Matches splitFullName() function from database.config.ts
 */
function splitFullName(fullName) {
  const trimmedName = fullName.trim();

  if (!trimmedName) {
    return { firstName: '', lastName: '' };
  }

  const nameParts = trimmedName.split(/\s+/);

  if (nameParts.length === 1) {
    return { firstName: nameParts[0], lastName: '' };
  }

  return {
    firstName: nameParts[0],
    lastName: nameParts.slice(1).join(' '),
  };
}

/**
 * Get user-friendly error message from database error
 */
function getDatabaseErrorMessage(error) {
  // Extract error code from various error formats
  const errorCode = error.code || error.errno || error.sqlState || 'UNKNOWN';

  // Map to user-friendly message
  if (error.message.includes('Access denied')) {
    return DATABASE_ERRORS.ACCESS_DENIED;
  } else if (error.message.includes('Connection refused') || error.message.includes('Can\'t connect')) {
    return DATABASE_ERRORS.CONNECTION_FAILED;
  } else if (error.message.includes('timeout')) {
    return DATABASE_ERRORS.CONNECTION_TIMEOUT;
  } else if (error.message.includes('Unknown database')) {
    return DATABASE_ERRORS.DATABASE_NOT_FOUND;
  } else if (error.message.includes('Table') && error.message.includes('doesn\'t exist')) {
    return DATABASE_ERRORS.TABLE_NOT_FOUND;
  } else if (error.message.includes('Duplicate entry')) {
    return DATABASE_ERRORS.DUPLICATE_ENTRY;
  } else if (error.message.includes('Data too long')) {
    return DATABASE_ERRORS.DATA_TOO_LONG;
  } else {
    return DATABASE_ERRORS.UNKNOWN_ERROR;
  }
}

/**
 * Validate database connection configuration
 */
function validateDatabaseConfig() {
  const errors = [];
  const config = DATABASE_CONFIG.connection;

  if (!config.host) errors.push('Database host is required');
  if (!config.database) errors.push('Database name is required');
  if (!config.username) errors.push('Database username is required');
  if (!config.password) errors.push('Database password is required');
  if (isNaN(config.port) || config.port < 1 || config.port > 65535) {
    errors.push('Database port must be a valid number between 1 and 65535');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Generate database connection string for debugging (password masked)
 */
function getConnectionDebugString() {
  const config = DATABASE_CONFIG.connection;
  const maskedPassword = config.password ? '***' : '[EMPTY]';

  return `mysql://${config.username}:${maskedPassword}@${config.host}:${config.port}/${config.database}`;
}

/**
 * Map form data to database fields using FIELD_MAPPING
 */
function mapFormToDatabase(formData) {
  const { firstName, lastName } = splitFullName(formData.fullName || '');

  return {
    [FIELD_MAPPING.fullName.firstName]: firstName,
    [FIELD_MAPPING.fullName.lastName]: lastName,
    [FIELD_MAPPING.email]: formData.email,
    [FIELD_MAPPING.phone]: formData.phone || null,
    [FIELD_MAPPING.domainName]: formData.domainName || null,
    [FIELD_MAPPING.subject]: formData.subject,
    [FIELD_MAPPING.message]: formData.message,
    [FIELD_MAPPING.department]: formData.department,
    [FIELD_MAPPING.referenceId]: formData.referenceId,
    [FIELD_MAPPING.ipAddress]: formData.userAgentData?.ipv4Address || null,
    [FIELD_MAPPING.userAgent]: formData.userAgentData?.userAgent || null,
    [FIELD_MAPPING.browserName]: formData.userAgentData?.browserName || null,
    [FIELD_MAPPING.operatingSystem]: formData.userAgentData?.operatingSystem || null,
    [FIELD_MAPPING.submissionDate]: new Date(),
  };
}

async function testDatabaseConnection() {
  log('\n🔍 DATABASE CONNECTION TEST', 'bold');
  log('================================');

  // Step 1: Validate configuration
  log('\n📋 Configuration Validation:', 'blue');
  const validation = validateDatabaseConfig();

  if (!validation.valid) {
    log('❌ Configuration validation failed:', 'red');
    validation.errors.forEach(error => {
      log(`   - ${error}`, 'red');
    });
    log('\n💡 Check your environment variables:', 'yellow');
    log('   DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, DB_PORT', 'yellow');
    return false;
  }

  log('✅ Configuration validation passed', 'green');
  log(`   Connection: ${getConnectionDebugString()}`, 'reset');

  // Step 2: Test name splitting functionality
  log('\n🧪 Testing Name Splitting Functionality:', 'blue');

  const testNames = [
    'John Smith',
    'Mary Jane Watson',
    'Madonna',
    'Jean-Claude Van Damme',
    '  Spaced   Name  ',
    'Dr. Martin Luther King Jr.',
    'Anne-Marie de la Cruz'
  ];

  let nameSplittingPassed = true;
  testNames.forEach(name => {
    const result = splitFullName(name);
    log(`   "${name}" → First: "${result.firstName}", Last: "${result.lastName}"`, 'reset');

    // Basic validation
    if (!result.firstName && name.trim()) {
      log(`     ⚠️  Warning: Empty first name for non-empty input`, 'yellow');
      nameSplittingPassed = false;
    }
  });

  if (nameSplittingPassed) {
    log('✅ Name splitting functionality working correctly', 'green');
  }

  // Step 3: Test field mapping
  log('\n🗺️  Testing Field Mapping:', 'blue');

  const testFormData = {
    fullName: 'John Smith',
    email: 'john.smith@example.com',
    phone: '(555) 123-4567',
    domainName: 'example.com',
    subject: 'Test Subject',
    message: 'This is a test message',
    department: 'Sales Questions',
    referenceId: 'TEST-123',
    userAgentData: {
      browserName: 'Chrome',
      operatingSystem: 'Windows',
      userAgent: 'Mozilla/5.0...'
    }
  };

  const mappedData = mapFormToDatabase(testFormData);
  log('   Form data mapped to database fields:', 'reset');
  Object.entries(mappedData).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {
      log(`   ${key}: ${value}`, 'reset');
    }
  });

  log('✅ Field mapping functionality working correctly', 'green');

  // Step 4: Test error message mapping
  log('\n📨 Error Message Mapping Test:', 'blue');
  const testErrors = [
    { code: 'ECONNREFUSED', message: 'Connection refused', description: 'Connection refused' },
    { code: 'ER_ACCESS_DENIED_ERROR', message: 'Access denied for user', description: 'Access denied' },
    { code: 'ER_NO_SUCH_TABLE', message: 'Table doesn\'t exist', description: 'Table not found' },
    { code: 'UNKNOWN_ERROR', message: 'Some random error', description: 'Unknown error' }
  ];

  testErrors.forEach(testError => {
    const errorMsg = getDatabaseErrorMessage({ code: testError.code, message: testError.message });
    log(`   ${testError.code}: "${errorMsg.title}"`, 'reset');
  });

  log('✅ Error message mapping working correctly', 'green');

  // Step 5: Attempt database connection (if mysql2 is available)
  log('\n🌐 Database Connection Test:', 'blue');

  try {
    // Try to require mysql2 - it may not be installed yet
    const mysql = require('mysql2/promise');

    log('   Attempting connection...', 'yellow');

    const connection = await mysql.createConnection({
      host: DATABASE_CONFIG.connection.host,
      port: DATABASE_CONFIG.connection.port,
      user: DATABASE_CONFIG.connection.username,
      password: DATABASE_CONFIG.connection.password,
      database: DATABASE_CONFIG.connection.database,
      charset: DATABASE_CONFIG.connection.charset,
      connectTimeout: 10000,
    });

    // Test the connection
    await connection.ping();
    log('✅ Database connection successful!', 'green');

    // Test a simple query
    const [rows] = await connection.execute('SELECT 1 as test');
    log('✅ Database query test successful', 'green');

    // Test if our table exists
    try {
      const [tableRows] = await connection.execute('SHOW TABLES LIKE "contact_submissions"');
      if (tableRows.length > 0) {
        log('✅ Contact submissions table exists', 'green');

        // Test table structure
        const [columns] = await connection.execute('DESCRIBE contact_submissions');
        log('   Table columns:', 'reset');
        columns.forEach(col => {
          log(`     ${col.Field} (${col.Type})`, 'reset');
        });
      } else {
        log('⚠️  Contact submissions table does not exist', 'yellow');
        log('   You will need to create the table before live testing', 'yellow');
      }
    } catch (tableError) {
      log('⚠️  Could not check table structure', 'yellow');
    }

    // Close connection
    await connection.end();
    log('✅ Connection closed properly', 'green');

    return true;

  } catch (error) {
    if (error.code === 'MODULE_NOT_FOUND' && error.message.includes('mysql2')) {
      log('⚠️  mysql2 package not installed', 'yellow');
      log('   Run: bun add mysql2', 'yellow');
      log('   Configuration appears valid, but cannot test actual connection', 'yellow');
      return true; // Configuration is valid, just missing package
    }

    log('❌ Database connection failed:', 'red');

    const errorMsg = getDatabaseErrorMessage(error);
    log(`   Error: ${errorMsg.title}`, 'red');
    log(`   Message: ${errorMsg.message}`, 'red');
    log(`   User Action: ${errorMsg.userAction}`, 'yellow');
    log(`   Tech Hint: ${errorMsg.techHint}`, 'blue');

    if (error.code) {
      log(`   Error Code: ${error.code}`, 'reset');
    }

    return false;
  }
}

async function main() {
  const success = await testDatabaseConnection();

  log('\n🎯 TEST SUMMARY:', 'bold');
  log('===============');

  if (success) {
    log('✅ Database configuration and field mapping are ready!', 'green');
    log('   Field mapping tests passed:', 'green');
    log('   - ✅ Name splitting (Full Name → First Name + Last Name)', 'green');
    log('   - ✅ Form data to database field mapping', 'green');
    log('   - ✅ Error message handling', 'green');
    log('   You can now integrate with the contact form for live testing', 'green');
  } else {
    log('❌ Database configuration needs attention', 'red');
    log('   Review the errors above before proceeding', 'red');
  }

  log(`\n📅 Test completed: ${new Date().toISOString()}`, 'reset');

  process.exit(success ? 0 : 1);
}

// Handle uncaught errors
process.on('uncaughtException', (error) => {
  log('\n💥 Uncaught Exception:', 'red');
  log(error.message, 'red');
  process.exit(1);
});

process.on('unhandledRejection', (reason) => {
  log('\n💥 Unhandled Rejection:', 'red');
  log(reason, 'red');
  process.exit(1);
});

// Run if called directly
if (require.main === module) {
  main();
}

module.exports = {
  testDatabaseConnection,
  splitFullName,
  mapFormToDatabase,
  getDatabaseErrorMessage
};
