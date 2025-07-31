/**
 * FORM HOOKS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file uses "re-exporting" to provide a single import point for all form hook functionality.
 * Modifying this file could break the entire form hook architecture.
 *

 *
 * This file maintains backward compatibility by re-exporting everything.
 * You can import from '@/hooks/form' for all form-related functionality.
 */

// Re-export types and interfaces
export type {
  FormField,
  FileHandlingState,
  FileHandlingActions,
  FileHandlingReturn,
  ValidationRule,
  ValidationResult,
  NotificationCallback,
  NotificationHelpers,
} from './form-types';

// Re-export configuration values ⭐ MODIFY THESE IN form-values.ts ⭐
export {
  FILE_HANDLING_CONFIG,
  FORM_FIELD_DEFAULTS,
  VALIDATION_RULES,
  FORM_BEHAVIOR_CONFIG,
  DEFAULT_NOTIFICATION_HELPERS,
} from './form-values';

// Re-export utility functions
export {
  validateField,
  validateForm,
  isFormValid,
  formatFileSize,
  isFileTypeAllowed,
  isFileSizeValid,
  getFileCategory,
  createFilePreview,
  cleanFormData,
  getCharacterCount,
  generateFieldId,
  getValidationRule,
  validateCommonField,
} from './form-utils';

// Re-export actual hooks (will be moved here)
export { useFormField } from '../useFormField';
export { useFileHandling } from '../useFileHandling';
