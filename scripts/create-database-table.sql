-- Contact Form Database Table Creation Script
-- Run this in your MySQL database to create the required table

-- Create the main contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    -- Primary key
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Required fields (from form)
    first_name VARCHAR(100) NOT NULL COMMENT 'First name from split full name',
    last_name VARCHAR(100) NOT NULL COMMENT 'Last name from split full name',
    email_address VARCHAR(255) NOT NULL COMMENT 'Customer email address',
    inquiry_subject VARCHAR(250) NOT NULL COMMENT 'Subject of the inquiry',
    inquiry_message TEXT NOT NULL COMMENT 'Main message content',
    department VARCHAR(100) NOT NULL COMMENT 'Department selected (Sales Questions, etc.)',

    -- Optional fields
    phone_number VARCHAR(50) NULL COMMENT 'Optional phone number',
    domain_name VARCHAR(255) NULL COMMENT 'Optional domain name',

    -- System fields
    reference_id VARCHAR(50) NULL COMMENT 'Unique reference ID for tracking',
    ip_address VARCHAR(45) NULL COMMENT 'Customer IP address (IPv4/IPv6)',
    user_agent TEXT NULL COMMENT 'Full browser user agent string',
    browser_name VARCHAR(100) NULL COMMENT 'Parsed browser name',
    operating_system VARCHAR(100) NULL COMMENT 'Parsed operating system',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Submission timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',

    -- Indexes for performance
    INDEX idx_email (email_address),
    INDEX idx_created_at (created_at),
    INDEX idx_reference_id (reference_id),
    INDEX idx_department (department)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact form submissions with automatic name field mapping';

-- Optional: Create attachments table for file uploads
CREATE TABLE IF NOT EXISTS contact_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL COMMENT 'Foreign key to contact_submissions.id',
    filename VARCHAR(255) NOT NULL COMMENT 'Stored filename on server',
    original_name VARCHAR(255) NOT NULL COMMENT 'Original filename from user',
    file_size INT NOT NULL COMMENT 'File size in bytes',
    mime_type VARCHAR(100) NOT NULL COMMENT 'File MIME type',
    description TEXT NULL COMMENT 'Optional file description',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (submission_id) REFERENCES contact_submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_id (submission_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File attachments for contact form submissions';

-- Show the created tables
SHOW TABLES LIKE 'contact_%';

-- Display table structure for verification
DESCRIBE contact_submissions;

-- Optional: Sample data insertion for testing
-- Uncomment the lines below to insert test data

/*
INSERT INTO contact_submissions (
    first_name, last_name, email_address, phone_number, domain_name,
    inquiry_subject, inquiry_message, department, reference_id, ip_address
) VALUES (
    'John', 'Smith', 'john.smith@example.com', '(555) 123-4567', 'example.com',
    'Test Subject', 'This is a test message from the database setup script.',
    'Sales Questions', 'TEST-001', '127.0.0.1'
);

-- Verify the test data was inserted
SELECT * FROM contact_submissions WHERE reference_id = 'TEST-001';
*/

-- Display success message
SELECT 'Database tables created successfully! You can now use the contact form with database integration.' AS message;
