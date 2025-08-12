# Contact Form Sales - Static Website

## 🚀 Ready-to-Deploy Contact Form

This repository contains a production-ready static website with a modern React contact form and PHP backend. All files are pre-built and ready for immediate deployment to any web server.

## 📁 Files Overview

- **index.html** - Main contact form page
- **contact-handler.php** - PHP backend for form processing
- **_next/** - Optimized JavaScript, CSS and assets
- **404.html** - Custom 404 error page

## 🛠️ Quick Deployment

### Option 1: Direct Download & Upload
1. Download all files from this repository
2. Upload to your web server's root directory
3. Configure the PHP handler (see below)

### Option 2: Raw GitHub URLs
Access files directly via raw.githubusercontent.com:
- **Main Page**: `https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/index.html`
- **PHP Handler**: `https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/contact-handler.php`
- **CSS**: `https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/_next/static/css/b98a90594af6fdae.css`

## ⚙️ Configuration Required

Edit `contact-handler.php` and update these lines:
- **Line 48**: Change `sales@yourdomain.com` to your email
- **Line 58**: Change `noreply@yourdomain.com` to your domain email

```php
// Line 48: Update recipient email
$to = "your-email@yourdomain.com";

// Line 58: Update sender email  
$headers = "From: noreply@yourdomain.com\r\n";
```

## 🔧 Server Requirements

- **PHP 7.4+** with mail() function enabled
- **Static file hosting** capability
- **Web server** (Apache, Nginx, etc.)

## 🎯 Features

- ✅ **Mobile Responsive** - Works on all devices
- ✅ **Fast Loading** - Optimized static assets
- ✅ **Secure** - CSRF protection and input validation
- ✅ **Professional Design** - Modern UI with Tailwind CSS
- ✅ **Email Integration** - Direct email sending via PHP
- ✅ **Error Handling** - Comprehensive validation and logging

## 📧 Form Fields

- Name (required)
- Email (required, validated)
- Phone (optional)
- Message (required)

## 🔒 Security Features

- Origin checking for CSRF protection
- Email validation and sanitization
- Input filtering and validation
- Optional submission logging

## 📝 Testing

1. Upload all files to your web server
2. Visit your domain
3. Fill out the contact form
4. Check your email inbox
5. Verify `contact_log.txt` is created (optional)

## 🆘 Troubleshooting

- **No emails received**: Check PHP mail configuration
- **Form not submitting**: Verify `contact-handler.php` is in root directory
- **403/404 errors**: Check file permissions (644 for PHP files)
- **CORS errors**: Ensure proper server configuration

## 📚 Documentation

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions and integration options.

## 🏢 About

This is a professional contact form solution designed for business websites. Built with modern technologies and optimized for performance and security.

---

**Ready to use** ✨ | **No build required** 🚫🔨 | **Deploy anywhere** 🌐