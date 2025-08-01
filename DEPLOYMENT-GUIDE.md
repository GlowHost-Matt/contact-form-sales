# 🚀 GlowHost Contact Form System - Deployment Guide

This guide covers the complete deployment workflow for the GlowHost Contact Form System, from development to production deployment on shared hosting.

## 📋 Table of Contents

- [Overview](#overview)
- [GitHub Actions Workflow](#github-actions-workflow)
- [Local Development](#local-development)
- [Deployment Packages](#deployment-packages)
- [Shared Hosting Deployment](#shared-hosting-deployment)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)

## 🎯 Overview

The deployment system transforms your Next.js application into a production-ready package optimized for shared hosting environments like cPanel. It includes:

- **Static Export**: Pre-built HTML, CSS, and JS files (no Node.js required)
- **PHP API**: Server-side form processing with email functionality
- **Optimization**: .htaccess rules, compression, and caching
- **Automation**: GitHub Actions for continuous deployment

## 🔄 GitHub Actions Workflow

### Workflow File: `.github/workflows/deploy.yml`

The workflow automatically triggers on:
- **Push to main branch**: Creates deployment packages and releases
- **Pull requests**: Validates builds without deployment
- **Manual trigger**: Allows optional deployment to servers

### Workflow Steps

1. **📥 Checkout & Setup**
   - Checks out repository code
   - Sets up Bun package manager
   - Caches dependencies for faster builds

2. **🏗️ Build Process**
   - Installs dependencies with `bun install`
   - Builds Next.js app with `bun run build`
   - Validates static export output

3. **📦 Package Creation**
   - Copies built files to deployment structure
   - Includes PHP API files and configuration
   - Creates optimized .htaccess file
   - Generates deployment manifests and checksums

4. **🎯 Artifact Generation**
   - **contact-form-deployment.zip**: Complete system package
   - **api-only-deployment.zip**: PHP API files only
   - **installer-only.zip**: Simplified installer for built files

5. **🌐 Optional Server Deployment**
   - FTP deployment to shared hosting
   - SSH deployment with backup creation
   - Configurable via workflow inputs

### Workflow Outputs

After a successful build, you'll find:
- **GitHub Release**: Tagged with build number
- **Downloadable Artifacts**: 30-day retention
- **Build Summary**: Detailed workflow results

## 💻 Local Development

### Prerequisites

- **Node.js**: 18+ (or Bun)
- **Git**: For version control
- **Code Editor**: VS Code recommended

### Setup

```bash
# Clone repository
git clone https://github.com/GlowHost-Matt/contact-form-sales.git
cd contact-form-sales

# Install dependencies
bun install

# Start development server
bun run dev
```

### Available Scripts

```bash
# Development
bun run dev                    # Start development server
bun run build                  # Build Next.js application
bun run build:deployment      # Build and create deployment package

# Testing
bun run deploy:validate       # Validate deployment structure
bun run deploy:package        # Create local deployment package
bun run deploy:local          # Test deployment locally (Python server)

# Code Quality
bun run lint                  # Run TypeScript and ESLint checks
bun run format                # Format code with Biome
```

### Local Testing

1. **Build deployment package:**
   ```bash
   bun run deploy:package
   ```

2. **Test locally:**
   ```bash
   bun run deploy:local
   # Opens local server at http://localhost:8000
   ```

3. **Validate structure:**
   ```bash
   bun run deploy:validate
   ```

## 📦 Deployment Packages

### 🎯 contact-form-deployment.zip (Recommended)

**Complete deployment package** containing:
- Pre-built Next.js static files
- PHP API endpoints
- Configuration files
- Optimized .htaccess
- Deployment documentation

**Usage**: Upload and extract to your public_html directory

### 🐘 api-only-deployment.zip

**API and configuration only** containing:
- PHP API files
- Configuration files
- .htaccess rules

**Usage**: For updating API without rebuilding frontend

### 🛠️ installer-only.zip

**Simplified installer** containing:
- installer-built.php
- Deployment instructions

**Usage**: Upload installer, then download and deploy main package

## 🌐 Shared Hosting Deployment

### Method 1: Complete Package (Recommended)

1. **Download** `contact-form-deployment.zip` from latest release
2. **Login** to cPanel File Manager
3. **Navigate** to `public_html` directory
4. **Upload** and extract the ZIP file
5. **Set permissions**: 755 for directories, 644 for files
6. **Configure** email settings in `api/submit-form.php`

### Method 2: Installer Method

1. **Download** `installer-only.zip`
2. **Upload** installer files to your domain root
3. **Download** `contact-form-deployment.zip` to same directory
4. **Run** `installer-built.php` in your browser
5. **Follow** installation prompts

### Method 3: FTP Upload

```bash
# Using standard FTP client
ftp your-domain.com
# Upload deployment-package contents to public_html/
```

### File Permissions

Set correct permissions after upload:
```bash
# Via SSH (if available)
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 api/*.php

# Via cPanel File Manager
# Select all files → File Permissions → Apply recursively
```

## ⚙️ Configuration

### Email Configuration

Edit `api/submit-form.php`:

```php
// SMTP Configuration (recommended for shared hosting)
$config = [
    'smtp_host' => 'mail.your-domain.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@your-domain.com',
    'smtp_password' => 'your-password',
    'from_email' => 'noreply@your-domain.com',
    'to_email' => 'admin@your-domain.com'
];
```

### Environment-Specific Settings

1. **Production**: Update `config/app.config.ts`
2. **Development**: Use `.env.local` (not deployed)
3. **Shared Hosting**: Modify `config/deployment.config.js`

### Domain Configuration

Update allowed origins in `.htaccess`:
```apache
# Replace * with your domain for security
Header always set Access-Control-Allow-Origin "https://your-domain.com"
```

## 🔧 Troubleshooting

### Common Issues

#### 1. **500 Internal Server Error**

**Cause**: .htaccess compatibility issues

**Solution**:
```bash
# Rename .htaccess temporarily
mv .htaccess .htaccess.backup
# If site works, there's an .htaccess issue
```

#### 2. **Form Submissions Not Working**

**Cause**: PHP configuration or email settings

**Solution**:
```bash
# Check PHP error logs in cPanel
# Verify email configuration in api/submit-form.php
# Test with config-helper.php?action=check
```

#### 3. **Static Files Not Loading**

**Cause**: Incorrect file paths or permissions

**Solution**:
```bash
# Check file permissions (644 for files, 755 for directories)
# Verify _next directory is present and accessible
# Check browser console for 404 errors
```

#### 4. **Email Not Sending**

**Cause**: SMTP configuration or server restrictions

**Solution**:
```php
// Test basic mail() function
<?php
if (mail('test@domain.com', 'Test', 'Test message')) {
    echo 'Mail function works';
} else {
    echo 'Mail function failed';
}
?>
```

### Debug Tools

1. **Configuration Check**:
   ```
   https://your-domain.com/config-helper.php?action=check
   ```

2. **Deployment Manifest**:
   ```
   https://your-domain.com/deployment-manifest.json
   ```

3. **File Integrity**:
   ```
   https://your-domain.com/checksums.json
   ```

### Support Resources

- **cPanel Documentation**: Check your hosting provider's docs
- **PHP Error Logs**: Monitor via cPanel Error Logs
- **GitHub Issues**: Report bugs in repository
- **GlowHost Support**: Contact for hosting-specific issues

## 🚀 Automation Setup

### GitHub Secrets (for auto-deployment)

Add these secrets in your repository settings:

```bash
# FTP Deployment
FTP_SERVER=ftp.your-domain.com
FTP_USERNAME=your-username
FTP_PASSWORD=your-password

# SSH Deployment (alternative)
SSH_HOST=your-domain.com
SSH_USERNAME=your-username
SSH_PRIVATE_KEY=your-private-key
SSH_PORT=22
```

### Workflow Triggers

- **Automatic**: Push to main branch
- **Manual**: Use "Run workflow" button with deployment options
- **Scheduled**: Add cron schedule if desired

### Release Management

The workflow automatically:
- Creates GitHub releases
- Tags with build numbers
- Includes deployment packages
- Generates release notes

## 📊 Monitoring and Maintenance

### Regular Tasks

1. **Monitor builds**: Check GitHub Actions regularly
2. **Update dependencies**: Use Dependabot or manual updates
3. **Review logs**: Check PHP error logs and access logs
4. **Test functionality**: Regularly test contact form
5. **Security updates**: Keep PHP and server software updated

### Performance Optimization

- **Enable CloudFlare**: For CDN and caching
- **Optimize images**: Use WebP format when possible
- **Monitor speed**: Use Google PageSpeed Insights
- **Review .htaccess**: Optimize caching rules

---

## 🎉 Conclusion

This deployment system provides a robust, automated workflow for deploying your Next.js contact form to shared hosting. The combination of static exports and PHP APIs ensures compatibility while maintaining modern development practices.

For additional support or feature requests, please open an issue in the GitHub repository.