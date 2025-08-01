/**
 * GlowHost Contact Form System - Deployment Configuration
 * Centralized configuration for different deployment environments
 */

const deploymentConfig = {
  // General deployment settings
  general: {
    appName: "GlowHost Contact Form System",
    version: "1.0.0",
    buildDir: "out",
    deploymentDir: "deployment-package",
    supportedPhpVersions: ["7.4", "8.0", "8.1", "8.2"],
    requiredExtensions: ["curl", "json", "mbstring", "session"]
  },

  // Shared hosting specific configuration
  sharedHosting: {
    // cPanel optimizations
    cpanel: {
      filePermissions: {
        files: "644",
        directories: "755",
        executables: "755"
      },
      htaccessOptimizations: true,
      compressionEnabled: true,
      cacheHeaders: true,
      errorPages: {
        "404": "/404.html",
        "500": "/500.html"
      }
    },

    // GlowHost specific optimizations
    glowhost: {
      optimizeForCloudFlare: true,
      enableServerSideIncludes: false,
      phpVersionTarget: "8.1",
      memoryLimitOptimization: true,
      mysqlOptimizations: false // Not using MySQL in this deployment
    },

    // Security settings for shared hosting
    security: {
      hideServerSignature: true,
      preventDirectoryListing: true,
      blockSensitiveFiles: [
        ".env*",
        "*.log",
        "*.bak",
        "*.backup",
        "*.old",
        "*.tmp",
        "config/*.ts",
        "src/*"
      ],
      corsSettings: {
        allowOrigin: "*", // Configure based on your domain
        allowMethods: ["GET", "POST", "OPTIONS"],
        allowHeaders: ["Content-Type", "Authorization", "X-Requested-With"]
      }
    }
  },

  // Build optimization settings
  build: {
    // Next.js static export optimization
    nextjs: {
      output: "export",
      trailingSlash: true,
      distDir: "out",
      generateBuildId: () => `build-${Date.now()}`,
      poweredByHeader: false,
      compress: true,
      generateEtags: false
    },

    // Asset optimization
    assets: {
      imageOptimization: {
        unoptimized: true, // Required for static export
        domains: [
          "glowhost.com",
          "source.unsplash.com",
          "images.unsplash.com"
        ]
      },
      cssOptimization: true,
      jsOptimization: true,
      removeUnusedAssets: true
    },

    // File processing
    files: {
      // Files to include in deployment
      include: [
        "out/**/*",
        "api/**/*.php",
        "config/**/*.{js,json,php}",
        "README.md"
      ],
      // Files to exclude from deployment
      exclude: [
        "node_modules/**/*",
        ".git/**/*",
        ".next/**/*",
        "src/**/*",
        "scripts/**/*",
        "*.ts",
        "*.tsx",
        "package*.json",
        "bun.lockb",
        "tsconfig.json",
        "next.config.js",
        "installer.php" // Original source installer
      ],
      // File transformations
      transformations: {
        ".ts": "exclude", // Remove TypeScript files
        ".tsx": "exclude",
        ".php": "include" // Keep PHP files
      }
    }
  },

  // Server configuration templates
  serverConfig: {
    apache: {
      // .htaccess template for Apache servers
      htaccessTemplate: `# GlowHost Contact Form System - Apache Configuration
# Auto-generated from deployment.config.js

# Enable rewrite engine
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options nosniff
  Header always set X-Frame-Options DENY
  Header always set X-XSS-Protection "1; mode=block"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;"
</IfModule>

# Performance optimizations
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  ExpiresByType image/png "access plus 1 month"
  ExpiresByType image/jpg "access plus 1 month"
  ExpiresByType image/jpeg "access plus 1 month"
  ExpiresByType image/gif "access plus 1 month"
  ExpiresByType image/svg+xml "access plus 1 month"
  ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json text/xml
</IfModule>

# Next.js routing support
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule ^(.*)$ /index.html [L]

# Security - Block access to sensitive files
<FilesMatch "\\.(env|log|bak|backup|old|tmp)$">
  Order Allow,Deny
  Deny from all
</FilesMatch>`
    },

    nginx: {
      // Nginx configuration template (for reference)
      configTemplate: `# GlowHost Contact Form System - Nginx Configuration
# Reference configuration (most shared hosting uses Apache)

server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/site;
    index index.html;

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Static assets with long cache
    location ~* \\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }

    # API routing
    location /api/ {
        try_files $uri $uri.php =404;
        include fastcgi_params;
        fastcgi_pass php-fpm;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Next.js routing fallback
    location / {
        try_files $uri $uri/ /index.html;
    }
}`
    }
  },

  // Email configuration for PHP API
  email: {
    // Default SMTP settings template
    smtp: {
      host: "mail.glowhost.com", // Default for GlowHost
      port: 587,
      encryption: "tls",
      auth: true,
      username: "", // To be configured by user
      password: "", // To be configured by user
      from: {
        email: "", // To be configured by user
        name: "Contact Form System"
      }
    },
    
    // PHP mail() function settings
    phpMail: {
      enabled: true,
      headers: {
        "Content-Type": "text/html; charset=UTF-8",
        "MIME-Version": "1.0",
        "X-Mailer": "GlowHost Contact Form System"
      }
    },

    // Template settings
    templates: {
      subject: "New Contact Form Submission",
      adminNotification: true,
      autoResponse: true,
      includeUserAgent: true,
      includeTimestamp: true
    }
  },

  // Development and testing settings
  development: {
    enableDebugMode: false,
    logLevel: "error", // error, warn, info, debug
    testEmailRecipient: "", // For testing email functionality
    enableCors: true,
    hotReload: false // Not applicable for static export
  },

  // Deployment automation settings
  automation: {
    github: {
      createReleases: true,
      tagFormat: "v{version}",
      releaseNotes: true,
      artifactRetention: 30 // days
    },
    
    ftp: {
      enabled: false, // Enable via GitHub Actions inputs
      passive: true,
      timeout: 30000,
      retries: 3
    },
    
    ssh: {
      enabled: false, // Enable via GitHub Actions inputs
      port: 22,
      timeout: 30000
    }
  },

  // Validation rules
  validation: {
    requiredFiles: [
      "index.html",
      "api/submit-form.php"
    ],
    requiredDirectories: [
      "_next",
      "api",
      "config"
    ],
    maxDeploymentSize: "50MB",
    allowedFileTypes: [
      ".html", ".css", ".js", ".json", ".php", 
      ".png", ".jpg", ".jpeg", ".gif", ".svg", 
      ".woff", ".woff2", ".ttf", ".eot",
      ".ico", ".txt", ".md"
    ]
  }
};

// Export for Node.js environment
if (typeof module !== 'undefined' && module.exports) {
  module.exports = deploymentConfig;
}

// Export for browser/other environments
if (typeof window !== 'undefined') {
  window.deploymentConfig = deploymentConfig;
}

// Make available as global for PHP integration
if (typeof global !== 'undefined') {
  global.deploymentConfig = deploymentConfig;
}