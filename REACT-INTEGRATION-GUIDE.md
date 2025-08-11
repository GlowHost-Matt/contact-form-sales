# 🚀 GlowHost Contact Form - React.js Integration Guide

## 📋 Overview

This guide explains how to integrate the **sophisticated React.js front-end** with the **existing stable PHP backend** for the GlowHost Contact Form system.

### System Architecture
```
contact-form-sales/
├── installer.php          ✅ Existing: Stable PHP installer
├── admin/                  ✅ Existing: PHP admin interface
├── config.php             ✅ Existing: Database configuration
├── api/                    🆕 NEW: PHP API endpoints for React.js
│   ├── submit-form.php     🆕 Form submission endpoint
│   ├── upload-file.php     🆕 File upload handler
│   ├── config.php          🆕 API configuration
│   └── database-migration.php 🆕 Database schema update
├── helpdesk/               🆕 NEW: React.js front-end
│   ├── out/                🆕 Static build output
│   ├── next.config.js      🆕 Next.js configuration
│   ├── package.json        🆕 Dependencies and scripts
│   └── [React.js source]   🆕 Front-end source code
└── uploads/                🆕 NEW: File upload storage
```

---

## 🎯 Integration Benefits

### **Frontend Advantages**
- ✅ **Modern React.js Interface** - Professional, responsive design
- ✅ **Advanced File Handling** - Drag & drop, progress tracking, multi-file support
- ✅ **Auto-save Protection** - LocalStorage persistence, never lose form data
- ✅ **Testing Infrastructure** - Development mode with pre-filled data
- ✅ **Mobile Optimization** - Perfect mobile/tablet experience
- ✅ **Type Safety** - TypeScript prevents runtime errors
- ✅ **Component Architecture** - Reusable, maintainable code

### **Backend Stability**
- ✅ **Preserves Existing System** - No changes to working PHP installer/admin
- ✅ **Database Compatibility** - Extends existing schema safely
- ✅ **Security Maintained** - All existing security measures preserved
- ✅ **Admin Interface** - Existing admin panel continues to work perfectly

---

## 📦 Step 1: Backend Preparation

### 1.1 Verify Existing Installation
Ensure your PHP backend is working:
```bash
# Test that installer and admin work
curl -I https://yourdomain.com/admin/
# Should return 200 OK
```

### 1.2 Create API Directory Structure
```bash
# Create API endpoints directory
mkdir -p api uploads

# Set proper permissions
chmod 755 api uploads
```

### 1.3 Deploy API Files
Copy the following files to your `api/` directory:
- `api/submit-form.php` - Main form submission endpoint
- `api/upload-file.php` - File upload handler
- `api/config.php` - API configuration
- `api/database-migration.php` - Database schema update

### 1.4 Run Database Migration
**⚠️ Important: Backup your database first!**

```bash
# Visit the migration script in your browser
https://yourdomain.com/api/database-migration.php
```

This will:
- Add file attachment support to the database
- Create new tables for file tracking
- Add React.js configuration settings
- Update schema for enhanced features

**After successful migration, delete the migration file for security:**
```bash
rm api/database-migration.php
```

---

## 🎨 Step 2: Frontend Deployment

### 2.1 Prepare React.js Source
Create the `helpdesk/` directory and copy your React.js source files:

```bash
mkdir helpdesk
# Copy your React.js source files to helpdesk/
```

### 2.2 Configure Next.js for Subdirectory Deployment
The provided `next.config.js` is pre-configured for:
- Static export (`output: 'export'`)
- Subdirectory deployment (`basePath: '/helpdesk'`)
- API integration (`NEXT_PUBLIC_API_BASE_URL: '../api'`)
- Production optimization

### 2.3 Install Dependencies and Build
```bash
cd helpdesk
npm install
npm run build
```

This creates the `out/` directory with static files.

### 2.4 Deploy Static Files
```bash
# Copy built files to make them accessible
cp -r out/* .

# Or use the deployment script
npm run deploy
```

---

## 🔧 Step 3: API Configuration

### 3.1 Update API Endpoints
Edit `api/config.php` to configure:

```php
// Email notifications
$EMAIL_CONFIG = [
    'enabled' => true,
    'to' => 'your-email@glowhost.com',    // ← Update this
    'from' => 'noreply@glowhost.com',
    'subject_prefix' => 'Contact Form Submission'
];

// CORS settings (production)
define('API_CORS_ORIGIN', 'https://yourdomain.com'); // ← Update for security
```

### 3.2 File Upload Configuration
Ensure the uploads directory exists and is writable:

```bash
mkdir -p uploads
chmod 755 uploads
```

**Security Note**: Add an `.htaccess` file to the uploads directory:
```apache
# uploads/.htaccess
Options -Indexes
<Files "*.php">
    Order Deny,Allow
    Deny from All
</Files>
```

---

## 🧪 Step 4: Testing Integration

### 4.1 Test API Endpoints
```bash
# Test form submission endpoint
curl -X POST https://yourdomain.com/api/submit-form.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","subject":"Test","message":"Test message","department":"Sales Questions"}'

# Should return: {"success":true,"reference_id":"GH-XXXX-YYMMDD",...}
```

### 4.2 Test Frontend
```bash
# Visit the React.js frontend
https://yourdomain.com/helpdesk/

# Should load the modern contact form interface
```

### 4.3 Test End-to-End Flow
1. Fill out the React.js form
2. Submit with file attachments
3. Verify data appears in PHP admin panel
4. Check email notifications work

---

## 🔐 Step 5: Security Hardening

### 5.1 API Security
```php
// In production, update api/config.php:
define('API_CORS_ORIGIN', 'https://yourdomain.com'); // Specific domain only
define('API_RATE_LIMIT', 5); // Stricter rate limiting
```

### 5.2 File Upload Security
```apache
# .htaccess in root directory
RewriteEngine On

# Block direct access to sensitive files
<Files "config.php">
    Order Deny,Allow
    Deny from All
</Files>

<Directory "uploads">
    Options -Indexes
    <Files "*.php">
        Order Deny,Allow
        Deny from All
    </Files>
</Directory>
```

### 5.3 Database Security
- Ensure database users have minimal required permissions
- Enable SSL connections if available
- Regular database backups

---

## 📊 Step 6: Monitoring & Maintenance

### 6.1 Log Monitoring
API requests are logged for debugging:
```bash
# Check PHP error logs
tail -f /path/to/php-error.log | grep "GlowHost API"
```

### 6.2 Performance Monitoring
- Monitor `api_requests` table for usage patterns
- Check file upload sizes and storage usage
- Monitor email delivery success rates

### 6.3 Updates & Maintenance
```bash
# Update React.js frontend
cd helpdesk
npm update
npm run build
npm run deploy

# Update PHP backend (if needed)
# Standard PHP maintenance procedures
```

---

## 🚨 Troubleshooting

### Common Issues

**❌ "API endpoint not found"**
```bash
# Check file permissions
ls -la api/
# Should show readable .php files

# Check .htaccess isn't blocking API
curl -I https://yourdomain.com/api/submit-form.php
```

**❌ "CORS error in browser"**
```php
// Update api/config.php
define('API_CORS_ORIGIN', '*'); // For testing only
```

**❌ "Database connection failed"**
```bash
# Verify config.php is accessible from api/
ls -la config.php
# Check database credentials haven't changed
```

**❌ "File uploads failing"**
```bash
# Check uploads directory permissions
ls -la uploads/
chmod 755 uploads
```

**❌ "React.js form not loading"**
```bash
# Check Next.js build
cd helpdesk
npm run build
# Look for build errors

# Check static files deployed
ls -la helpdesk/out/
```

---

## 🎯 Rollback Plan

If integration causes issues, you can safely rollback:

### Quick Rollback
```bash
# 1. Remove API directory
rm -rf api/

# 2. Remove React.js frontend
rm -rf helpdesk/

# 3. Remove uploads directory
rm -rf uploads/

# 4. Restore database from backup (if needed)
mysql yourdatabase < backup.sql
```

**The original PHP installer and admin interface will continue working perfectly.**

---

## 🎊 Success Verification

After successful integration, you should have:

✅ **Original PHP System** - Installer and admin working as before
✅ **Modern React.js Frontend** - Available at `/helpdesk/`
✅ **Seamless Integration** - Forms submitted from React.js appear in PHP admin
✅ **File Upload Support** - Drag & drop file attachments working
✅ **Email Notifications** - Submissions trigger email alerts
✅ **Mobile Responsive** - Perfect experience on all devices
✅ **Auto-save Protection** - Users never lose form data
✅ **Admin Management** - Full control through existing admin panel

---

## 📞 Support

### Getting Help
- **Documentation**: This guide covers 95% of integration scenarios
- **PHP Backend Issues**: Use existing GlowHost support channels
- **React.js Frontend Issues**: Check browser console for errors
- **API Integration Issues**: Check server error logs

### Emergency Contacts
- **GlowHost Support**: 1 (888) 293-HOST (24/7/365)
- **Technical Issues**: Log files and browser console provide key debugging info

---

## 🏆 Conclusion

This integration gives you the **best of both worlds**:
- **Stable, proven PHP backend** with automatic database creation
- **Modern, sophisticated React.js frontend** with advanced features
- **Safe, reversible integration** that preserves existing functionality
- **Enterprise-grade user experience** that competitors can't match

The result is a **professional, scalable contact form system** that provides exceptional user experience while maintaining the reliability and security of your existing PHP infrastructure.

**🎉 Congratulations on deploying a cutting-edge contact form system!**
