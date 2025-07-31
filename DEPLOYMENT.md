# 🚀 One-Click Deployment Guide

## Enterprise Single-File Installer

Deploy the complete GlowHost Contact Form System with just **one file upload**. No more FTP complexity or file permission headaches!

## 📥 Quick Deployment (Recommended)

### Step 1: Download the Installer
Download the single installer file: [`installer.php`](installer.php)

### Step 2: Upload to Your Server
Upload `installer.php` to your web server root directory (or desired subdirectory)

### Step 3: Run the Installer
Visit `yoursite.com/installer.php` in your browser and click **"Install Contact Form System"**

### Step 4: Follow Setup Wizard
After automatic installation, you'll be redirected to the 6-step setup wizard:
1. **Welcome & Requirements** - System compatibility check
2. **Database Connection** - Test your MySQL credentials
3. **Table Creation** - Automatic database setup
4. **Admin Account** - Create your secure admin login
5. **Configuration** - Generate security settings
6. **Completion** - Access your contact form and dashboard

## ✨ What the Installer Does

### Automatic Process
- ✅ **Downloads** the latest system from GitHub
- ✅ **Extracts** all files with proper directory structure
- ✅ **Sets** appropriate file permissions automatically
- ✅ **Launches** the installation wizard
- ✅ **Cleans up** temporary files and self-destructs

### System Requirements Check
- ✅ **PHP 7.4+** compatibility verification
- ✅ **cURL extension** for downloading
- ✅ **ZIP extension** for extraction
- ✅ **Write permissions** validation
- ✅ **Memory limit** requirements

### Security Features
- 🛡️ **CSRF protection** during installation
- 🛡️ **Secure file handling** with proper permissions
- 🛡️ **Automatic cleanup** of temporary files
- 🛡️ **Installation locking** to prevent re-installation

## 📁 Alternative Deployment Methods

### Method 1: Single-File Installer (Recommended) ⭐
```bash
# 1. Upload installer.php to your server
# 2. Visit installer.php in browser
# 3. Click "Install"
# 4. ✅ Done!
```

### Method 2: Manual FTP Upload
```bash
# 1. Download all files from GitHub
# 2. Upload 85+ files via FTP
# 3. Set file permissions manually
# 4. Navigate to /install/
# 5. Run installation wizard
```

### Method 3: Git Clone (Advanced)
```bash
git clone https://github.com/GlowHost-Matt/contact-form-sales.git
cd contact-form-sales
# Set permissions and navigate to /install/
```

## 🏆 Deployment Comparison

| Method | Files to Upload | Time Required | Complexity | Error Risk |
|--------|----------------|---------------|------------|------------|
| **Single-File Installer** | **1 file** | **< 2 minutes** | **Easy** | **Low** |
| Manual FTP | 85+ files | 10-15 minutes | Medium | High |
| Git Clone | Command line | 5-10 minutes | Advanced | Medium |

## 🔧 Hosting Environment Support

### ✅ Fully Supported
- **cPanel Hosting** (shared hosting)
- **VPS/Dedicated Servers**
- **Cloud Hosting** (AWS, DigitalOcean, etc.)
- **Managed WordPress Hosting** (with PHP access)

### 📋 Requirements
- **PHP 7.4+** with cURL and ZIP extensions
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Write permissions** in web directory
- **128MB+ memory limit** (typical for most hosts)

## 🛠️ Troubleshooting

### Common Issues

**"cURL extension not found"**
- Contact your hosting provider to enable cURL
- Most hosting providers have this enabled by default

**"Write permissions denied"**
- Ensure the web directory is writable (chmod 755 or 775)
- Some hosts require specific permission settings

**"Download failed"**
- Check your server's internet connectivity
- Verify firewall settings allow outbound connections

**"System already installed"**
- Remove existing installation files if you want to reinstall
- The installer detects existing installations for safety

### Getting Help

1. **Check installer.log** - The installer creates a detailed log file
2. **System Requirements** - Verify all requirements are met
3. **Contact Support** - Reach out with specific error messages

## 📊 Installation Process Details

### What Gets Installed
```
yoursite.com/
├── install/                 # 6-step installation wizard
├── admin/                   # Admin dashboard (foundation)
├── api/                     # PHP backend with database integration
├── config/                  # Configuration files (auto-generated)
├── out/                     # Built contact form (static files)
├── scripts/                 # Utility and testing scripts
├── logs/                    # System logs directory
└── .htaccess               # Security rules
```

### File Permissions Set
- **Configuration files**: 600 (owner read/write only)
- **Directories**: 755 (standard web permissions)
- **PHP files**: 644 (standard web permissions)
- **Log directories**: 755 (writable for logging)

### Security Measures
- ✅ **Sensitive files protected** with .htaccess rules
- ✅ **Environment variables** stored securely
- ✅ **Database credentials** properly protected
- ✅ **Installation files** cleaned up after completion

## 🚀 Post-Installation Steps

### Immediate Actions
1. **Complete the setup wizard** - Configure your database and admin account
2. **Test the contact form** - Submit a test message
3. **Access admin dashboard** - Review your first submission
4. **Update settings** - Customize for your domain and requirements

### Production Recommendations
1. **Enable SSL** - Update settings for HTTPS
2. **Configure email** - Set up notification emails
3. **Backup database** - Schedule regular backups
4. **Update CORS settings** - Set your specific domain in the PHP backend
5. **Remove installer.php** - Delete after successful installation (optional)

## 📈 Success Metrics

**Deployment Time Reduction:**
- **Traditional FTP**: 10-15 minutes + setup
- **Single-File Installer**: < 2 minutes + setup
- **Time Saved**: 80%+ reduction in deployment time

**Error Reduction:**
- **Eliminates file upload errors**
- **Automatic permission setting**
- **Built-in requirement checking**
- **Self-validating installation process**

---

**🎯 Result**: Professional enterprise contact management system deployed in under 2 minutes with a single file upload!

Ready to transform your contact form experience? Upload `installer.php` and click install!
