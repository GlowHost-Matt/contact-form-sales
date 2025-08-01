/**
 * UI COMPONENTS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file uses "re-exporting" to provide a single import point for all UI component functionality.
 * Modifying this file could break the entire UI component architecture.
 *

 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * WHAT IS RE-EXPORTING?
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * Re-exporting means this file imports code from other files and immediately exports
 * it again, creating a "barrel export" pattern. This allows you to import everything
 * from one convenient location:
 *
 *   import { Button, DEFAULT_BUTTON_CONFIG, ButtonProps } from '@/components/ui'
 *
 * Instead of having to import from multiple files:
 *
 *   import { Button } from '@/components/ui/Button'
 *   import { DEFAULT_BUTTON_CONFIG } from '@/components/ui/ui-values'
 *   import { ButtonProps } from '@/components/ui/ui-types'
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could create circular dependency loops
 *   - May break existing imports throughout the application
 *   - TypeScript compilation errors if export signatures change
 *   - Runtime errors if re-export paths become invalid
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of file separation
 *   - Creates confusion about where actual configurations are defined
 *   - Makes the codebase harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ❌ DEVELOPER CONFUSION:
 *   - Other developers won't know where to find actual component implementations
 *   - Mixed concerns (barrel export + actual components) in one file
 *   - Harder to understand the component architecture
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * SAFE MODIFICATION PRACTICES:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ✅ MODIFY CONFIGURATIONS: Go to ui-values.ts
 * ✅ MODIFY TYPES: Go to ui-types.ts
 * ✅ MODIFY UTILITIES: Go to ui-utils.ts
 * ✅ ADD NEW COMPONENTS: Create component file, then re-export here
 * ✅ ADD NEW EXPORTS: Add to the appropriate specialized file, then re-export here
 *
 * This file maintains backward compatibility while keeping the codebase organized.
 * You can continue importing from '@/components/ui' as before.
 *
 * CURRENT CONFIG: GlowHost-optimized (professional, accessible, responsive)
 */

// Re-export types and interfaces
export type {
  BaseUIProps,
  SizeVariant,
  ColorVariant,
  LoadingState,
  NotificationConfig,
  NotificationItem,
  NotificationAction,
  AutoSaveIndicatorConfig,
  FloatingAutoSaveIndicatorConfig,
  AttachmentConfig,
  AttachmentFile,
  FormFieldConfig,
  ValidationState,
  ModalConfig,
  ButtonConfig,
  ButtonProps,
  CardConfig,
  CardProps,
  InputConfig,
  InputProps,
  ProgressConfig,
  ProgressProps,
  UIThemeConfig,
  UIComponentConfig,
  ComponentEventHandler,
  ComponentStateUpdater,
  AccessibilityConfig,
  AriaProps,
} from './ui-types';

export type { ComponentType } from './ui-types';

// Re-export configuration values ⭐ MODIFY THESE IN ui-values.ts ⭐
export {
  DEFAULT_NOTIFICATION_CONFIG,
  DEFAULT_AUTO_SAVE_INDICATOR_CONFIG,
  DEFAULT_FLOATING_AUTO_SAVE_INDICATOR_CONFIG,
  DEFAULT_ATTACHMENT_CONFIG,
  DEFAULT_FORM_FIELD_CONFIG,
  DEFAULT_MODAL_CONFIG,
  DEFAULT_BUTTON_CONFIG,
  DEFAULT_CARD_CONFIG,
  DEFAULT_INPUT_CONFIG,
  DEFAULT_PROGRESS_CONFIG,
  DEFAULT_UI_THEME_CONFIG,
  DEFAULT_ACCESSIBILITY_CONFIG,
  DEFAULT_UI_COMPONENT_CONFIG,
  EXAMPLE_UI_CONFIGS,
  BRAND_COLORS,
  SIZE_SCALES,
  COMPONENT_TYPES,
} from './ui-values';

// Re-export utility functions
export {
  cn,
  getComponentClasses,
  getVariantClasses,
  getSizeClasses,
  getSpacingClasses,
  generateNotificationId,
  createNotification,
  getNotificationPositionClasses,
  createValidationState,
  validateRequired,
  validateEmail,
  validateMinLength,
  validateMaxLength,
  combineValidationStates,
  formatFileSize,
  isFileTypeAllowed,
  isFileSizeValid,
  getFileCategory,
  createAttachmentFile,
  createFilePreview,
  generateAccessibleId,
  createAriaProps,
  prefersReducedMotion,
  getKeyboardKey,
  getDeviceType,
  isTouchDevice,
  getSafeAreaInsets,
  createTransition,
  debounce,
  throttle,
  hexToRgb,
  getContrastRatio,
  createDisplayName,
  generateComponentKey,
  mergeConfigs,
} from './ui-utils';

// Re-export actual component files
export { Attachments } from './Attachments';
export { AutoSaveIndicator, AutoSaveStatusBar, FloatingAutoSaveIndicator } from './AutoSaveIndicator';
export { ConfirmationPage } from './ConfirmationPage';
export { TicketThread } from './TicketThread';
export { NotificationContainer, useNotificationHelpers } from './notification';
export { NotificationProvider, useNotifications } from './notification-context';

// Convenience functions that use the default UI configuration
import {
  DEFAULT_UI_COMPONENT_CONFIG,
  DEFAULT_NOTIFICATION_CONFIG,
  DEFAULT_BUTTON_CONFIG,
} from './ui-values';
import { createNotification, getComponentClasses, mergeConfigs } from './ui-utils';
import type { NotificationItem, UIComponentConfig } from './ui-types';

// Create notification with default config
export const createDefaultNotification = (
  title: string,
  message: string,
  type: NotificationItem['type'] = 'info'
): NotificationItem => {
  return createNotification(title, message, type, {
    duration: DEFAULT_NOTIFICATION_CONFIG.defaultDuration,
  });
};

// Get component classes with default theme
export const getThemedComponentClasses = (
  baseClasses: string,
  variant?: string,
  size?: string,
  additionalClasses?: string
): string => {
  return getComponentClasses(
    baseClasses,
    variant as any,
    size as any,
    additionalClasses
  );
};

// Merge user config with defaults
export const createUIConfig = (userConfig: Partial<UIComponentConfig> = {}): UIComponentConfig => {
  return mergeConfigs(DEFAULT_UI_COMPONENT_CONFIG, userConfig);
};

// Component display name helpers for debugging
export const UI_COMPONENT_NAMES = {
  ATTACHMENTS: 'Attachments',
  AUTO_SAVE_INDICATOR: 'AutoSaveIndicator',
  AUTO_SAVE_STATUS_BAR: 'AutoSaveStatusBar',
  CONFIRMATION_PAGE: 'ConfirmationPage',
  TICKET_THREAD: 'TicketThread',
  NOTIFICATION_CONTAINER: 'NotificationContainer',
  NOTIFICATION_PROVIDER: 'NotificationProvider',
} as const;

// Version info for debugging and development
export const UI_MODULE_INFO = {
  version: '1.0.0',
  architecture: 'modular-barrel-export',
  configurable: true,
  themeable: true,
  accessible: true,
  responsive: true,
} as const;
