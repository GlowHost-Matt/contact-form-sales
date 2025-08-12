# Contact Form Sales - Deployment Guide

## Overview
This is a modern React contact form application with a PHP backend for handling form submissions. The React frontend is built as a static export that can be deployed to any web server, while the PHP backend handles form processing.

## File Structure
```
out/                          # Static React build (upload to web root)
├── index.html               # Main page
├── _next/                   # Next.js assets
├── contact-handler.php      # PHP backend (copy to web root)
└── ...                      # Other static assets
```

## Deployment Steps

### 1. Upload Static Files
Upload all files from the `out/` directory to your web server's document root (usually `public_html/` in cPanel).

### 2. Configure PHP Backend
1. Edit `contact-handler.php` and update:
   - Line 48: Change `sales@yourdomain.com` to your actual email
   - Line 58: Change `noreply@yourdomain.com` to your domain's email

### 3. Set Permissions
Ensure the PHP file has proper permissions:
```bash
chmod 644 contact-handler.php
```

### 4. Test the Installation
1. Visit your website
2. Fill out the contact form
3. Check that emails are received
4. Verify the `contact_log.txt` file is created (optional logging)

## Requirements
- Web server with PHP 7.4+ support
- Mail function enabled (most shared hosting has this)
- Static file hosting capability

## Security Notes
- The PHP script includes CSRF protection via origin checking
- All form data is sanitized and validated
- Email addresses are properly validated
- Submission logging is optional but recommended

## Troubleshooting
- If emails aren't sent, check PHP mail configuration
- Ensure contact-handler.php is in the web root, not in _next/ folder
- Check server error logs for PHP errors
- Verify CORS headers if form submission fails

## Integration with Existing PHP Backend
If you have an existing PHP application, you can:
1. Move the contact form handling into your existing PHP structure
2. Update the form action URL in ContactForm.tsx
3. Implement your own database storage instead of email-only