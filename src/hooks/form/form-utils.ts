/**
 * FORM HOOK UTILITY FUNCTIONS
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains helper functions for form-related hooks.
 * Modifying this file could break form functionality across the entire application.
 *
 * 🔧 TO MODIFY FORM HOOK SETTINGS:
 *    Go to → form-values.ts
 *    This is where you change validation rules, file handling config, and form behaviors.
 *
 * 📚 FOR TYPE DEFINITIONS:
 *    See → form-types.ts
 *    Contains interfaces and type definitions for form hooks.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break form validation across all forms
 *   - May cause file upload failures and data loss
 *   - Function signature changes affect all form components
 *   - Breaking changes propagate throughout the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual validation rules are defined
 *   - Makes the form system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in form-values.ts
 *   - Add new utility functions following existing patterns
 *   - Consult with the development team before function changes
 *   - Test thoroughly if utility modifications are absolutely necessary
 *
 * These utilities support form validation, file handling, and data processing.
 * For actual configuration values, see form-values.ts
 */

import type { ValidationRule, ValidationResult } from './form-types';
import { VALIDATION_RULES, FILE_HANDLING_CONFIG } from './form-values';

/**
 * VALIDATION UTILITIES
 */

// Validate a single field against a rule
export const validateField = (value: string, rule: ValidationRule): ValidationResult => {
  const errors: string[] = [];

  // Required validation
  if (rule.required && !value.trim()) {
    errors.push('This field is required');
  }

  // Skip other validations if field is empty and not required
  if (!value.trim() && !rule.required) {
    return { isValid: true, errors: [] };
  }

  // Length validations
  if (rule.minLength && value.length < rule.minLength) {
    errors.push(`Must be at least ${rule.minLength} characters`);
  }

  if (rule.maxLength && value.length > rule.maxLength) {
    errors.push(`Must be no more than ${rule.maxLength} characters`);
  }

  // Pattern validation
  if (rule.pattern && !rule.pattern.test(value)) {
    errors.push('Invalid format');
  }

  // Custom validation
  if (rule.custom && !rule.custom(value)) {
    errors.push('Invalid value');
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
};

// Validate multiple fields at once
export const validateForm = (
  formData: Record<string, string>,
  rules: Record<string, ValidationRule>
): Record<string, ValidationResult> => {
  const results: Record<string, ValidationResult> = {};

  for (const [fieldName, value] of Object.entries(formData)) {
    const rule = rules[fieldName];
    if (rule) {
      results[fieldName] = validateField(value, rule);
    }
  }

  return results;
};

// Check if entire form is valid
export const isFormValid = (validationResults: Record<string, ValidationResult>): boolean => {
  return Object.values(validationResults).every(result => result.isValid);
};

/**
 * FILE HANDLING UTILITIES
 */

// Format file size for display
export const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Check if file type is allowed
export const isFileTypeAllowed = (filename: string): boolean => {
  const extension = filename.toLowerCase().substring(filename.lastIndexOf('.'));
  const { allowedExtensions } = FILE_HANDLING_CONFIG;

  return (
    allowedExtensions.images.includes(extension as any) ||
    allowedExtensions.documents.includes(extension as any) ||
    allowedExtensions.archives.includes(extension as any)
  );
};

// Check if file size is within limits
export const isFileSizeValid = (fileSize: number): boolean => {
  return fileSize <= FILE_HANDLING_CONFIG.maxFileSize;
};

// Get file type category
export const getFileCategory = (filename: string): 'image' | 'document' | 'archive' | 'unknown' => {
  const extension = filename.toLowerCase().substring(filename.lastIndexOf('.'));
  const { allowedExtensions } = FILE_HANDLING_CONFIG;

  if (allowedExtensions.images.includes(extension as any)) return 'image';
  if (allowedExtensions.documents.includes(extension as any)) return 'document';
  if (allowedExtensions.archives.includes(extension as any)) return 'archive';
  return 'unknown';
};

// Create file preview (for images)
export const createFilePreview = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    if (!file.type.startsWith('image/')) {
      resolve(''); // No preview for non-images
      return;
    }

    const reader = new FileReader();
    reader.onload = (e: ProgressEvent<FileReader>) => {
      resolve((e.target?.result as string) || '');
    };
    reader.onerror = () => reject(new Error('Failed to read file'));
    reader.readAsDataURL(file);
  });
};

/**
 * FORM DATA UTILITIES
 */

// Clean and format form data
export const cleanFormData = (formData: Record<string, string>): Record<string, string> => {
  const cleaned: Record<string, string> = {};

  for (const [key, value] of Object.entries(formData)) {
    // Trim whitespace and normalize
    cleaned[key] = value.trim();
  }

  return cleaned;
};

// Get character count with formatting
export const getCharacterCount = (text: string, maxLength?: number): {
  count: number;
  remaining?: number;
  percentage?: number;
  isNearLimit?: boolean;
} => {
  const count = text.length;

  if (!maxLength) {
    return { count };
  }

  const remaining = maxLength - count;
  const percentage = (count / maxLength) * 100;
  const isNearLimit = percentage >= 90;

  return {
    count,
    remaining,
    percentage,
    isNearLimit,
  };
};

// Generate form field ID
export const generateFieldId = (formName: string, fieldName: string): string => {
  return `${formName}-${fieldName}`;
};

/**
 * PRE-DEFINED VALIDATION HELPERS
 *
 * Quick access to common validation rules.
 */
export const getValidationRule = (fieldType: keyof typeof VALIDATION_RULES): ValidationRule => {
  return VALIDATION_RULES[fieldType];
};

// Validate common form fields with pre-defined rules
export const validateCommonField = (fieldType: keyof typeof VALIDATION_RULES, value: string): ValidationResult => {
  const rule = getValidationRule(fieldType);
  return validateField(value, rule);
};
