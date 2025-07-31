import type { FormData, FormValidation } from '@/types';
import { FORM_VALIDATION, VALIDATION_MESSAGES } from '../../config/features.config';

/**
 * Validate a single form field
 */
export function validateField(name: string, value: string): string | null {
  // Check if field is required
  if (FORM_VALIDATION.REQUIRED_FIELDS.includes(name as any) && !value.trim()) {
    return VALIDATION_MESSAGES.REQUIRED;
  }

  // Skip other validations if field is empty and not required
  if (!value.trim()) return null;

  // Email validation
  if (name === 'email' && !FORM_VALIDATION.EMAIL_REGEX.test(value)) {
    return VALIDATION_MESSAGES.EMAIL_INVALID;
  }

  // Phone validation (if provided)
  if (name === 'phone' && value.trim() && !FORM_VALIDATION.PHONE_REGEX.test(value.replace(/[\s\-\(\)]/g, ''))) {
    return VALIDATION_MESSAGES.PHONE_INVALID;
  }

  // Length validations
  const minLength = FORM_VALIDATION.MIN_LENGTHS[name as keyof typeof FORM_VALIDATION.MIN_LENGTHS];
  if (minLength && value.trim().length < minLength) {
    return VALIDATION_MESSAGES.TOO_SHORT(name, minLength);
  }

  return null;
}

/**
 * Validate entire form
 */
export function validateForm(formData: FormData): FormValidation {
  const errors: Record<string, string> = {};

  // Validate each field
  Object.entries(formData).forEach(([key, value]) => {
    if (typeof value === 'string') {
      const error = validateField(key, value);
      if (error) {
        errors[key] = error;
      }
    }
  });

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  };
}

/**
 * Check if form has minimum required data for submission
 */
export function isFormValid(formData: FormData): boolean {
  return FORM_VALIDATION.REQUIRED_FIELDS.every(field =>
    formData[field as keyof FormData]?.toString().trim()
  );
}

/**
 * Validate alphanumeric input (for file descriptions)
 */
export function validateAlphanumeric(value: string): string {
  return value.replace(/[^a-zA-Z0-9\s]/g, '');
}

/**
 * Clean phone number for validation
 */
export function cleanPhoneNumber(phone: string): string {
  return phone.replace(/[\s\-\(\)]/g, '');
}

/**
 * Format character count display
 */
export function getCharacterCountStyle(current: number, max: number): string {
  const percentage = (current / max) * 100;

  if (percentage >= 100) return 'text-red-500';
  if (percentage >= 90) return 'text-orange-500';
  if (percentage >= 75) return 'text-yellow-600';
  return 'text-gray-500';
}
