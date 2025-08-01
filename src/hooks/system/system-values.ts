/**
 * SYSTEM HOOK CONFIGURATION VALUES
 *
 * ⭐ THIS IS WHERE YOU MODIFY SYSTEM HOOK SETTINGS ⭐
 *
 * This file contains the actual configuration data for system-related hooks.
 * To change system behavior, performance monitoring, or utility settings, modify values here.
 *
 * FOR TYPE DEFINITIONS: See system-types.ts
 * FOR HELPER FUNCTIONS: See system-utils.ts
 */

/**
 * PAGE FOCUS CONFIGURATION
 *
 * Controls page visibility and focus detection behavior.
 */
export const PAGE_FOCUS_CONFIG = {
  // Focus behavior
  autoFocusDelay: 100, // milliseconds to wait before auto-focusing
  trackVisibility: true, // Track page visibility changes
  trackFocus: true, // Track focus/blur events

  // Accessibility settings
  skipToContentOnFocus: false, // Skip to main content when focused
  highlightOnFocus: false, // Add visual highlight when focused

  // Performance settings
  throttleInterval: 100, // Throttle focus events (ms)

} as const;

/**
 * USER AGENT DETECTION CONFIGURATION
 *
 * Controls how user agent information is detected and processed.
 */
export const USER_AGENT_CONFIG = {
  // Detection settings
  enableBotDetection: true,
  enableDeviceTypeDetection: true,
  enableBrowserVersionDetection: true,

  // Bot detection patterns
  botPatterns: [
    /bot/i,
    /crawler/i,
    /spider/i,
    /scraper/i,
    /facebook/i,
    /twitter/i,
    /linkedin/i,
    /google/i,
    /bing/i,
    /yahoo/i,
  ],

  // Browser detection patterns
  browserPatterns: {
    chrome: /chrome|crios/i,
    firefox: /firefox|fxios/i,
    safari: /safari/i,
    edge: /edg/i,
    opera: /opr|opera/i,
    ie: /msie|trident/i,
  },

  // OS detection patterns
  osPatterns: {
    windows: /windows nt/i,
    macos: /macintosh|mac os x/i,
    android: /android/i,
    ios: /iphone|ipad|ipod/i,
    linux: /linux/i,
  },

  // Device type breakpoints
  deviceBreakpoints: {
    mobile: 768, // px
    tablet: 1024, // px
  },

  // Caching settings
  cacheUserAgent: true,
  cacheExpiration: 24 * 60 * 60 * 1000, // 24 hours

} as const;

/**
 * PERFORMANCE MONITORING CONFIGURATION
 *
 * Controls how performance metrics are collected and reported.
 */
export const PERFORMANCE_CONFIG = {
  // Monitoring settings
  enableAutoMonitoring: false, // Auto-start monitoring on mount
  collectMemoryUsage: true, // Monitor memory usage (if available)
  collectConnectionInfo: true, // Monitor connection type

  // Timing settings
  measureInterval: 1000, // How often to collect metrics (ms)
  maxMeasurements: 100, // Maximum number of measurements to keep

  // Thresholds for warnings
  thresholds: {
    loadTimeWarning: 3000, // ms
    renderTimeWarning: 100, // ms
    memoryWarning: 50, // MB
  },

  // Reporting settings
  enableConsoleLogging: false, // Log metrics to console
  enableLocalStorage: false, // Store metrics in localStorage

} as const;

/**
 * LOCAL STORAGE CONFIGURATION
 *
 * Controls local storage behavior and serialization.
 */
export const STORAGE_CONFIG = {
  // Storage behavior
  prefix: 'glowhost-', // Prefix for all storage keys
  enableCompression: false, // Compress stored data
  enableEncryption: false, // Encrypt stored data (requires crypto setup)

  // Synchronization settings
  enableTabSync: true, // Sync across browser tabs
  syncThrottleDelay: 100, // ms

  // Data management
  enableAutoCleanup: true, // Clean up expired data
  defaultExpiration: 7 * 24 * 60 * 60 * 1000, // 7 days
  maxStorageSize: 5 * 1024 * 1024, // 5MB

  // Error handling
  enableFallbackMemory: true, // Use memory storage if localStorage fails
  enableErrorLogging: true, // Log storage errors

  // Default serializers
  serializers: {
    json: {
      parse: JSON.parse,
      stringify: JSON.stringify,
    },
    string: {
      parse: (value: string) => value,
      stringify: (value: unknown) => String(value),
    },
  },

} as const;

/**
 * SYSTEM HOOK DEFAULTS
 *
 * Default configurations that apply across all system hooks.
 */
export const SYSTEM_HOOK_DEFAULTS = {
  // Error handling
  enableErrorBoundary: true,
  logErrors: true,
  retryOnError: true,
  maxRetries: 3,

  // Performance
  enableMemoization: true,
  enableThrottling: true,
  enableDebouncing: true,

  // Development
  enableDevLogging: process.env.NODE_ENV === 'development',
  enablePerformanceWarnings: process.env.NODE_ENV === 'development',

} as const;
