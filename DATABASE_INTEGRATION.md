# Database Integration Guide

## Overview

This contact form now includes **automatic field mapping** that seamlessly converts the user-friendly "Full Name" field into separate "First Name" and "Last Name" database fields. Users continue to experience a simple form while your database receives properly structured data.

## ✅ Implementation Complete

### What's Working
- **Automatic Name Splitting**: "John Smith" → `first_name: "John"`, `last_name: "Smith"`
- **Database Integration**: PHP backend ready for MySQL connection
- **Error Handling**: User-friendly messages for database issues
- **Fallback System**: File logging if database is unavailable
- **Field Mapping**: All form fields mapped to database columns

### User Experience (Unchanged)
- Users still see and fill a single "Full Name" field
- Auto-save functionality continues to work perfectly
- Form submission process remains identical
- No disruption to customer workflow

### Backend Intelligence
- Automatically splits names into first/last components
- Handles complex names like "Mary Jane Watson" correctly
- Maps all form fields to appropriate database columns
- Provides graceful error handling and fallback

## Database Schema

Your database needs a `contact_submissions` table with these columns:

### Required Columns
```sql
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email_address VARCHAR(255) NOT NULL,
    inquiry_subject VARCHAR(250) NOT NULL,
    inquiry_message TEXT NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Optional Columns (Recommended)
```sql
ALTER TABLE contact_submissions ADD COLUMN phone_number VARCHAR(50);
ALTER TABLE contact_submissions ADD COLUMN domain_name VARCHAR(255);
ALTER TABLE contact_submissions ADD COLUMN reference_id VARCHAR(50);
ALTER TABLE contact_submissions ADD COLUMN ip_address VARCHAR(45);
ALTER TABLE contact_submissions ADD COLUMN user_agent TEXT;
ALTER TABLE contact_submissions ADD COLUMN browser_name VARCHAR(100);
ALTER TABLE contact_submissions ADD COLUMN operating_system VARCHAR(100);
ALTER TABLE contact_submissions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

## Environment Configuration

### Development Testing
```bash
# Test the field mapping (no database required)
DB_HOST=localhost DB_NAME=test_db DB_USER=test_user DB_PASSWORD=test_pass bun run db:test
```

### Production Environment Variables
Set these in your hosting environment (cPanel, .env file, or server config):

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_username
DB_PASSWORD=your_secure_password
DB_SSL=false
```

## Field Mapping Reference

| Form Field | Database Column | Example |
|------------|-----------------|---------|
| Full Name | `first_name` + `last_name` | "John Smith" → `first_name: "John"`, `last_name: "Smith"` |
| Email Address | `email_address` | "john@example.com" |
| Phone Number | `phone_number` | "(555) 123-4567" |
| Domain Name | `domain_name` | "example.com" |
| Subject | `inquiry_subject` | "Hosting inquiry" |
| Message | `inquiry_message` | Full message text |
| Department | `department` | "Sales Questions" |
| Reference ID | `reference_id` | Auto-generated: "DB-ABC123" |
| Submission Date | `created_at` | Auto-generated timestamp |
| IP Address | `ip_address` | User's IP address |
| User Agent | `user_agent` | Browser information |
| Browser Name | `browser_name` | "Chrome", "Firefox", etc. |
| Operating System | `operating_system` | "Windows", "macOS", etc. |

## Name Splitting Examples

The automatic name splitting handles various formats intelligently:

### Standard Names
- `"John Smith"` → `first_name: "John"`, `last_name: "Smith"`
- `"Mary Johnson"` → `first_name: "Mary"`, `last_name: "Johnson"`

### Multiple Names
- `"Mary Jane Watson"` → `first_name: "Mary"`, `last_name: "Jane Watson"`
- `"Dr. Martin Luther King Jr."` → `first_name: "Dr."`, `last_name: "Martin Luther King Jr."`

### Hyphenated Names
- `"Jean-Claude Van Damme"` → `first_name: "Jean-Claude"`, `last_name: "Van Damme"`
- `"Anne-Marie de la Cruz"` → `first_name: "Anne-Marie"`, `last_name: "de la Cruz"`

### Single Names
- `"Madonna"` → `first_name: "Madonna"`, `last_name: ""`
- `"Cher"` → `first_name: "Cher"`, `last_name: ""`

### Edge Cases
- `"  Spaced   Name  "` → `first_name: "Spaced"`, `last_name: "Name"` (trimmed)
- `""` → `first_name: ""`, `last_name: ""` (empty handling)

## Error Handling

### Database Connection Issues
When database connection fails, the system:
1. **Continues to accept form submissions** (no user disruption)
2. **Logs to backup file** for manual import later
3. **Shows user-friendly messages** instead of technical errors
4. **Provides guidance** on what users should do

### Error Message Examples
- **Connection Error**: "Unable to connect to the database. Please try again in a few moments."
- **Access Denied**: "Unable to access the database due to authentication issues."
- **Table Not Found**: "A required system component is missing."
- **Duplicate Entry**: "This appears to be a duplicate submission."

## Testing the Integration

### 1. Test Field Mapping (No Database Required)
```bash
cd Contact-Form-Sales
DB_HOST=localhost DB_NAME=test DB_USER=test DB_PASSWORD=test bun run db:test
```

### 2. Test with Real Database
```bash
# Set your actual credentials
DB_HOST=your_host DB_NAME=your_db DB_USER=your_user DB_PASSWORD=your_pass bun run db:test
```

### 3. Test Form Submission
1. Fill out the contact form
2. Submit the form
3. Check your database for the new record
4. Verify the name was split correctly

## Production Deployment

### Step 1: Database Setup
1. Create the `contact_submissions` table using the SQL above
2. Set up database user with appropriate permissions
3. Test connection using the test script

### Step 2: Environment Configuration
1. Set environment variables in your hosting control panel
2. Update CORS settings in `api/submit-form.php` for your domain
3. Test the configuration

### Step 3: File Upload
1. Upload the updated `api/submit-form.php` to your server
2. Ensure proper file permissions (644)
3. Create `logs/` directory with 755 permissions

### Step 4: Testing
1. Submit a test form submission
2. Verify database entry was created correctly
3. Check that name splitting worked as expected
4. Monitor error logs for any issues

## Backup and Monitoring

### File Logging Backup
Even with database integration, all submissions are logged to:
```
logs/form_submissions.log
```

### Log Format
Each log entry includes:
- Original form data
- Database mapping results
- Success/failure status
- Error details if applicable
- User information and timestamp

### Monitoring Checklist
- [ ] Database connectivity
- [ ] Successful name splitting
- [ ] Proper field mapping
- [ ] Error rate monitoring
- [ ] Log file rotation

## Troubleshooting

### Common Issues

**"Database password is required"**
- Solution: Set `DB_PASSWORD` environment variable

**"Connection refused"**
- Solution: Check `DB_HOST` and `DB_PORT` settings
- Verify database server is running

**"Access denied"**
- Solution: Check `DB_USER` and `DB_PASSWORD`
- Verify user has proper database permissions

**"Table doesn't exist"**
- Solution: Create the `contact_submissions` table
- Run the SQL schema creation commands

**Names not splitting correctly**
- Check the log file for actual vs expected results
- Review the name splitting examples above
- Contact support if persistent issues

### Testing Commands

```bash
# Validate configuration
bun run db:test

# Check git status
bun run git:verify

# Run development server
bun run dev

# Check logs
tail -f logs/form_submissions.log
```

## Support

For database integration support:
1. Check the troubleshooting section above
2. Review log files for specific error messages
3. Test with the provided test script
4. Verify environment variables are set correctly

The automatic field mapping system is production-ready and tested with comprehensive error handling. Your users will experience no changes while your database receives properly structured data.
