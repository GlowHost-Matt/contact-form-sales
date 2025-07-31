/**
 * DATABASE CONFIGURATION
 *
 * MySQL connection settings, error handling, and field mappings for the contact form.
 * Configure database credentials and customize error messages displayed to users.
 */

// ============================================================================
// DATABASE CONNECTION SETTINGS
// ============================================================================

/**
 * MySQL connection configuration
 * host: Database server address (localhost for cPanel)
 * port: MySQL port (usually 3306)
 * database: Your database name
 * charset: Character encoding (utf8mb4 for full emoji support)
 */
export const DATABASE_CONFIG = {
  connection: {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '3306'),
    database: process.env.DB_NAME || 'glowhost_contacts',
    username: process.env.DB_USER || 'glowhost_user',
    password: process.env.DB_PASSWORD || '',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
  },

  /**
   * Connection pool settings for better performance
   * connectionLimit: Maximum concurrent connections
   * acquireTimeout: How long to wait for connection (ms)
   * timeout: Query timeout (ms)
   */
  pool: {
    connectionLimit: 10,
    acquireTimeout: 60000,
    timeout: 60000,
    reconnect: true,
  },

  /**
   * SSL configuration for secure connections
   * Enable for production hosting environments
   */
  ssl: {
    enabled: process.env.DB_SSL === 'true',
    rejectUnauthorized: process.env.NODE_ENV === 'production',
  },
} as const;

// ============================================================================
// DATABASE SCHEMA MAPPING
// ============================================================================

/**
 * Form field to database column mapping
 * Maps our form fields to your existing database structure
 */
export const FIELD_MAPPING = {
  // Contact form fields → Database columns
  fullName: {
    firstName: 'first_name',     // Split full name into first name
    lastName: 'last_name'       // Split full name into last name
  },
  email: 'email_address',
  phone: 'phone_number',
  domainName: 'domain_name',
  subject: 'inquiry_subject',
  message: 'inquiry_message',
  department: 'department',

  // System fields
  referenceId: 'reference_id',
  submissionDate: 'created_at',
  ipAddress: 'ip_address',
  userAgent: 'user_agent',
  browserName: 'browser_name',
  operatingSystem: 'operating_system',
} as const;

/**
 * Database table configuration
 * table: Primary table name for contact submissions
 * fields: Required and optional database fields
 */
export const TABLE_CONFIG = {
  main: {
    name: 'contact_submissions',
    primaryKey: 'id',
    required: ['first_name', 'last_name', 'email_address', 'inquiry_subject', 'inquiry_message'],
    optional: ['phone_number', 'domain_name', 'department', 'reference_id', 'ip_address'],
    timestamps: {
      created: 'created_at',
      updated: 'updated_at',
    },
  },

  attachments: {
    name: 'contact_attachments',
    primaryKey: 'id',
    foreignKey: 'submission_id',
    fields: ['filename', 'original_name', 'file_size', 'mime_type', 'description'],
  },
} as const;

// ============================================================================
// USER-FRIENDLY ERROR MESSAGES
// ============================================================================

/**
 * Human-readable error messages for common database issues
 * These messages are shown to users when database operations fail
 */
export const DATABASE_ERRORS = {
  // Connection errors
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

  // Authentication errors
  ACCESS_DENIED: {
    title: 'Database Access Error',
    message: 'Unable to access the database due to authentication issues.',
    userAction: 'Please contact support with reference to this error.',
    techHint: 'Check database username, password, and user permissions',
  },

  // Database/table errors
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

  // Data validation errors
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

  // General database errors
  UNKNOWN_ERROR: {
    title: 'Unexpected Error',
    message: 'An unexpected error occurred while saving your submission.',
    userAction: 'Please try again. If the problem persists, contact support.',
    techHint: 'Unhandled database error - check error logs for details',
  },

  DISK_FULL: {
    title: 'System Storage Full',
    message: 'The system is temporarily unable to accept new submissions.',
    userAction: 'Please try again in a few minutes or contact support.',
    techHint: 'Database disk space is full - immediate attention required',
  },
} as const;

// ============================================================================
// DATABASE UTILITY FUNCTIONS
// ============================================================================

/**
 * Map MySQL error codes to user-friendly messages
 */
export const ERROR_CODE_MAPPING = {
  // Connection errors
  'ECONNREFUSED': 'CONNECTION_FAILED',
  'ETIMEDOUT': 'CONNECTION_TIMEOUT',
  'ENOTFOUND': 'CONNECTION_FAILED',

  // Authentication
  'ER_ACCESS_DENIED_ERROR': 'ACCESS_DENIED',
  'ER_BAD_DB_ERROR': 'DATABASE_NOT_FOUND',

  // Table/schema issues
  'ER_NO_SUCH_TABLE': 'TABLE_NOT_FOUND',
  'ER_TABLE_EXISTS_ERROR': 'TABLE_NOT_FOUND',

  // Data issues
  'ER_DUP_ENTRY': 'DUPLICATE_ENTRY',
  'ER_DATA_TOO_LONG': 'DATA_TOO_LONG',
  'ER_BAD_NULL_ERROR': 'INVALID_DATA',
  'ER_NO_DEFAULT_FOR_FIELD': 'INVALID_DATA',

  // System issues
  'ER_DISK_FULL': 'DISK_FULL',
} as const;

/**
 * Split full name into first and last name components
 * Handles various name formats gracefully
 */
export function splitFullName(fullName: string): { firstName: string; lastName: string } {
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
export function getDatabaseErrorMessage(error: any): typeof DATABASE_ERRORS[keyof typeof DATABASE_ERRORS] {
  // Extract error code from various error formats
  const errorCode = error.code || error.errno || error.sqlState || 'UNKNOWN';

  // Map to user-friendly message
  const messageKey = ERROR_CODE_MAPPING[errorCode as keyof typeof ERROR_CODE_MAPPING] || 'UNKNOWN_ERROR';

  return DATABASE_ERRORS[messageKey as keyof typeof DATABASE_ERRORS];
}

/**
 * Validate database connection configuration
 */
export function validateDatabaseConfig(): { valid: boolean; errors: string[] } {
  const errors: string[] = [];
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
export function getConnectionDebugString(): string {
  const config = DATABASE_CONFIG.connection;
  const maskedPassword = config.password ? '***' : '[EMPTY]';

  return `mysql://${config.username}:${maskedPassword}@${config.host}:${config.port}/${config.database}`;
}

/**
 * Map form data to database fields using FIELD_MAPPING
 */
export function mapFormToDatabase(formData: any): Record<string, any> {
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

// ============================================================================
// ENVIRONMENT VARIABLES REFERENCE
// ============================================================================

/**
 * Required environment variables for database configuration:
 *
 * DB_HOST=localhost                    # Database server address
 * DB_PORT=3306                        # MySQL port (usually 3306)
 * DB_NAME=glowhost_contacts           # Your database name
 * DB_USER=glowhost_user               # Database username
 * DB_PASSWORD=your_secure_password    # Database password
 * DB_SSL=false                        # Enable SSL connection (true/false)
 *
 * Add these to your .env.local file for local development
 * Set these in your cPanel environment variables for production
 */
