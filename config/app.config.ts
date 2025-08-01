/**
 * APPLICATION CONFIGURATION
 *
 * Core application settings, branding, API endpoints, and environment detection.
 * Modify values below to customize the application behavior.
 */

// ============================================================================
// BRAND IDENTITY
// ============================================================================

/**
 * Application identity and version information
 * VERSION: Update this when deploying new features
 * APP_NAME: Displays in browser titles and notifications
 */
export const APP_CONFIG = {
  identity: {
    VERSION: '273',
    APP_NAME: 'GlowHost Contact Form',
  },

  /**
   * Brand colors and visual identity
   * PRIMARY_COLOR: Main brand color used throughout the interface
   * SUPPORT_INFO: Displayed in headers and contact information
   */
  branding: {
    LOGO_URL: 'https://glowhost.com/wp-content/uploads/page_notag.png',
    PRIMARY_COLOR: '#1a679f',
    SUPPORT_INFO: '24 / 7 / 365 Support',
    PHONE_DISPLAY: '1 (888) 293-HOST',
    PHONE_LINK: 'tel:+18882934678',
  },

  /**
   * Navigation and external links
   * SUPPORT_HOME: Where "Support" links redirect
   * NETLIFY_URL: Current deployment URL for links and redirects
   */
  links: {
    SUPPORT_HOME: '/support/',
    NETLIFY_URL: 'https://same-a89hlldg4cm-latest.netlify.app',
  },

  /**
   * Form field character limits
   * Balance user needs vs database/display constraints
   */
  limits: {
    SUBJECT: 250,
    MESSAGE: 10000,
    FILE_DESCRIPTION: 150,
  },

  /**
   * Available department options
   * ORDER MATTERS: First item is default selection
   */
  departments: [
    'Sales Questions',
    'Technical Support',
    'Billing Support',
    'General Inquiry'
  ] as const,

  /**
   * Environment detection for conditional behavior
   * isStaging: Uses NEXT_PUBLIC_ENVIRONMENT variable
   */
  isDevelopment: process.env.NODE_ENV === 'development',
  isProduction: process.env.NODE_ENV === 'production',
  isStaging: process.env.NEXT_PUBLIC_ENVIRONMENT === 'staging',
} as const;

// ============================================================================
// API CONFIGURATION
// ============================================================================

/**
 * API endpoints and external service URLs
 * SUBMIT_FORM: Where form data is sent for processing
 * UPLOAD_FILE: Endpoint for file attachments
 * USER_AGENT: Service for detecting browser/device info
 */
export const API_CONFIG = {
  endpoints: {
    SUBMIT_FORM: '/api/submit-form.php',
    UPLOAD_FILE: '/api/upload-file.php',
    USER_AGENT: 'https://api.ipify.org?format=json',
  },

  /**
   * API request settings
   * timeout: How long to wait for responses (milliseconds)
   * retries: Number of retry attempts for failed requests
   */
  settings: {
    timeout: 30000,
    retries: 3,
    enableCaching: true,
  },
} as const;

// ============================================================================
// BACKEND MODE DETECTION
// ============================================================================

/**
 * Determines whether to use PHP backend or local testing
 * Checks URL parameters first, then environment variables, then defaults
 *
 * URL override: ?php=true or ?php=false
 * Environment: NEXT_PUBLIC_USE_PHP_BACKEND=true/false
 * Default: false (local testing mode)
 */
export function getBackendMode(): boolean {
  // Check URL parameter first
  if (typeof window !== 'undefined') {
    const urlParams = new URLSearchParams(window.location.search);
    const phpParam = urlParams.get('php');
    if (phpParam === 'true') return true;
    if (phpParam === 'false') return false;
  }

  // Check environment variable
  const envMode = process.env.NEXT_PUBLIC_USE_PHP_BACKEND;
  if (envMode === 'true') return true;
  if (envMode === 'false') return false;

  // Default to local testing
  return false;
}
