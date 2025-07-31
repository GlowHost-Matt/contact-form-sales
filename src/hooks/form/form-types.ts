/**
 * FORM HOOK TYPES AND INTERFACES
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains TypeScript type definitions and interfaces for form-related hooks.
 * Modifying this file could break the entire form hook system across the application.
 *
 * 🔧 TO MODIFY FORM HOOK SETTINGS:
 *    Go to → form-values.ts
 *    This is where you change validation rules, file handling config, and form behaviors.
 *
 * 📚 FOR UTILITY FUNCTIONS:
 *    See → form-utils.ts
 *    Contains helper functions for validation, file handling, and data processing.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break TypeScript compilation across the entire application
 *   - May cause runtime errors in form validation and file handling
 *   - Interface changes affect all forms and form components
 *   - Breaking changes propagate to every form in the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual form settings are defined
 *   - Makes the form system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in form-values.ts
 *   - Add new types following existing patterns and consulting the team
 *   - For utility functions: See form-utils.ts
 *   - Test thoroughly if type modifications are absolutely necessary
 *
 * This file defines the contracts that form hook configuration objects must follow.
 * Configuration values are defined in form-values.ts
 */

// Form field hook interface
export interface FormField {
  value: string;
  setValue: (value: string) => void;
  onChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void;
  reset: () => void;
  isDirty: boolean;
}

// File handling hook types
export interface FileHandlingState {
  uploadedFiles: File[];
  filePreviews: string[];
  fileDescriptions: string[];
  isDragging: boolean;
}

export interface FileHandlingActions {
  handleFileChange: (e: React.ChangeEvent<HTMLInputElement>) => Promise<void>;
  handleDrop: (e: React.DragEvent<HTMLDivElement>) => Promise<void>;
  handleDragEnter: (e: React.DragEvent<HTMLDivElement>) => void;
  handleDragLeave: (e: React.DragEvent<HTMLDivElement>) => void;
  handleDragOver: (e: React.DragEvent<HTMLDivElement>) => void;
  setUploadedFiles: React.Dispatch<React.SetStateAction<File[]>>;
  setFilePreviews: React.Dispatch<React.SetStateAction<string[]>>;
  setFileDescriptions: React.Dispatch<React.SetStateAction<string[]>>;
  processFiles: (files: File[]) => Promise<void>;
  removeFile: (index: number) => void;
  removeAllFiles: () => void;
}

export interface FileHandlingReturn extends FileHandlingState, FileHandlingActions {}

// Form validation types
export interface ValidationRule {
  required?: boolean;
  minLength?: number;
  maxLength?: number;
  pattern?: RegExp;
  custom?: (value: string) => boolean;
}

export interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

// Notification callback type for hooks
export type NotificationCallback = (title: string, message: string) => void;

export interface NotificationHelpers {
  showError?: NotificationCallback;
  showWarning?: NotificationCallback;
  showSuccess?: NotificationCallback;
  showInfo?: NotificationCallback;
}
