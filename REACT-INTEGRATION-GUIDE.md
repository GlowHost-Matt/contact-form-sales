# ⚛️ React.js Integration Guide - GlowHost Contact Form

## 🎯 Overview

This guide covers the integration between the React.js frontend and PHP backend for the GlowHost Contact Form System.

---

## 🏗️ Architecture Overview

### **Frontend Stack:**
- **Framework:** Next.js 14 with React 18
- **Language:** TypeScript
- **Styling:** Tailwind CSS
- **Build Tool:** Bun
- **UI Components:** shadcn/ui components

### **Backend Stack:**
- **Language:** PHP 7.4+
- **Database:** MySQL with PDO
- **API Style:** RESTful JSON API
- **File Handling:** Secure upload with validation

---

## 📁 Project Structure

```
contact-form-sales/
├── helpdesk/                    # React.js Frontend
│   ├── src/
│   │   ├── app/
│   │   │   ├── page.tsx         # Main contact form
│   │   │   └── layout.tsx       # App layout
│   │   ├── components/
│   │   │   ├── ui/              # Reusable UI components
│   │   │   └── layout/          # Layout components
│   │   ├── types/               # TypeScript definitions
│   │   ├── hooks/               # Custom React hooks
│   │   └── lib/                 # Utilities
│   ├── package.json
│   └── next.config.js
├── api/                         # PHP Backend
│   ├── submit-form.php          # Form submission endpoint
│   ├── upload-file.php          # File upload endpoint
│   ├── config.php               # API configuration
│   └── database-migration.php   # DB schema updates
├── admin/                       # Admin Interface
│   ├── login.php
│   └── index.php
├── config.php                   # Main configuration
├── install.php                  # Installation wizard
├── start_fixed.php              # Environment checker
└── detect-fixed.php             # Requirements gateway
```

---

## 🔌 API Integration

### **Endpoint: Form Submission**

**URL:** `/api/submit-form.php`
**Method:** POST
**Content-Type:** application/json

**Request Format:**
```typescript
interface FormSubmission {
  name: string;
  email: string;
  phone?: string;
  domainName?: string;
  subject: string;
  message: string;
  department: string;
  userAgentData?: {
    userAgent: string;
    ipv4Address: string;
    browserName: string;
    operatingSystem: string;
  };
  uploadedFiles?: FileUpload[];
  fileDescriptions?: string[];
}
```

**Response Format:**
```typescript
interface ApiResponse {
  success: boolean;
  reference_id?: string;
  message: string;
  submission_id?: number;
  timestamp: string;
  error?: string;
}
```

### **Endpoint: File Upload**

**URL:** `/api/upload-file.php`
**Method:** POST
**Content-Type:** multipart/form-data

**Request:** FormData with 'files' field (multiple files)

**Response:**
```typescript
interface UploadResponse {
  success: boolean;
  uploaded_files: {
    original_name: string;
    safe_filename: string;
    size: number;
    type: string;
    url: string;
  }[];
  upload_count: number;
  timestamp: string;
  errors?: string[];
}
```

---

## 🔧 Configuration

### **Frontend Configuration**

**File:** `helpdesk/next.config.js`
```javascript
module.exports = {
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: '/api/:path*'
      }
    ];
  },
  env: {
    API_BASE_URL: process.env.API_BASE_URL || '',
  }
};
```

### **Backend Configuration**

**File:** `config.php`
```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'glowhost_contact');
define('DB_USER', 'username');
define('DB_PASS', 'password');

// System Configuration
define('SITE_URL', 'https://your-domain.com');
define('ADMIN_URL', 'https://your-domain.com/admin');
define('SYSTEM_VERSION', '4.0');
```

**File:** `api/config.php`
```php
// API Configuration
define('API_VERSION', '1.0.0');
define('API_CORS_ORIGIN', '*'); // Set to specific domain in production
define('API_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('API_RATE_LIMIT', 10); // Requests per minute per IP
```

---

## 🗄️ Database Schema

### **Primary Table: contact_submissions**

```sql
CREATE TABLE contact_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email_address VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NULL,
    domain_name VARCHAR(100) NULL,
    inquiry_subject VARCHAR(250) NOT NULL,
    inquiry_message TEXT NOT NULL,
    department VARCHAR(50) NOT NULL,
    reference_id VARCHAR(20) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    browser_name VARCHAR(100) NULL,
    operating_system VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'responded', 'archived') DEFAULT 'new',
    admin_notes TEXT NULL,
    
    -- React.js Integration Fields
    file_attachments JSON NULL,
    ipv4_address VARCHAR(45) NULL,
    session_data JSON NULL,
    form_version VARCHAR(20) DEFAULT 'react-1.0'
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **File Attachments Table:**

```sql
CREATE TABLE file_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    safe_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_description TEXT NULL,
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('uploaded', 'processed', 'deleted') DEFAULT 'uploaded',
    FOREIGN KEY (submission_id) REFERENCES contact_submissions(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 🔒 Security Features

### **Frontend Security:**

1. **Input Validation:**
   - TypeScript type checking
   - Zod schema validation
   - Client-side sanitization

2. **CSRF Protection:**
   - SameSite cookies
   - Origin validation
   - Secure headers

### **Backend Security:**

1. **Input Sanitization:**
   ```php
   function sanitizeInput($data) {
       if (is_array($data)) {
           return array_map('sanitizeInput', $data);
       }
       return trim(str_replace("\0", '', $data));
   }
   ```

2. **Rate Limiting:**
   ```php
   function checkRateLimit() {
       // IP-based rate limiting
       // 10 requests per minute per IP
   }
   ```

3. **File Upload Security:**
   - MIME type validation
   - File extension whitelist
   - Size limitations
   - Secure filename generation

---

## 🚀 Deployment Guide

### **Step 1: Environment Setup**

1. **Install Dependencies:**
   ```bash
   cd helpdesk
   bun install
   ```

2. **Build Frontend:**
   ```bash
   bun run build
   ```

3. **Run Database Migration:**
   - Access `/api/database-migration.php`
   - Follow migration wizard

### **Step 2: Configuration**

1. **Update config.php with production settings**
2. **Set proper CORS origins in api/config.php**
3. **Configure email settings**
4. **Set up file upload directory permissions**

### **Step 3: Testing**

1. **Run Pre-Deployment Tests:**
   ```bash
   bun run pre-human-test
   ```

2. **Verify API Endpoints:**
   - Test form submission
   - Test file upload
   - Verify email notifications

### **Step 4: Production Setup**

1. **SSL Certificate:** Ensure HTTPS is enabled
2. **Database Backup:** Set up regular backups
3. **Monitoring:** Configure error logging
4. **Caching:** Implement appropriate caching

---

## 🧪 Testing

### **Automated Tests:**

```bash
# Run all tests
bun run pre-human-test

# Individual test categories
bun run test              # Unit tests
bun run build-frontend    # Build verification
bun run full-test         # Complete suite
```

### **Manual Testing Checklist:**

- [ ] Form loads without errors
- [ ] All departments accessible
- [ ] Form validation works
- [ ] File upload functions
- [ ] Email notifications sent
- [ ] Admin interface accessible
- [ ] Database records created
- [ ] Responsive design works

---

## 🔧 Troubleshooting

### **Common Issues:**

1. **CORS Errors:**
   ```
   Solution: Check API_CORS_ORIGIN in api/config.php
   ```

2. **Database Connection Failed:**
   ```
   Solution: Verify config.php database settings
   ```

3. **File Upload Fails:**
   ```
   Solution: Check directory permissions and file size limits
   ```

4. **Email Not Sending:**
   ```
   Solution: Verify mail() function and SMTP settings
   ```

### **Debug Mode:**

Enable debug logging in PHP:
```php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

---

## 📚 API Reference

### **Response Codes:**

- **200:** Success
- **400:** Bad Request (validation errors)
- **405:** Method Not Allowed
- **429:** Rate Limit Exceeded
- **500:** Internal Server Error

### **Error Response Format:**

```json
{
  "success": false,
  "error": "Detailed error message",
  "timestamp": "2025-08-13T02:00:00Z"
}
```

---

## 🎯 Performance Optimization

### **Frontend Optimization:**

- Component lazy loading
- Image optimization
- Bundle size optimization
- Caching strategies

### **Backend Optimization:**

- Database query optimization
- Response caching
- File compression
- CDN integration

---

## 🔮 Future Enhancements

### **Planned Features:**

- Real-time form collaboration
- Advanced file preview
- Multi-language support
- Enhanced analytics
- API versioning
- Webhook integrations

---

**🎯 This integration provides a robust, scalable foundation for the GlowHost Contact Form System with modern React.js frontend and secure PHP backend.**