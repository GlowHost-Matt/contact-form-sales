# Contact Form Sales - Advanced Version

## Overview
This is the **advanced local version** of the Contact Form Sales application, built with Next.js and configured for `/advanced/` subdirectory deployment. This version includes enhanced features for modular department routing and centralized configuration.

## Features
- **Subdirectory Deployment**: Pre-configured with `basePath: '/advanced'` for deployment under `/advanced/` path
- **Modular Architecture**: Centralized department configuration and dynamic routing
- **Static Export**: All files are pre-built as static assets for easy deployment
- **PHP Backend**: Included contact form handler with validation and security features

## File Structure
```
├── index.html                 # Main contact form page
├── 404.html                   # Error page
├── contact-handler.php        # PHP backend for form processing
├── _next/                     # Next.js static assets
│   ├── static/chunks/         # JavaScript bundles
│   ├── static/css/           # Stylesheets
│   └── static/media/         # Font files
└── index.txt                 # Build manifest
```

## Deployment
This build is specifically configured for deployment at `/advanced/` subdirectory. 

### Quick Deploy
1. Upload all files to your web server's `/advanced/` directory
2. Edit `contact-handler.php` to configure email addresses
3. Ensure PHP is enabled on your server
4. Test the form at `https://yourdomain.com/advanced/`

### Detailed Instructions
See `DEPLOYMENT-ADVANCED.md` for complete deployment instructions.

## Testing URLs
Once deployed, the form will be available at:
- **Production**: `https://yourdomain.com/advanced/`
- **Local testing**: `http://localhost/advanced/`

## Configuration
- **Base Path**: `/advanced/` (configured in build)
- **Form Handler**: `contact-handler.php`
- **Styling**: Tailwind CSS with custom design system
- **Framework**: Next.js static export

## Support
This is the advanced version with enhanced department routing capabilities. All assets are pre-configured for immediate deployment to the `/advanced/` subdirectory.