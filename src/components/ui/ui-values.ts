/**
 * UI COMPONENT CONFIGURATION VALUES
 *
 * ⭐ THIS IS WHERE YOU MODIFY UI COMPONENT SETTINGS ⭐
 *
 * This file contains the actual configuration data for UI components.
 * To change component behavior, styling, or default props, modify the values in this file.
 *
 * CURRENT CONFIG: GlowHost-optimized (professional, accessible, responsive)
 * DESIGN SYSTEM: Modern, clean, consistent with brand colors
 *
 * FOR TYPE DEFINITIONS: See ui-types.ts
 * FOR HELPER FUNCTIONS: See ui-utils.ts
 */

import type {
  UIComponentConfig,
  NotificationConfig,
  AutoSaveIndicatorConfig,
  FloatingAutoSaveIndicatorConfig,
  AttachmentConfig,
  FormFieldConfig,
  ModalConfig,
  ButtonConfig,
  CardConfig,
  InputConfig,
  ProgressConfig,
  UIThemeConfig,
  AccessibilityConfig,
} from './ui-types';

/**
 * NOTIFICATION SYSTEM CONFIGURATION
 *
 * Controls toast notifications, alerts, and user feedback messages.
 * Optimized for non-intrusive user experience.
 */
export const DEFAULT_NOTIFICATION_CONFIG: NotificationConfig = {
  position: 'bottom-right',           // Less intrusive than top
  maxNotifications: 5,                // Prevent notification spam
  defaultDuration: 5000,              // 5 seconds - enough time to read
  enableAnimation: true,              // Smooth user experience
  enableSound: false,                 // No audio by default (web accessibility)
  persistOnHover: true,               // User-friendly interaction
};

/**
 * AUTO-SAVE INDICATOR CONFIGURATION
 *
 * Controls how auto-save status is displayed to users.
 * Optimized for subtle, non-distracting feedback.
 */
export const DEFAULT_AUTO_SAVE_INDICATOR_CONFIG: AutoSaveIndicatorConfig = {
  showText: true,                     // Clear status messages
  size: 'sm',                         // Compact, non-intrusive
  position: 'inline',                 // Contextual positioning
  enableAnimation: true,              // Visual feedback for status changes
  statusDuration: 2500,               // Quick feedback, doesn't linger
};

/**
 * FLOATING AUTO-SAVE INDICATOR CONFIGURATION
 *
 * Controls the floating auto-save indicator that stays visible at all times.
 * Optimized for always-visible feedback without scrolling.
 */
export const DEFAULT_FLOATING_AUTO_SAVE_INDICATOR_CONFIG: FloatingAutoSaveIndicatorConfig = {
  enabled: true,                      // Enable floating indicator
  position: 'bottom-right',           // Less intrusive position
  showDuration: 3000,                 // Show 'saved' status for 3 seconds
  enableAnimation: true,              // Smooth fade-in/fade-out
  enableBackdropBlur: true,           // Modern backdrop blur effect
  enableShadow: true,                 // Professional shadow for depth
  spacing: {
    mobile: 16,                       // 16px from edge on mobile
    desktop: 24,                      // 24px from edge on desktop
  },
  zIndex: 50,                         // Above most content, below modals
  hideOnIdle: true,                   // Hide when no auto-save activity
  showText: true,                     // Show status text for clarity
};

/**
 * ATTACHMENT SYSTEM CONFIGURATION
 *
 * Controls file upload behavior and constraints.
 * Balanced between functionality and security.
 */
export const DEFAULT_ATTACHMENT_CONFIG: AttachmentConfig = {
  maxFileSize: 10 * 1024 * 1024,      // 10MB - reasonable for most files
  maxFiles: 5,                        // Prevent overwhelming uploads
  allowedTypes: [
    // Images
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    // Documents
    'application/pdf', 'text/plain', 'text/csv',
    // Archives (safe types only)
    'application/zip', 'application/x-rar-compressed',
  ],
  enablePreview: true,                // Better user experience
  enableDragDrop: true,               // Modern interaction pattern
  enableDescriptions: true,           // Context for uploaded files
};

/**
 * FORM FIELD CONFIGURATION
 *
 * Controls form input behavior and validation display.
 * Optimized for user guidance and error prevention.
 */
export const DEFAULT_FORM_FIELD_CONFIG: FormFieldConfig = {
  showCharacterCount: true,           // User guidance for limits
  enableValidation: true,             // Data quality assurance
  validateOnChange: false,            // Less intrusive validation
  validateOnBlur: true,               // Validate when user finishes field
  showRequiredIndicator: true,        // Clear field requirements
  requiredIndicator: '*',             // Standard accessibility symbol
};

/**
 * MODAL AND DIALOG CONFIGURATION
 *
 * Controls modal windows and overlay dialogs.
 * Focused on accessibility and user control.
 */
export const DEFAULT_MODAL_CONFIG: ModalConfig = {
  enableBackdropClick: true,          // Easy dismissal
  enableEscapeKey: true,              // Keyboard accessibility
  showCloseButton: true,              // Clear exit option
  size: 'md',                         // Reasonable default size
  centered: true,                     // Better visual hierarchy
  enableAnimation: true,              // Smooth transitions
};

/**
 * BUTTON COMPONENT CONFIGURATION
 *
 * Controls button appearance and behavior.
 * Professional styling with clear visual hierarchy.
 */
export const DEFAULT_BUTTON_CONFIG: ButtonConfig = {
  defaultVariant: 'primary',          // Clear primary action
  defaultSize: 'md',                  // Comfortable click target
  enableRippleEffect: false,          // Professional, not flashy
  enableFocusRing: true,              // Accessibility requirement
  loadingSpinnerType: 'spinner',      // Clear loading indication
};

/**
 * CARD COMPONENT CONFIGURATION
 *
 * Controls card containers and content sections.
 * Clean, organized content presentation.
 */
export const DEFAULT_CARD_CONFIG: CardConfig = {
  defaultShadow: 'sm',                // Subtle depth
  defaultBorder: true,                // Clear content boundaries
  defaultPadding: 'md',               // Comfortable content spacing
  enableHoverEffects: true,           // Interactive feedback
};

/**
 * INPUT COMPONENT CONFIGURATION
 *
 * Controls form input fields and text areas.
 * User-friendly with clear validation feedback.
 */
export const DEFAULT_INPUT_CONFIG: InputConfig = {
  defaultSize: 'md',                  // Comfortable input size
  showFocusRing: true,                // Accessibility requirement
  enableClearButton: false,           // Clean interface (can be enabled per field)
  enablePasswordToggle: true,         // Better password UX
  debounceDelay: 300,                 // Responsive but not excessive
};

/**
 * PROGRESS INDICATOR CONFIGURATION
 *
 * Controls progress bars and loading indicators.
 * Clear status communication.
 */
export const DEFAULT_PROGRESS_CONFIG: ProgressConfig = {
  defaultSize: 'md',                  // Visible but not overwhelming
  enableAnimation: true,              // Visual progress feedback
  showPercentage: false,              // Clean appearance (can be enabled)
  colorScheme: 'primary',             // Consistent with brand
};

/**
 * THEME AND STYLING CONFIGURATION
 *
 * Controls overall visual design system.
 * Professional, accessible, brand-consistent.
 */
export const DEFAULT_UI_THEME_CONFIG: UIThemeConfig = {
  colorScheme: 'light',               // Professional default
  primaryColor: '#1a679f',            // GlowHost brand blue
  fontFamily: 'system-ui, -apple-system, sans-serif', // Readable, system fonts
  borderRadius: 'md',                 // Modern but not excessive
  animation: {
    duration: 'normal',               // Balanced animation speed
    easing: 'ease-out',               // Natural feeling animations
  },
  spacing: {
    unit: 4,                          // 4px base unit (Tailwind standard)
    scale: [0, 1, 2, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24, 32, 40, 48, 56, 64], // Tailwind scale
  },
};

/**
 * ACCESSIBILITY CONFIGURATION
 *
 * Controls accessibility features across all components.
 * Comprehensive a11y support for inclusive design.
 */
export const DEFAULT_ACCESSIBILITY_CONFIG: AccessibilityConfig = {
  enableKeyboardNavigation: true,     // Full keyboard support
  enableScreenReader: true,           // ARIA labels and descriptions
  enableHighContrast: false,          // Optional high contrast mode
  enableReducedMotion: true,          // Respect user preferences
  enableFocusIndicators: true,        // Clear focus visualization
};

/**
 * COMPLETE UI COMPONENT CONFIGURATION
 *
 * Master configuration object that combines all component settings.
 * This is the main export that components will use.
 */
export const DEFAULT_UI_COMPONENT_CONFIG: UIComponentConfig = {
  notification: DEFAULT_NOTIFICATION_CONFIG,
  autoSaveIndicator: DEFAULT_AUTO_SAVE_INDICATOR_CONFIG,
  floatingAutoSaveIndicator: DEFAULT_FLOATING_AUTO_SAVE_INDICATOR_CONFIG,
  attachment: DEFAULT_ATTACHMENT_CONFIG,
  formField: DEFAULT_FORM_FIELD_CONFIG,
  modal: DEFAULT_MODAL_CONFIG,
  button: DEFAULT_BUTTON_CONFIG,
  card: DEFAULT_CARD_CONFIG,
  input: DEFAULT_INPUT_CONFIG,
  progress: DEFAULT_PROGRESS_CONFIG,
  theme: DEFAULT_UI_THEME_CONFIG,
};

/**
 * EXAMPLE CONFIGURATIONS FOR SPECIALIZED USE CASES
 *
 * Pre-built configuration templates for specific application scenarios.
 *
 * IMPORTANT: DO NOT MODIFY these examples directly - they serve as reference templates.
 * Instead, COPY the configuration you need and customize it for your specific requirements.
 *
 * HOW TO USE:
 * 1. Choose the configuration that best matches your use case
 * 2. Copy the entire configuration object
 * 3. Modify the copy to fit your specific needs
 * 4. Pass your custom config to UIProvider as initialConfig
 *
 * USAGE EXAMPLE:
 *   const myCustomConfig = {
 *     ...EXAMPLE_UI_CONFIGS.ADMIN_DASHBOARD_CONFIG,
 *     theme: { ...EXAMPLE_UI_CONFIGS.ADMIN_DASHBOARD_CONFIG.theme, colorScheme: 'dark' }
 *   };
 */
export const EXAMPLE_UI_CONFIGS = {
  // ADMIN DASHBOARD: High-density interface for power users
  ADMIN_DASHBOARD_CONFIG: {
    notification: { ...DEFAULT_NOTIFICATION_CONFIG, position: 'top-right' as const, maxNotifications: 10 },
    button: { ...DEFAULT_BUTTON_CONFIG, defaultSize: 'sm' as const },
    input: { ...DEFAULT_INPUT_CONFIG, defaultSize: 'sm' as const },
    card: { ...DEFAULT_CARD_CONFIG, defaultPadding: 'sm' as const },
    theme: { ...DEFAULT_UI_THEME_CONFIG, colorScheme: 'dark' as const },
  },

  // CUSTOMER PORTAL: Clean, simple interface for end users
  CUSTOMER_PORTAL_CONFIG: {
    notification: { ...DEFAULT_NOTIFICATION_CONFIG, enableAnimation: true, defaultDuration: 4000 },
    button: { ...DEFAULT_BUTTON_CONFIG, defaultSize: 'lg' as const, enableRippleEffect: true },
    input: { ...DEFAULT_INPUT_CONFIG, defaultSize: 'lg' as const },
    card: { ...DEFAULT_CARD_CONFIG, defaultShadow: 'md' as const },
    theme: { ...DEFAULT_UI_THEME_CONFIG, borderRadius: 'lg' as const },
  },

  // HIGH CONTRAST: Maximum accessibility for users with visual needs
  HIGH_CONTRAST_CONFIG: {
    notification: { ...DEFAULT_NOTIFICATION_CONFIG, enableAnimation: false },
    theme: {
      ...DEFAULT_UI_THEME_CONFIG,
      colorScheme: 'light' as const,
      primaryColor: '#000000',
      borderRadius: 'none' as const,
    },
    button: { ...DEFAULT_BUTTON_CONFIG, enableFocusRing: true },
  },

  // MOBILE OPTIMIZED: Touch-friendly interface for mobile devices
  MOBILE_OPTIMIZED_CONFIG: {
    button: { ...DEFAULT_BUTTON_CONFIG, defaultSize: 'lg' as const },
    input: { ...DEFAULT_INPUT_CONFIG, defaultSize: 'lg' as const },
    modal: { ...DEFAULT_MODAL_CONFIG, size: 'fullscreen' as const },
    attachment: { ...DEFAULT_ATTACHMENT_CONFIG, enableDragDrop: false },
    theme: { ...DEFAULT_UI_THEME_CONFIG, spacing: { unit: 6, scale: [0, 1, 2, 4, 6, 8, 12, 16, 24, 32] } },
  },
} as const;

/**
 * BRAND COLOR PALETTE
 *
 * GlowHost brand colors for consistent theming.
 * Use these for component color customization.
 */
export const BRAND_COLORS = {
  // Primary brand colors
  primary: '#1a679f',                 // GlowHost blue
  primaryDark: '#144a73',             // Darker blue for hover states
  primaryLight: '#4a9fd1',            // Lighter blue for backgrounds

  // Status colors
  success: '#10b981',                 // Green for success states
  warning: '#f59e0b',                 // Amber for warnings
  error: '#ef4444',                   // Red for errors
  info: '#3b82f6',                    // Blue for information

  // Neutral colors
  gray50: '#f9fafb',
  gray100: '#f3f4f6',
  gray200: '#e5e7eb',
  gray300: '#d1d5db',
  gray400: '#9ca3af',
  gray500: '#6b7280',
  gray600: '#4b5563',
  gray700: '#374151',
  gray800: '#1f2937',
  gray900: '#111827',
} as const;

/**
 * COMPONENT SIZE SCALES
 *
 * Consistent sizing across all components.
 * Based on a harmonious scale for visual hierarchy.
 */
export const SIZE_SCALES = {
  // Padding/margin scale (in Tailwind classes)
  spacing: {
    xs: 'p-1',     // 4px
    sm: 'p-2',     // 8px
    md: 'p-3',     // 12px
    lg: 'p-4',     // 16px
    xl: 'p-6',     // 24px
  },

  // Text size scale
  text: {
    xs: 'text-xs',    // 12px
    sm: 'text-sm',    // 14px
    md: 'text-base',  // 16px
    lg: 'text-lg',    // 18px
    xl: 'text-xl',    // 20px
  },

  // Border radius scale
  radius: {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
  },

  // Shadow scale
  shadow: {
    none: 'shadow-none',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg',
    xl: 'shadow-xl',
  },
} as const;

/**
 * COMPONENT TYPE CONSTANTS
 *
 * Type-safe references for component identification and configuration.
 * Used throughout the UI system for component categorization.
 */
export const COMPONENT_TYPES = {
  NOTIFICATION: 'notification',
  AUTO_SAVE_INDICATOR: 'autoSaveIndicator',
  ATTACHMENT: 'attachment',
  FORM_FIELD: 'formField',
  MODAL: 'modal',
  BUTTON: 'button',
  CARD: 'card',
  INPUT: 'input',
  PROGRESS: 'progress',
} as const;
