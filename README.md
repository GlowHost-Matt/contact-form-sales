# GlowHost Contact Form System - Enterprise Edition

A complete professional contact management system with one-click installation, automatic field mapping, and admin dashboard. Transform any website into a powerful contact management platform.

## 🚀 Enterprise Features

### Installation & Setup
- **One-click installation wizard** with 6-step guided setup
- **Real-time database testing** with user-friendly error messages
- **Automated table creation** with progress feedback
- **Secure admin account setup** with password strength validation
- **Automatic configuration generation** with security keys

### Contact Form System
- **Auto-save functionality** with real-time indicators
- **Automatic field mapping** - "Full Name" splits into "First Name" + "Last Name"
- **Database integration** with MySQL support and error handling
- **File attachments** with drag-and-drop support
- **Form validation** with character counting
- **Professional GlowHost branding** throughout
- **Responsive design** for all devices

### Enterprise Security
- **CSRF protection** with token validation
- **Session management** with IP tracking and auto-expiration
- **Rate limiting** and brute force protection
- **Password hashing** with secure algorithms
- **File permission management** and .htaccess security rules
- **Installation cleanup** and access controls

### Admin Dashboard Foundation
- **Secure admin authentication** system
- **Database monitoring** and connection health
- **User management** capabilities
- **Configuration management** interface
- **Submission tracking** (ready for dashboard implementation)

## 🚀 One-Click Deployment

### Professional Single-File Installer (Recommended) ⭐
The easiest way to deploy the complete contact management system:

1. **Download** [`installer.php`](installer.php)
2. **Upload** the single file to your web server
3. **Visit** `yoursite.com/installer.php` in your browser
4. **Click** "Install Contact Form System"
5. **Follow** the 6-step setup wizard
6. **Done!** Your enterprise contact system is ready

**🎯 Deployment Time: < 2 minutes | Files to Upload: 1**

### Advanced Deployment Options
- **Manual Installation**: See [DEPLOYMENT.md](DEPLOYMENT.md) for FTP/Git methods
- **Development Setup**: See development section below

### For Development
```bash
# Install dependencies
bun install

# Start development server
bun run dev
```

Visit `http://localhost:3000` to see the contact form.

### Prerequisites
- **Production:** PHP 7.4+ with MySQL database (installer checks automatically)
- **Development:** Node.js 18+ or Bun

## 🛠️ Configuration

### Backend Mode
Control whether to use PHP backend or local testing:

**Environment Variable:**
```bash
NEXT_PUBLIC_USE_PHP_BACKEND=false  # Local testing
NEXT_PUBLIC_USE_PHP_BACKEND=true   # PHP backend
```

**URL Parameter Override:**
- `?php=true` - Force PHP backend mode
- `?php=false` - Force local testing mode

### Database Integration (NEW!)
Automatic field mapping converts "Full Name" to separate database fields:

**Database Environment Variables:**
```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password
DB_SSL=false
```

**Field Mapping:**
- "John Smith" → `first_name: "John"`, `last_name: "Smith"`
- "Mary Jane Watson" → `first_name: "Mary"`, `last_name: "Jane Watson"`
- All form fields automatically mapped to database columns

**Test Database Integration:**
```bash
bun run db:test  # Test field mapping functionality
```

### Auto-Save Settings
Configure auto-save behavior in `config/features.config.ts`:

```typescript
AUTO_SAVE_CONFIG: {
  enabled: true,
  timeouts: {
    save: 2000,        // Save after 2 seconds of inactivity
    showStatus: 3000,  // Show "saved" message for 3 seconds
    debounce: 300,     // Wait 300ms between keystrokes
  }
}
```

### UI Styling
Customize appearance in `config/ui.config.ts`:

```typescript
UI_THEME_CONFIG: {
  primaryColor: '#1a679f',      // GlowHost brand blue
  borderRadius: 'md',           // rounded-md for all components
  colorScheme: 'light',         // light or dark theme
}
```

## 📁 Project Structure

```
Contact-Form-Sales/
├── installer.php          # ⭐ ONE-CLICK INSTALLER - Upload this single file!
├── install/               # 🆕 Professional Installation Wizard
│   ├── index.php         # Main installation interface
│   ├── assets/           # CSS and JavaScript for installer
│   ├── steps/            # Individual installation steps
│   │   ├── welcome.php   # Welcome and requirements check
│   │   ├── database-test.php # Database connection testing
│   │   ├── table-setup.php   # Automated table creation
│   │   ├── admin-setup.php   # Admin account creation
│   │   ├── config-gen.php    # Configuration file generation
│   │   └── completion.php    # Installation completion
│   └── ajax/             # Real-time AJAX handlers
├── api/                     # PHP backend endpoints
│   └── submit-form.php     # Main form handler with database integration
├── config/                 # Configuration files
│   ├── app.config.ts      # App settings, branding, API
│   ├── features.config.ts # Auto-save, validation, uploads
│   ├── ui.config.ts       # UI styling and components
│   └── database.config.ts # Database field mapping and error handling
├── scripts/               # Utility scripts
│   ├── test-database.js   # Database connection and field mapping tests
│   ├── create-database-table.sql # SQL table creation script
│   ├── verify-github-sync.sh # GitHub sync verification
│   └── git-status.js      # Git status checking
├── src/
│   ├── app/
│   │   ├── page.tsx       # Main contact form
│   │   └── layout.tsx     # App layout
│   ├── components/
│   │   ├── ui/            # UI components
│   │   ├── layout/        # Layout components
│   │   └── providers/     # React context providers
│   ├── hooks/             # Custom React hooks
│   ├── lib/               # Utility functions
│   └── types/             # TypeScript type definitions
├── admin/                 # 🆕 Admin Dashboard (foundation)
├── DEPLOYMENT.md          # 🆕 One-click deployment guide
├── DATABASE_INTEGRATION.md # Comprehensive database setup guide
└── out/                   # Production build output
```

## 🚀 Deployment

### Option 1: cPanel Hosting with Database (Recommended)

1. **Set up database:**
```bash
# Run the SQL script in your MySQL database
mysql -u your_user -p your_database < scripts/create-database-table.sql
```

2. **Configure environment variables** in cPanel:
```
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password
```

3. **Build the static files:**
```bash
bun run build
```

4. **Upload contents of `out/` folder** to your cPanel `public_html` directory

5. **Upload `api/submit-form.php`** to `public_html/api/`

6. **Configure PHP backend** in `api/submit-form.php`:
   - Update CORS origins for your domain
   - Set your email address for notifications
   - Enable email sending if desired

7. **Test database integration:**
```bash
# Set your credentials and test
DB_HOST=your_host DB_NAME=your_db DB_USER=your_user DB_PASSWORD=your_pass bun run db:test
```

### Option 2: Static Hosting (Netlify, Vercel, GitHub Pages)

1. **Build static files:**
```bash
bun run build
```

2. **Deploy `out/` folder** to your hosting provider

3. **Note:** PHP backend features will not work with static hosting

### Option 3: Node.js Hosting

Deploy as a Next.js application to platforms like Vercel, Railway, or DigitalOcean.

## 🔧 PHP Backend Configuration

### Security Setup (IMPORTANT)

**Development:**
```php
header('Access-Control-Allow-Origin: *'); // Allow all domains
```

**Production (REQUIRED):**
```php
header('Access-Control-Allow-Origin: https://yourdomain.com'); // Specific domain only
```

### Email Configuration

In `api/submit-form.php`:

```php
$send_email = true; // Enable email notifications
$to = 'your-email@yourdomain.com'; // Your email address
```

### File Permissions

- `submit-form.php`: 644 (-rw-r--r--)
- `logs/` directory: 755 (drwxr-xr-x)

## 🎨 Customization

### Branding
Update branding in `config/app.config.ts`:

```typescript
branding: {
  LOGO_URL: 'https://yourcompany.com/logo.png',
  PRIMARY_COLOR: '#your-brand-color',
  SUPPORT_INFO: 'Your Support Info',
}
```

### Form Fields
Add or modify form fields in `src/app/page.tsx` and update:
- Validation rules in `config/features.config.ts`
- Auto-save field list in `FORM_AUTO_SAVE_CONFIGS`

### File Upload Limits
Configure in `config/features.config.ts`:

```typescript
FILE_UPLOAD_CONFIG: {
  maxFileSize: 10 * 1024 * 1024, // 10MB
  maxFiles: 5,
  allowedExtensions: {
    images: ['.jpg', '.jpeg', '.png', '.gif'],
    documents: ['.pdf', '.txt', '.log'],
    archives: ['.zip', '.rar', '.7z'],
  }
}
```

## 🧪 Development Mode

The application includes a development mode with:
- **Pre-filled form data** for easy testing
- **Simulated form submissions** (no PHP backend required)
- **Development indicator** banner
- **Console logging** of form data

Toggle between modes using the `USE_PHP_BACKEND` setting.

## 🔍 Testing

### Local Testing
```bash
bun run dev
```

Form will be pre-filled with test data and submissions will be simulated.

### PHP Backend Testing
Set `NEXT_PUBLIC_USE_PHP_BACKEND=true` and test with a local PHP server or staging environment.

## 📊 Technical Details

- **Framework:** Next.js 15+ with App Router
- **Styling:** Tailwind CSS with custom component system
- **TypeScript:** Full type safety throughout
- **Package Manager:** Bun (npm also supported)
- **Auto-save:** LocalStorage with debouncing and session management
- **File Handling:** Client-side validation with server-side security
- **Responsive:** Mobile-first design approach

## 🛡️ Security Features

- **CORS configuration** for production deployment
- **File upload validation** with size and type restrictions
- **Rate limiting** to prevent spam submissions
- **Input sanitization** and validation
- **SQL injection protection** through parameterized queries
- **XSS protection** through input filtering

## 🐛 Troubleshooting

### Auto-save Not Working
1. Check browser localStorage is enabled
2. Verify `features.config.ts` has auto-save enabled
3. Check console for JavaScript errors

### File Uploads Failing
1. Verify file size under 10MB limit
2. Check file type is in allowed extensions
3. Ensure proper CORS configuration

### PHP Backend Issues
1. Check PHP error logs in cPanel
2. Verify file permissions are correct
3. Ensure CORS headers match your domain

## 📄 License

This project is configured for GlowHost contact form use. Modify branding and configuration as needed for your implementation.

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review configuration files for customization options
3. Test in development mode first before deploying

---

**Ready for production!** 🚀 Build, upload, and configure your PHP backend to go live.
