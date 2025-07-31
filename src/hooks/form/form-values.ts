/**
 * FORM HOOK CONFIGURATION VALUES
 *
 * ⭐ THIS IS WHERE YOU MODIFY FORM HOOK SETTINGS ⭐
 *
 * This file contains the actual configuration data for form-related hooks.
 * To change form behavior, validation rules, or file handling settings, modify values here.
 *
 * FOR TYPE DEFINITIONS: See form-types.ts
 * FOR HELPER FUNCTIONS: See form-utils.ts
 */

import type { ValidationRule, NotificationHelpers } from './form-types';

/**
 * FILE HANDLING CONFIGURATION
 *
 * Controls file upload behavior, size limits, and allowed file types.
 */
export const FILE_HANDLING_CONFIG = {
  // File size limits
  maxFileSize: 10 * 1024 * 1024, // 10MB in bytes

  // Allowed file extensions
  allowedExtensions: {
    images: ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'],
    documents: ['.pdf', '.txt', '.log'],
    archives: ['.zip', '.rar', '.7z'],
  },

  // UI behavior
  showPreviews: true,
  enableDragDrop: true,

  // Error messages
  messages: {
    fileTooLarge: (filename: string, maxSize: string) =>
      `"${filename}" exceeds the ${maxSize} limit. Please choose a smaller file.`,
    invalidFileType: (filename: string) =>
      `"${filename}" is not a supported file type. Please upload images, PDFs, text files (.txt, .log), or safe archives (.zip, .rar, .7z).`,
    uploadFailed: (filename: string) =>
      `Failed to upload "${filename}". Please try again.`,
  },
} as const;

/**
 * FORM FIELD DEFAULTS
 *
 * Default values and behaviors for form fields.
 */
export const FORM_FIELD_DEFAULTS = {
  // Initial values
  initialValue: '',

  // Behavior settings
  trimWhitespace: true,
  validateOnChange: false,
  validateOnBlur: true,

  // Auto-formatting
  autoCapitalize: false,
  autoComplete: true,
} as const;

/**
 * VALIDATION RULES LIBRARY
 *
 * Pre-defined validation rules for common form fields.
 * Copy and modify these for custom validation needs.
 */
export const VALIDATION_RULES = {
  // Contact form fields
  name: {
    required: true,
    minLength: 2,
    maxLength: 100,
    pattern: /^[a-zA-Z\s'-]+$/,
  } as ValidationRule,

  email: {
    required: true,
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  } as ValidationRule,

  phone: {
    required: false,
    pattern: /^[\+]?[1-9][\d]{0,15}$/,
  } as ValidationRule,

  subject: {
    required: true,
    minLength: 5,
    maxLength: 250,
  } as ValidationRule,

  message: {
    required: true,
    minLength: 10,
    maxLength: 10000,
  } as ValidationRule,

  domain: {
    required: false,
    pattern: /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/,
  } as ValidationRule,
} as const;

/**
 * FORM BEHAVIOR CONFIGURATION
 *
 * Controls how forms behave throughout the application.
 */
export const FORM_BEHAVIOR_CONFIG = {
  // Submission settings
  preventDoubleSubmit: true,
  showSubmissionFeedback: true,
  resetAfterSubmit: false,

  // Validation settings
  validateOnSubmit: true,
  stopOnFirstError: false,
  showInlineErrors: true,

  // Auto-save integration
  enableAutoSave: true,
  autoSaveDelay: 1000, // milliseconds

  // Character counting
  showCharacterCount: true,
  warnAtPercentage: 90, // Show warning at 90% of limit
} as const;

/**
 * DEFAULT NOTIFICATION HELPERS
 *
 * Fallback notification functions when none are provided.
 * These use console logging in development.
 */
export const DEFAULT_NOTIFICATION_HELPERS: Required<NotificationHelpers> = {
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
