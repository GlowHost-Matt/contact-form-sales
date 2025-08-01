/**
 * UI COMPONENT UTILITY FUNCTIONS
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains helper functions for UI components.
 * Modifying this file could break component styling and functionality across the entire application.
 *

 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break component styling and class name generation
 *   - May cause notification systems and form validation to fail
 *   - Function signature changes affect all UI components
 *   - Breaking changes propagate throughout the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual styling settings are defined
 *   - Makes the UI system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in ui-values.ts
 *   - Add new utility functions following existing patterns
 *   - Consult with the development team before function changes
 *   - Test thoroughly if utility modifications are absolutely necessary
 *
 * These utilities support component styling, event handling, and state management.
 * For actual configuration values, see ui-values.ts
 */

import type {
  SizeVariant,
  ColorVariant,
  NotificationItem,
  AttachmentFile,
  ValidationState,
  ComponentType,
  AriaProps,
} from './ui-types';
import { BRAND_COLORS, SIZE_SCALES, DEFAULT_UI_COMPONENT_CONFIG } from './ui-values';

/**
 * CLASS NAME UTILITIES
 */

// Combine class names with proper handling of conditionals
export const cn = (...classes: (string | undefined | null | false)[]): string => {
  return classes.filter(Boolean).join(' ');
};

// Generate component class names based on variant and size
export const getComponentClasses = (
  baseClasses: string,
  variant?: ColorVariant,
  size?: SizeVariant,
  additionalClasses?: string
): string => {
  const variantClasses = variant ? getVariantClasses(variant) : '';
  const sizeClasses = size ? getSizeClasses(size) : '';

  return cn(baseClasses, variantClasses, sizeClasses, additionalClasses);
};

// Get color variant classes
export const getVariantClasses = (variant: ColorVariant): string => {
  const variantMap: Record<ColorVariant, string> = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 border-blue-600',
    secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 border-gray-300',
    success: 'bg-green-600 text-white hover:bg-green-700 border-green-600',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600 border-yellow-500',
    error: 'bg-red-600 text-white hover:bg-red-700 border-red-600',
    info: 'bg-blue-500 text-white hover:bg-blue-600 border-blue-500',
    neutral: 'bg-gray-500 text-white hover:bg-gray-600 border-gray-500',
  };

  return variantMap[variant] || variantMap.neutral;
};

// Get size variant classes
export const getSizeClasses = (size: SizeVariant): string => {
  const sizeMap: Record<SizeVariant, string> = {
    xs: 'px-2 py-1 text-xs',
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
    xl: 'px-8 py-4 text-xl',
  };

  return sizeMap[size] || sizeMap.md;
};

// Get spacing classes based on size
export const getSpacingClasses = (size: SizeVariant): string => {
  const spacingMap: Record<SizeVariant, string> = {
    xs: 'p-1 gap-1',
    sm: 'p-2 gap-2',
    md: 'p-3 gap-3',
    lg: 'p-4 gap-4',
    xl: 'p-6 gap-6',
  };

  return spacingMap[size] || spacingMap.md;
};

/**
 * NOTIFICATION UTILITIES
 */

// Generate unique notification ID
export const generateNotificationId = (): string => {
  return `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
};

// Create notification item with defaults
export const createNotification = (
  title: string,
  message: string,
  type: NotificationItem['type'] = 'info',
  options: Partial<Pick<NotificationItem, 'duration' | 'persistent' | 'actions'>> = {}
): NotificationItem => {
  return {
    id: generateNotificationId(),
    title,
    message,
    type,
    duration: options.duration || DEFAULT_UI_COMPONENT_CONFIG.notification.defaultDuration,
    persistent: options.persistent || false,
    actions: options.actions || [],
    timestamp: Date.now(),
  };
};

// Calculate notification position styles
export const getNotificationPositionClasses = (
  position: string
): string => {
  const positionMap: Record<string, string> = {
    'top-left': 'top-4 left-4',
    'top-right': 'top-4 right-4',
    'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
    'bottom-left': 'bottom-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
  };

  return positionMap[position] || positionMap['bottom-right'];
};

/**
 * FORM VALIDATION UTILITIES
 */

// Create validation state object
export const createValidationState = (
  isValid = true,
  errors: string[] = [],
  warnings: string[] = [],
  isDirty = false,
  isTouched = false
): ValidationState => {
  return {
    isValid,
    isDirty,
    isTouched,
    errors,
    warnings,
  };
};

// Validate required field
export const validateRequired = (value: string | undefined | null): ValidationState => {
  const isEmpty = !value || value.toString().trim().length === 0;
  return createValidationState(
    !isEmpty,
    isEmpty ? ['This field is required'] : []
  );
};

// Validate email format
export const validateEmail = (email: string): ValidationState => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const isValid = emailRegex.test(email);
  return createValidationState(
    isValid,
    isValid ? [] : ['Please enter a valid email address']
  );
};

// Validate minimum length
export const validateMinLength = (value: string, minLength: number): ValidationState => {
  const isValid = value.length >= minLength;
  return createValidationState(
    isValid,
    isValid ? [] : [`Must be at least ${minLength} characters`]
  );
};

// Validate maximum length
export const validateMaxLength = (value: string, maxLength: number): ValidationState => {
  const isValid = value.length <= maxLength;
  const warnings = value.length > maxLength * 0.9 ? [`Approaching character limit (${value.length}/${maxLength})`] : [];

  return createValidationState(
    isValid,
    isValid ? [] : [`Must be no more than ${maxLength} characters`],
    warnings
  );
};

// Combine multiple validation results
export const combineValidationStates = (states: ValidationState[]): ValidationState => {
  const allErrors = states.flatMap(state => state.errors);
  const allWarnings = states.flatMap(state => state.warnings);
  const isValid = states.every(state => state.isValid);
  const isDirty = states.some(state => state.isDirty);
  const isTouched = states.some(state => state.isTouched);

  return createValidationState(isValid, allErrors, allWarnings, isDirty, isTouched);
};

/**
 * FILE ATTACHMENT UTILITIES
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
export const isFileTypeAllowed = (file: File, allowedTypes: string[]): boolean => {
  return allowedTypes.includes(file.type) || allowedTypes.includes('*');
};

// Check if file size is within limits
export const isFileSizeValid = (file: File, maxSize: number): boolean => {
  return file.size <= maxSize;
};

// Get file category for icon display
export const getFileCategory = (file: File): 'image' | 'document' | 'archive' | 'other' => {
  if (file.type.startsWith('image/')) return 'image';
  if (file.type === 'application/pdf' || file.type.startsWith('text/')) return 'document';
  if (file.type.includes('zip') || file.type.includes('rar')) return 'archive';
  return 'other';
};

// Generate attachment file object
export const createAttachmentFile = (file: File): AttachmentFile => {
  return {
    id: `file-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    file,
    status: 'pending',
    uploadProgress: 0,
  };
};

// Create file preview URL for images
export const createFilePreview = async (file: File): Promise<string | null> => {
  if (!file.type.startsWith('image/')) return null;

  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => resolve(e.target?.result as string || null);
    reader.onerror = () => resolve(null);
    reader.readAsDataURL(file);
  });
};

/**
 * ACCESSIBILITY UTILITIES
 */

// Generate accessible ID for form elements
export const generateAccessibleId = (prefix: string): string => {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
};

// Create ARIA props for better accessibility
export const createAriaProps = (
  label?: string,
  describedBy?: string,
  live?: 'off' | 'polite' | 'assertive'
): AriaProps => {
  const props: AriaProps = {};

  if (label) props['aria-label'] = label;
  if (describedBy) props['aria-describedby'] = describedBy;
  if (live) props['aria-live'] = live;

  return props;
};

// Check if user prefers reduced motion
export const prefersReducedMotion = (): boolean => {
  if (typeof window === 'undefined') return false;
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

// Get keyboard event key in a consistent way
export const getKeyboardKey = (event: KeyboardEvent): string => {
  return event.key || event.keyCode.toString();
};

/**
 * RESPONSIVE UTILITIES
 */

// Get device type based on screen width
export const getDeviceType = (): 'mobile' | 'tablet' | 'desktop' => {
  if (typeof window === 'undefined') return 'desktop';

  const width = window.innerWidth;
  if (width < 768) return 'mobile';
  if (width < 1024) return 'tablet';
  return 'desktop';
};

// Check if device supports touch
export const isTouchDevice = (): boolean => {
  if (typeof window === 'undefined') return false;
  return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
};

// Get safe area insets for mobile devices
export const getSafeAreaInsets = (): {
  top: number;
  right: number;
  bottom: number;
  left: number;
} => {
  if (typeof window === 'undefined') {
    return { top: 0, right: 0, bottom: 0, left: 0 };
  }

  const style = getComputedStyle(document.documentElement);
  return {
    top: parseInt(style.getPropertyValue('env(safe-area-inset-top)')) || 0,
    right: parseInt(style.getPropertyValue('env(safe-area-inset-right)')) || 0,
    bottom: parseInt(style.getPropertyValue('env(safe-area-inset-bottom)')) || 0,
    left: parseInt(style.getPropertyValue('env(safe-area-inset-left)')) || 0,
  };
};

/**
 * ANIMATION UTILITIES
 */

// Create CSS transition string
export const createTransition = (
  properties: string[] = ['all'],
  duration: number = 200,
  easing: string = 'ease-out'
): string => {
  return properties.map(prop => `${prop} ${duration}ms ${easing}`).join(', ');
};

// Debounce function for performance optimization
export const debounce = <T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void => {
  let timeout: NodeJS.Timeout;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

// Throttle function for performance optimization
export const throttle = <T extends (...args: any[]) => any>(
  func: T,
  limit: number
): (...args: Parameters<T>) => void => {
  let inThrottle: boolean;
  return (...args: Parameters<T>) => {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
};

/**
 * COLOR UTILITIES
 */

// Convert hex color to RGB
export const hexToRgb = (hex: string): { r: number; g: number; b: number } | null => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16),
  } : null;
};

// Get contrast ratio between two colors
export const getContrastRatio = (color1: string, color2: string): number => {
  const rgb1 = hexToRgb(color1);
  const rgb2 = hexToRgb(color2);

  if (!rgb1 || !rgb2) return 1;

  const getLuminance = (r: number, g: number, b: number) => {
    const [rs, gs, bs] = [r, g, b].map(c => {
      c = c / 255;
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
  };

  const lum1 = getLuminance(rgb1.r, rgb1.g, rgb1.b);
  const lum2 = getLuminance(rgb2.r, rgb2.g, rgb2.b);

  const brightest = Math.max(lum1, lum2);
  const darkest = Math.min(lum1, lum2);

  return (brightest + 0.05) / (darkest + 0.05);
};

/**
 * COMPONENT STATE UTILITIES
 */

// Create component display name for debugging
export const createDisplayName = (componentName: string, type?: ComponentType): string => {
  return type ? `${componentName}.${type}` : componentName;
};

// Generate stable component key for React reconciliation
export const generateComponentKey = (prefix: string, id?: string | number): string => {
  return id ? `${prefix}-${id}` : `${prefix}-${Date.now()}`;
};

// Deep merge configuration objects
export const mergeConfigs = <T extends Record<string, any>>(
  defaultConfig: T,
  userConfig: Partial<T>
): T => {
  const result = { ...defaultConfig };

  for (const key in userConfig) {
    if (userConfig[key] !== undefined) {
      if (typeof userConfig[key] === 'object' && userConfig[key] !== null && !Array.isArray(userConfig[key])) {
        result[key] = mergeConfigs(result[key] || ({} as any), userConfig[key]);
      } else {
        result[key] = userConfig[key] as T[Extract<keyof T, string>];
      }
    }
  }

  return result;
};
