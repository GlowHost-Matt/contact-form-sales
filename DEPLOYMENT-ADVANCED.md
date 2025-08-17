# Contact Form Sales - Advanced Version Deployment Guide

## Overview
This deployment guide is for the **advanced version** of the Contact Form Sales application, pre-configured for `/advanced/` subdirectory deployment. This version includes enhanced department routing and modular architecture.

## Pre-Deployment Checklist
✅ Web server with PHP 7.4+ support  
✅ `/advanced/` directory access on your domain  
✅ Mail function enabled (for form submissions)  
✅ File upload permissions  

## File Structure After Deployment
```
yourdomain.com/advanced/
├── index.html                 # Main contact form
├── 404.html                   # Error page  
├── contact-handler.php        # PHP form processor
├── _next/                     # Next.js assets
│   ├── static/chunks/         # JavaScript bundles
│   ├── static/css/           # Stylesheets
│   └── static/media/         # Font files
└── index.txt                 # Build manifest
```

## Deployment Steps

### 1. Upload Files to /advanced/ Directory
Upload ALL files from this repository to your web server's `/advanced/` directory:

**Via cPanel File Manager:**
1. Navigate to `public_html/advanced/` (create `/advanced/` folder if needed)
2. Upload all files maintaining the directory structure
3. Ensure `_next/` folder and all subdirectories are preserved

**Via FTP:**
```bash
# Upload to /advanced/ directory on your server
ftp://yourdomain.com/public_html/advanced/
```

### 2. Configure PHP Backend
Edit `contact-handler.php` in the `/advanced/` directory:

```php
// Line 48: Update recipient email
$to = 'sales@yourdomain.com';  // ← Change this

// Line 58: Update sender email  
$headers .= "From: noreply@yourdomain.com\r\n";  // ← Change this
```

### 3. Set File Permissions
Ensure proper permissions for the PHP handler:
```bash
chmod 644 contact-handler.php
chmod 755 /advanced/
```

### 4. Test the Installation
1. **Visit the form**: `https://yourdomain.com/advanced/`
2. **Submit a test**: Fill out and submit the contact form
3. **Check email**: Verify emails are received at your configured address
4. **Check logs**: Look for `contact_log.txt` in `/advanced/` directory

## Configuration Details

### Subdirectory Configuration
This build is pre-configured with:
- **Base Path**: `/advanced/`
- **Asset URLs**: All assets reference `/advanced/_next/static/...`
- **Form Action**: Points to `/advanced/contact-handler.php`

### PHP Handler Configuration
The `contact-handler.php` includes:
- ✅ CORS headers for subdirectory deployment
- ✅ JSON input validation
- ✅ Email sanitization
- ✅ Request logging
- ✅ Error handling

### Required Email Updates
**IMPORTANT**: Update these lines in `contact-handler.php`:
```php
// Recipient (who receives the form submissions)
$to = 'sales@yourdomain.com';  // Line 48

// Sender (from address)
$headers .= "From: noreply@yourdomain.com\r\n";  // Line 58
```

## Testing Checklist
- [ ] Form loads at `/advanced/` URL
- [ ] All styles and assets load correctly
- [ ] Form submission works without errors
- [ ] Email notifications are received
- [ ] Form validation works properly
- [ ] Mobile responsiveness functions

## Troubleshooting

### Form doesn't load properly
- Check that `/advanced/` directory exists
- Verify all `_next/` assets were uploaded
- Ensure directory permissions are correct

### Form submission fails
- Check PHP is enabled on `/advanced/` directory
- Verify `contact-handler.php` is uploaded to `/advanced/`
- Check server error logs for PHP errors

### Emails not received
- Verify email addresses in `contact-handler.php`
- Check spam/junk folders
- Test server mail function: `<?php mail('test@domain.com', 'Test', 'Test message'); ?>`

### Asset loading issues
- Confirm `_next/static/` directory structure is intact
- Check browser console for 404 errors on assets
- Verify base path configuration in assets

## Security Notes
- All form inputs are sanitized and validated
- CSRF protection via origin header checking
- Email addresses are properly validated using PHP filters
- Request logging for security monitoring
- No sensitive data stored in client-side code

## Production Considerations
- **CDN**: Consider CDN for `_next/static/` assets
- **Monitoring**: Monitor `contact_log.txt` for suspicious activity
- **Backups**: Regular backups of configuration files
- **SSL**: Ensure HTTPS is enabled for form submissions

## Raw GitHub URLs for Direct Access
After deployment, you can access files directly via:
- Form: `https://yourdomain.com/advanced/`
- Handler: `https://yourdomain.com/advanced/contact-handler.php`
- Assets: `https://yourdomain.com/advanced/_next/static/...`

## Support
This advanced version is optimized for subdirectory deployment with enhanced department routing capabilities. All paths are pre-configured for the `/advanced/` subdirectory structure.