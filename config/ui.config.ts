/**
 * UI CONFIGURATION
 *
 * Visual styling, component behavior, animations, and user interface settings.
 * Modify values below to customize the look and feel of the application.
 */

// ============================================================================
// DESIGN SYSTEM FOUNDATION
// ============================================================================

/**
 * Brand color palette for consistent theming
 * primary: Main GlowHost brand color
 * primaryDark/Light: Variations for hover states and backgrounds
 * status: Colors for success, warning, error, and info states
 * neutral: Gray scale for text, borders, and backgrounds
 */
export const BRAND_COLORS = {
  primary: '#1a679f',
  primaryDark: '#144a73',
  primaryLight: '#4a9fd1',

  success: '#10b981',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6',

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
 * Component size scales for visual hierarchy
 * spacing: Padding and margin values (Tailwind classes)
 * text: Font size scale from small to large
 * radius: Border radius options for different components
 * shadow: Shadow depth levels for layered interfaces
 */
export const SIZE_SCALES = {
  spacing: {
    xs: 'p-1',
    sm: 'p-2',
    md: 'p-3',
    lg: 'p-4',
    xl: 'p-6',
  },
  text: {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl',
  },
  radius: {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
  },
  shadow: {
    none: 'shadow-none',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg',
    xl: 'shadow-xl',
  },
} as const;

// ============================================================================
// COMPONENT CONFIGURATIONS
// ============================================================================

/**
 * Toast notification behavior and positioning
 * position: Where notifications appear on screen
 * maxNotifications: Prevent notification overflow
 * defaultDuration: How long messages stay visible (5000ms = 5 seconds)
 * enableAnimation: Smooth slide-in/fade-out effects
 * persistOnHover: Keep notifications visible when mouse hovers
 */
export const NOTIFICATION_CONFIG = {
  position: 'bottom-right',
  maxNotifications: 5,
  defaultDuration: 5000,
  enableAnimation: true,
  enableSound: false,
  persistOnHover: true,
} as const;

/**
 * Auto-save status indicator display settings
 * showText: Include text with status icons
 * size: Compact (sm), medium (md), or large (lg)
 * enableAnimation: Visual feedback for status changes
 * statusDuration: How long to show "saved" confirmation (2500ms)
 */
export const AUTO_SAVE_INDICATOR_CONFIG = {
  showText: true,
  size: 'sm',
  position: 'inline',
  enableAnimation: true,
  statusDuration: 2500,
} as const;

/**
 * Floating auto-save indicator that stays visible during work
 * enabled: Show the persistent floating indicator
 * position: Corner placement (bottom-right is less intrusive)
 * showDuration: How long "saved" status remains visible (3000ms)
 * spacing: Distance from screen edges (16px mobile, 24px desktop)
 * zIndex: Layer above content but below modals (50)
 * hideOnIdle: Hide when no auto-save activity
 */
export const FLOATING_AUTO_SAVE_INDICATOR_CONFIG = {
  enabled: true,
  position: 'bottom-right',
  showDuration: 3000,
  enableAnimation: true,
  enableBackdropBlur: true,
  enableShadow: true,
  spacing: {
    mobile: 16,
    desktop: 24,
  },
  zIndex: 50,
  hideOnIdle: true,
  showText: true,
} as const;

/**
 * File attachment and upload constraints
 * maxFileSize: 10MB balances user needs vs server capacity
 * maxFiles: 5 files prevents overwhelming submissions
 * allowedTypes: Security-focused MIME types only
 * enablePreview: Show image thumbnails for better UX
 * enableDragDrop: Modern drag-and-drop interface
 * enableDescriptions: Let users add context to files
 */
export const ATTACHMENT_CONFIG = {
  maxFileSize: 10 * 1024 * 1024,
  maxFiles: 5,
  allowedTypes: [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf', 'text/plain', 'text/csv',
    'application/zip', 'application/x-rar-compressed',
  ],
  enablePreview: true,
  enableDragDrop: true,
  enableDescriptions: true,
} as const;

/**
 * Form field behavior and validation display
 * showCharacterCount: Help users stay within limits
 * enableValidation: Real-time field validation
 * validateOnChange: Validate while typing (can be distracting)
 * validateOnBlur: Validate when leaving field (less intrusive)
 * showRequiredIndicator: Visual * for required fields
 */
export const FORM_FIELD_CONFIG = {
  showCharacterCount: true,
  enableValidation: true,
  validateOnChange: false,
  validateOnBlur: true,
  showRequiredIndicator: true,
  requiredIndicator: '*',
} as const;

/**
 * Modal dialog behavior and appearance
 * enableBackdropClick: Click outside to close
 * enableEscapeKey: Press Escape to close
 * showCloseButton: X button in corner
 * size: Default modal size (sm, md, lg, xl)
 * centered: Center vertically on screen
 * enableAnimation: Smooth open/close transitions
 */
export const MODAL_CONFIG = {
  enableBackdropClick: true,
  enableEscapeKey: true,
  showCloseButton: true,
  size: 'md',
  centered: true,
  enableAnimation: true,
} as const;

/**
 * Button component defaults
 * defaultVariant: Primary (filled) or secondary (outline)
 * defaultSize: Comfortable click target size
 * enableRippleEffect: Material Design ripple (can be flashy)
 * enableFocusRing: Accessibility focus indicator
 * loadingSpinnerType: Visual loading feedback
 */
export const BUTTON_CONFIG = {
  defaultVariant: 'primary',
  defaultSize: 'md',
  enableRippleEffect: false,
  enableFocusRing: true,
  loadingSpinnerType: 'spinner',
} as const;

/**
 * Card container styling
 * defaultShadow: Subtle depth with shadow
 * defaultBorder: Clear content boundaries
 * defaultPadding: Comfortable internal spacing
 * enableHoverEffects: Interactive feedback on hover
 */
export const CARD_CONFIG = {
  defaultShadow: 'sm',
  defaultBorder: true,
  defaultPadding: 'md',
  enableHoverEffects: true,
} as const;

/**
 * Input field styling and behavior
 * defaultSize: Comfortable input height
 * showFocusRing: Accessibility requirement
 * enableClearButton: X to clear content (can add per field)
 * enablePasswordToggle: Show/hide password toggle
 * debounceDelay: Wait time for input events (300ms = responsive)
 */
export const INPUT_CONFIG = {
  defaultSize: 'md',
  showFocusRing: true,
  enableClearButton: false,
  enablePasswordToggle: true,
  debounceDelay: 300,
} as const;

/**
 * Progress indicator styling
 * defaultSize: Visible but not overwhelming
 * enableAnimation: Visual progress feedback
 * showPercentage: Numeric progress display (can enable per use)
 * colorScheme: Consistent with brand colors
 */
export const PROGRESS_CONFIG = {
  defaultSize: 'md',
  enableAnimation: true,
  showPercentage: false,
  colorScheme: 'primary',
} as const;

/**
 * Overall visual design system
 * colorScheme: Light or dark theme preference
 * primaryColor: GlowHost brand blue
 * fontFamily: System fonts for better performance and native feel
 * borderRadius: Modern but not excessive rounded corners
 * animation: Balanced speed and easing for natural feel
 * spacing: 4px base unit following Tailwind standards
 */
export const UI_THEME_CONFIG = {
  colorScheme: 'light',
  primaryColor: '#1a679f',
  fontFamily: 'system-ui, -apple-system, sans-serif',
  borderRadius: 'md',
  animation: {
    duration: 'normal',
    easing: 'ease-out',
  },
  spacing: {
    unit: 4,
    scale: [0, 1, 2, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24, 32, 40, 48, 56, 64],
  },
} as const;

/**
 * Accessibility features for inclusive design
 * enableKeyboardNavigation: Full keyboard support
 * enableScreenReader: ARIA labels and descriptions
 * enableHighContrast: Optional high contrast mode
 * enableReducedMotion: Respect user motion preferences
 * enableFocusIndicators: Clear focus visualization
 */
export const ACCESSIBILITY_CONFIG = {
  enableKeyboardNavigation: true,
  enableScreenReader: true,
  enableHighContrast: false,
  enableReducedMotion: true,
  enableFocusIndicators: true,
} as const;

// ============================================================================
// MASTER UI CONFIGURATION
// ============================================================================

/**
 * Consolidated UI configuration object
 * Import this to access all UI settings in components
 */
export const UI_CONFIG = {
  colors: BRAND_COLORS,
  scales: SIZE_SCALES,
  notification: NOTIFICATION_CONFIG,
  autoSaveIndicator: AUTO_SAVE_INDICATOR_CONFIG,
  floatingAutoSaveIndicator: FLOATING_AUTO_SAVE_INDICATOR_CONFIG,
  attachment: ATTACHMENT_CONFIG,
  formField: FORM_FIELD_CONFIG,
  modal: MODAL_CONFIG,
  button: BUTTON_CONFIG,
  card: CARD_CONFIG,
  input: INPUT_CONFIG,
  progress: PROGRESS_CONFIG,
  theme: UI_THEME_CONFIG,
  accessibility: ACCESSIBILITY_CONFIG,
} as const;

// ============================================================================
// COMPONENT TYPE CONSTANTS
// ============================================================================

/**
 * Component type identifiers for configuration and debugging
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

// ============================================================================
// CONFIGURATION TEMPLATES
// ============================================================================

/**
 * Pre-built configuration templates for different use cases
 * Copy and modify these for specific requirements
 */
export const UI_TEMPLATES = {
  /**
   * High-density interface for admin dashboards
   * Smaller components, more information per screen
   */
  ADMIN_DASHBOARD: {
    notification: { ...NOTIFICATION_CONFIG, position: 'top-right' as const, maxNotifications: 10 },
    button: { ...BUTTON_CONFIG, defaultSize: 'sm' as const },
    input: { ...INPUT_CONFIG, defaultSize: 'sm' as const },
    card: { ...CARD_CONFIG, defaultPadding: 'sm' as const },
    theme: { ...UI_THEME_CONFIG, colorScheme: 'dark' as const },
  },

  /**
   * Clean interface optimized for customer interactions
   * Larger touch targets, simplified design
   */
  CUSTOMER_PORTAL: {
    notification: { ...NOTIFICATION_CONFIG, enableAnimation: true, defaultDuration: 4000 },
    button: { ...BUTTON_CONFIG, defaultSize: 'lg' as const, enableRippleEffect: true },
    input: { ...INPUT_CONFIG, defaultSize: 'lg' as const },
    card: { ...CARD_CONFIG, defaultShadow: 'md' as const },
    theme: { ...UI_THEME_CONFIG, borderRadius: 'lg' as const },
  },

  /**
   * Maximum accessibility for users with visual needs
   * High contrast, reduced motion, clear focus indicators
   */
  HIGH_CONTRAST: {
    notification: { ...NOTIFICATION_CONFIG, enableAnimation: false },
    theme: {
      ...UI_THEME_CONFIG,
      colorScheme: 'light' as const,
      primaryColor: '#000000',
      borderRadius: 'none' as const,
    },
    button: { ...BUTTON_CONFIG, enableFocusRing: true },
    accessibility: { ...ACCESSIBILITY_CONFIG, enableHighContrast: true },
  },

  /**
   * Touch-optimized interface for mobile devices
   * Larger buttons, simplified interactions, mobile gestures
   */
  MOBILE_OPTIMIZED: {
    button: { ...BUTTON_CONFIG, defaultSize: 'lg' as const },
    input: { ...INPUT_CONFIG, defaultSize: 'lg' as const },
    modal: { ...MODAL_CONFIG, size: 'fullscreen' as const },
    attachment: { ...ATTACHMENT_CONFIG, enableDragDrop: false },
    theme: { ...UI_THEME_CONFIG, spacing: { unit: 6, scale: [0, 1, 2, 4, 6, 8, 12, 16, 24, 32] } },
  },
} as const;
