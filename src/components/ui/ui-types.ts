/**
 * UI COMPONENT TYPES AND INTERFACES
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains TypeScript type definitions and interfaces for UI components.
 * Modifying this file could break the entire UI component system across the application.
 *
 * 🔧 TO MODIFY UI COMPONENT SETTINGS:
 *    Go to → ui-values.ts
 *    This is where you change component behavior, styling, and default props.
 *
 * 📚 FOR UTILITY FUNCTIONS:
 *    See → ui-utils.ts
 *    Contains helper functions for styling, event handling, and state management.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break TypeScript compilation across the entire application
 *   - May cause runtime errors in component rendering and styling
 *   - Interface changes affect all components using these types
 *   - Breaking changes propagate throughout the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual component settings are defined
 *   - Makes the UI system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in ui-values.ts
 *   - Add new types following existing patterns and consulting the team
 *   - For utility functions: See ui-utils.ts
 *   - Test thoroughly if type modifications are absolutely necessary
 *
 * This file defines the contracts that UI component configuration objects must follow.
 * Configuration values are defined in ui-values.ts
 */

import type { ReactNode } from 'react';

// Base component props that all UI components should support
export interface BaseUIProps {
  className?: string;
  children?: ReactNode;
  id?: string;
  'data-testid'?: string;
}

// Size variants used across multiple components
export type SizeVariant = 'xs' | 'sm' | 'md' | 'lg' | 'xl';

// Color variants for components
export type ColorVariant = 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'info' | 'neutral';

// Component loading states
export type LoadingState = 'idle' | 'loading' | 'success' | 'error';

// Notification system types
export interface NotificationConfig {
  position: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' | 'top-center' | 'bottom-center';
  maxNotifications: number;
  defaultDuration: number;
  enableAnimation: boolean;
  enableSound: boolean;
  persistOnHover: boolean;
}

export interface NotificationItem {
  id: string;
  title: string;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  duration?: number;
  persistent?: boolean;
  actions?: NotificationAction[];
  timestamp: number;
}

export interface NotificationAction {
  label: string;
  onClick: () => void;
  variant?: 'primary' | 'secondary';
}

// Auto-save indicator types
export interface AutoSaveIndicatorConfig {
  showText: boolean;
  size: SizeVariant;
  position: 'inline' | 'floating' | 'status-bar';
  enableAnimation: boolean;
  statusDuration: number;
}

// Floating auto-save indicator types
export interface FloatingAutoSaveIndicatorConfig {
  enabled: boolean;
  position: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
  showDuration: number; // milliseconds to show 'saved' status
  enableAnimation: boolean;
  enableBackdropBlur: boolean;
  enableShadow: boolean;
  spacing: {
    mobile: number; // pixels from edge on mobile
    desktop: number; // pixels from edge on desktop
  };
  zIndex: number;
  hideOnIdle: boolean; // hide when status is 'idle'
  showText: boolean;
}

// Attachment system types
export interface AttachmentConfig {
  maxFileSize: number; // bytes
  maxFiles: number;
  allowedTypes: string[];
  enablePreview: boolean;
  enableDragDrop: boolean;
  enableDescriptions: boolean;
}

export interface AttachmentFile {
  id: string;
  file: File;
  preview?: string;
  description?: string;
  uploadProgress?: number;
  status: 'pending' | 'uploading' | 'completed' | 'error';
  error?: string;
}

// Form components types
export interface FormFieldConfig {
  showCharacterCount: boolean;
  enableValidation: boolean;
  validateOnChange: boolean;
  validateOnBlur: boolean;
  showRequiredIndicator: boolean;
  requiredIndicator: string;
}

export interface ValidationState {
  isValid: boolean;
  isDirty: boolean;
  isTouched: boolean;
  errors: string[];
  warnings: string[];
}

// Modal and dialog types
export interface ModalConfig {
  enableBackdropClick: boolean;
  enableEscapeKey: boolean;
  showCloseButton: boolean;
  size: 'sm' | 'md' | 'lg' | 'xl' | 'fullscreen';
  centered: boolean;
  enableAnimation: boolean;
}

// Button component types
export interface ButtonConfig {
  defaultVariant: ColorVariant;
  defaultSize: SizeVariant;
  enableRippleEffect: boolean;
  enableFocusRing: boolean;
  loadingSpinnerType: 'spinner' | 'dots' | 'pulse';
}

export interface ButtonProps extends BaseUIProps {
  variant?: ColorVariant;
  size?: SizeVariant;
  loading?: boolean;
  disabled?: boolean;
  fullWidth?: boolean;
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
  icon?: ReactNode;
  iconPosition?: 'left' | 'right';
}

// Card component types
export interface CardConfig {
  defaultShadow: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  defaultBorder: boolean;
  defaultPadding: SizeVariant;
  enableHoverEffects: boolean;
}

export interface CardProps extends BaseUIProps {
  shadow?: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  border?: boolean;
  padding?: SizeVariant;
  clickable?: boolean;
  onClick?: () => void;
}

// Input component types
export interface InputConfig {
  defaultSize: SizeVariant;
  showFocusRing: boolean;
  enableClearButton: boolean;
  enablePasswordToggle: boolean;
  debounceDelay: number;
}

export interface InputProps extends BaseUIProps {
  type?: string;
  value?: string;
  defaultValue?: string;
  placeholder?: string;
  size?: SizeVariant;
  disabled?: boolean;
  readOnly?: boolean;
  required?: boolean;
  error?: string;
  label?: string;
  helpText?: string;
  prefix?: ReactNode;
  suffix?: ReactNode;
  onChange?: (value: string) => void;
  onBlur?: () => void;
  onFocus?: () => void;
}

// Progress indicator types
export interface ProgressConfig {
  defaultSize: SizeVariant;
  enableAnimation: boolean;
  showPercentage: boolean;
  colorScheme: ColorVariant;
}

export interface ProgressProps extends BaseUIProps {
  value: number;
  max?: number;
  size?: SizeVariant;
  color?: ColorVariant;
  showValue?: boolean;
  label?: string;
}

// Theme and styling configuration
export interface UIThemeConfig {
  colorScheme: 'light' | 'dark' | 'auto';
  primaryColor: string;
  fontFamily: string;
  borderRadius: 'none' | 'sm' | 'md' | 'lg' | 'full';
  animation: {
    duration: 'fast' | 'normal' | 'slow';
    easing: 'linear' | 'ease' | 'ease-in' | 'ease-out' | 'ease-in-out';
  };
  spacing: {
    unit: number; // base spacing unit in pixels
    scale: number[]; // multiplier array for spacing scale
  };
}

// Component configuration registry
export interface UIComponentConfig {
  notification: NotificationConfig;
  autoSaveIndicator: AutoSaveIndicatorConfig;
  floatingAutoSaveIndicator: FloatingAutoSaveIndicatorConfig;
  attachment: AttachmentConfig;
  formField: FormFieldConfig;
  modal: ModalConfig;
  button: ButtonConfig;
  card: CardConfig;
  input: InputConfig;
  progress: ProgressConfig;
  theme: UIThemeConfig;
}

// Event handler types for components
export type ComponentEventHandler<T = any> = (event: T) => void;
export type ComponentStateUpdater<T> = (newState: T | ((prevState: T) => T)) => void;

// Accessibility types
export interface AccessibilityConfig {
  enableKeyboardNavigation: boolean;
  enableScreenReader: boolean;
  enableHighContrast: boolean;
  enableReducedMotion: boolean;
  enableFocusIndicators: boolean;
}

export interface AriaProps {
  'aria-label'?: string;
  'aria-labelledby'?: string;
  'aria-describedby'?: string;
  'aria-expanded'?: boolean;
  'aria-hidden'?: boolean;
  'aria-live'?: 'off' | 'polite' | 'assertive';
  'aria-atomic'?: boolean;
  role?: string;
}

// Component type string literal (constants defined in ui-values.ts)
export type ComponentType = 'notification' | 'autoSaveIndicator' | 'attachment' | 'formField' | 'modal' | 'button' | 'card' | 'input' | 'progress';
