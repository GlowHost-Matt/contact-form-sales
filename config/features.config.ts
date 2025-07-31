/**
 * FEATURES CONFIGURATION
 *
 * Auto-save behavior, file uploads, validation rules, and form features.
 * Modify values below to customize feature behavior and limits.
 */

// ============================================================================
// AUTO-SAVE TYPES & CONFIGURATION
// ============================================================================

/**
 * Auto-save status states
 */
export type AutoSaveStatus = 'idle' | 'session-active' | 'saving' | 'saved' | 'error' | 'recovered';

/**
 * Auto-save configuration interface
 */
export interface AutoSaveConfig {
  enabled: boolean;
  timeouts: {
    save: number;
    showStatus: number;
    debounce: number;
    sessionTimeout: number;
    immediateResponse: number;
  };
  storage: {
    prefix: string;
    maxAge: number;
    compression: boolean;
  };
  ui: {
    showIndicator: boolean;
    position: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
    enableSessionMode: boolean;
    sessionIdleText: string;
    showDuringSession: boolean;
  };
  forms: {
    [key: string]: {
      enabled: boolean;
      saveTimeout: number;
      fields: readonly string[];
      clearOnSubmit: boolean;
      sessionBehavior: {
        enabled: boolean;
        textareaFastMode: boolean;
        inactivityTimeout: number;
        immediateStart: boolean;
      };
    };
  };
}

/**
 * Global auto-save behavior
 * enabled: Turn auto-save on/off globally
 * save: Wait time after user stops typing (2000ms = responsive but not overwhelming)
 * showStatus: How long to show "Saved" message (3000ms = enough time to notice)
 * debounce: Delay before processing keystrokes (300ms = feels instant but prevents spam)
 * sessionTimeout: Hide indicator after inactivity (30000ms = 30 seconds)
 * immediateResponse: Show "typing" feedback time (100ms = almost instant)
 */
export const AUTO_SAVE_CONFIG = {
  enabled: true,

  timeouts: {
    save: 2000,
    showStatus: 3000,
    debounce: 300,
    sessionTimeout: 30000,
    immediateResponse: 100,
  },

  /**
   * Auto-save storage settings
   * prefix: LocalStorage key prefix for saved data
   * maxAge: Keep saved data for 3 days (259200000ms)
   * compression: Enable for large forms (disabled for simplicity)
   */
  storage: {
    prefix: 'glowhost-autosave',
    maxAge: 3 * 24 * 60 * 60 * 1000,
    compression: false,
  },

  /**
   * Auto-save UI indicator settings
   * showIndicator: Display auto-save status to users
   * position: Where to show the floating indicator
   * enableSessionMode: Keep indicator visible during active sessions
   * sessionIdleText: Text shown during active sessions
   * showDuringSession: Keep visible while user is working
   */
  ui: {
    showIndicator: true,
    position: 'bottom-right',
    enableSessionMode: true,
    sessionIdleText: 'Auto-save active',
    showDuringSession: true,
  },

  forms: {} as any,  // Will be populated after FORM_AUTO_SAVE_CONFIGS is defined
} as const;

/**
 * Form-specific auto-save configurations
 * Each form can have different auto-save behavior:
 * - Contact forms: Fast saving for detailed messages
 * - Support threads: Quick saving for rapid replies
 * - Notes: Slower saving for thoughtful content
 */
export const FORM_AUTO_SAVE_CONFIGS = {
  /**
   * Main contact form auto-save settings
   * saveTimeout: 2000ms = fast saving for better UX
   * fields: All form fields that should be auto-saved
   * textareaFastMode: Extra responsive for long message fields
   * inactivityTimeout: 30 seconds before hiding indicator
   */
  'contact-form': {
    enabled: true,
    saveTimeout: 2000,
    fields: ['name', 'email', 'phone', 'domainName', 'subject', 'message', 'showAttachment', 'fileDescriptions'],
    clearOnSubmit: false,
    sessionBehavior: {
      enabled: true,
      textareaFastMode: true,
      inactivityTimeout: 30000,
      immediateStart: true,
    },
  },

  /**
   * Support thread reply auto-save settings
   * saveTimeout: 2000ms = fast saving for quick replies
   * inactivityTimeout: 20 seconds = shorter for rapid conversation
   */
  'support-thread': {
    enabled: true,
    saveTimeout: 2000,
    fields: ['reply', 'showAttachment', 'fileDescriptions'],
    clearOnSubmit: false,
    sessionBehavior: {
      enabled: true,
      textareaFastMode: true,
      inactivityTimeout: 20000,
      immediateStart: true,
    },
  },

  /**
   * Confirmation notes auto-save settings
   * saveTimeout: 3000ms = slower saving for thoughtful notes
   * textareaFastMode: false = standard timing for careful writing
   * inactivityTimeout: 45 seconds = longer for thoughtful content
   */
  'confirmation-notes': {
    enabled: true,
    saveTimeout: 3000,
    fields: ['notes', 'followup', 'priority', 'status'],
    clearOnSubmit: false,
    sessionBehavior: {
      enabled: true,
      textareaFastMode: false,
      inactivityTimeout: 45000,
      immediateStart: true,
    },
  },
} as const;

/**
 * Complete auto-save configuration combining base settings with form-specific configs
 */
export const COMPLETE_AUTO_SAVE_CONFIG: AutoSaveConfig = {
  ...AUTO_SAVE_CONFIG,
  forms: FORM_AUTO_SAVE_CONFIGS,
};

/**
 * Generate storage key for auto-save data
 */
export const generateStorageKey = (formType: string, userId?: string): string => {
  const prefix = COMPLETE_AUTO_SAVE_CONFIG.storage.prefix;
  return userId ? `${prefix}-${formType}-${userId}` : `${prefix}-${formType}`;
};

/**
 * Check if auto-save data has expired
 */
export const isDataExpired = (timestamp: number): boolean => {
  const maxAge = COMPLETE_AUTO_SAVE_CONFIG.storage.maxAge;
  return Date.now() - timestamp > maxAge;
};

// ============================================================================
// FORM VALIDATION
// ============================================================================

/**
 * Form field validation rules
 * REQUIRED_FIELDS: Form cannot submit without these
 * EMAIL_REGEX: Standard email validation pattern
 * PHONE_REGEX: Flexible international phone number format
 * MIN_LENGTHS: Minimum character requirements prevent spam
 */
export const FORM_VALIDATION = {
  REQUIRED_FIELDS: ['name', 'email', 'subject', 'message'] as const,
  EMAIL_REGEX: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  PHONE_REGEX: /^[\+]?[1-9][\d]{0,15}$/,
  MIN_LENGTHS: {
    name: 2,
    subject: 5,
    message: 10
  } as const,
  ALPHANUMERIC_REGEX: /^[a-zA-Z0-9\s]*$/
} as const;

/**
 * User-friendly validation error messages
 * Customize these to match your brand voice and help users fix issues
 */
export const VALIDATION_MESSAGES = {
  REQUIRED: 'This field is required',
  EMAIL_INVALID: 'Please enter a valid email address',
  PHONE_INVALID: 'Please enter a valid phone number',
  TOO_SHORT: (field: string, min: number) => `${field} must be at least ${min} characters`,
  TOO_LONG: (field: string, max: number) => `${field} must be no more than ${max} characters`,
  FILE_TOO_LARGE: (filename: string, maxSize: string) => `"${filename}" exceeds the ${maxSize} limit`,
  FILE_TYPE_INVALID: (filename: string) => `"${filename}" is not a supported file type`,
  ALPHANUMERIC_ONLY: 'Only letters, numbers, and spaces are allowed'
} as const;

// ============================================================================
// FILE UPLOAD CONFIGURATION
// ============================================================================

/**
 * File upload security and size limits
 * maxFileSize: 10MB balance between user needs and server capacity
 * maxFiles: 5 files maximum prevents overwhelming submissions
 * allowedExtensions: Security-focused list of safe file types
 */
export const FILE_UPLOAD_CONFIG = {
  maxFileSize: 10 * 1024 * 1024,
  maxFiles: 5,

  /**
   * Allowed file types for security
   * images: Common image formats for screenshots/examples
   * documents: Safe document types for sharing information
   * archives: Compressed files (can disable if security is critical)
   */
  allowedExtensions: {
    images: ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'],
    documents: ['.pdf', '.txt', '.log'],
    archives: ['.zip', '.rar', '.7z'],
  },

  /**
   * User experience settings
   * showPreviews: Display image thumbnails
   * enableDragDrop: Allow drag-and-drop uploads
   * enableDescriptions: Let users describe attachments
   */
  showPreviews: true,
  enableDragDrop: true,
  enableDescriptions: true,

  /**
   * Advanced security options
   * scanForMalware: Enable if you have virus scanning service
   * requireDescription: Force users to describe files
   */
  scanForMalware: false,
  requireDescription: false,

  /**
   * File upload error messages
   */
  messages: {
    fileTooLarge: (filename: string, maxSize: string) =>
      `"${filename}" exceeds the ${maxSize} limit. Please choose a smaller file.`,
    invalidFileType: (filename: string) =>
      `"${filename}" is not a supported file type. Please upload images, PDFs, text files (.txt, .log), or safe archives (.zip, .rar, .7z).`,
    uploadFailed: (filename: string) =>
      `Failed to upload "${filename}". Please try again.`,
  },
} as const;

// ============================================================================
// FORM BEHAVIOR CONFIGURATION
// ============================================================================

/**
 * Form submission and interaction behavior
 * preventDoubleSubmit: Prevent accidental double submissions
 * showSubmissionFeedback: Display success/error messages
 * resetAfterSubmit: Clear form after successful submission
 * validateOnSubmit: Check all fields before submitting
 * enableAutoSave: Connect to auto-save system
 * showCharacterCount: Display character limits to users
 */
export const FORM_BEHAVIOR_CONFIG = {
  preventDoubleSubmit: true,
  showSubmissionFeedback: true,
  resetAfterSubmit: false,
  validateOnSubmit: true,
  stopOnFirstError: false,
  showInlineErrors: true,
  enableAutoSave: true,
  autoSaveDelay: 1000,
  showCharacterCount: true,
  warnAtPercentage: 90,
} as const;

/**
 * Default notification helpers for development
 * Used when no notification system is provided
 */
export const NOTIFICATION_HELPERS = {
  showError: (title: string, message: string) => {
    console.error(`❌ ${title}: ${message}`);
  },
  showWarning: (title: string, message: string) => {
    console.warn(`⚠️ ${title}: ${message}`);
  },
  showSuccess: (title: string, message: string) => {
    console.log(`✅ ${title}: ${message}`);
  },
  showInfo: (title: string, message: string) => {
    console.info(`ℹ️ ${title}: ${message}`);
  },
} as const;

/**
 * Feature defaults and fallback values
 */
export const FEATURE_DEFAULTS = {
  autoSaveEnabled: true,
  fileUploadsEnabled: true,
  validationEnabled: true,
  notificationsEnabled: true,
} as const;

/**
 * Consolidated features configuration
 * Import this object to access all feature settings
 */
export const FEATURES_CONFIG = {
  autoSave: COMPLETE_AUTO_SAVE_CONFIG,
  formAutoSave: FORM_AUTO_SAVE_CONFIGS,
  validation: FORM_VALIDATION,
  validationMessages: VALIDATION_MESSAGES,
  fileUpload: FILE_UPLOAD_CONFIG,
  formBehavior: FORM_BEHAVIOR_CONFIG,
  notifications: NOTIFICATION_HELPERS,
  defaults: FEATURE_DEFAULTS,
} as const;

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get auto-save configuration for a specific form
 */
export function getAutoSaveConfig(formId: string) {
  const formConfig = FORM_AUTO_SAVE_CONFIGS[formId as keyof typeof FORM_AUTO_SAVE_CONFIGS];
  if (!formConfig) {
    console.warn(`No auto-save configuration found for form: ${formId}`);
    return null;
  }

  return {
    ...COMPLETE_AUTO_SAVE_CONFIG,
    ...formConfig,
  };
}

/**
 * Validate uploaded file against security rules
 */
export function validateFile(file: File): { valid: boolean; error?: string } {
  const config = FILE_UPLOAD_CONFIG;

  // Check file size
  if (file.size > config.maxFileSize) {
    const maxSizeMB = Math.round(config.maxFileSize / (1024 * 1024));
    return {
      valid: false,
      error: config.messages.fileTooLarge(file.name, `${maxSizeMB}MB`)
    };
  }

  // Check file type
  const extension = '.' + file.name.split('.').pop()?.toLowerCase();
  const allAllowedExtensions = [
    ...config.allowedExtensions.images,
    ...config.allowedExtensions.documents,
    ...config.allowedExtensions.archives,
  ] as const;

  if (!allAllowedExtensions.includes(extension as any)) {
    return {
      valid: false,
      error: config.messages.invalidFileType(file.name)
    };
  }

  return { valid: true };
}

/**
 * Format file size for display
 */
export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
