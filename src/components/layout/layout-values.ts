/**
 * LAYOUT COMPONENT CONFIGURATION VALUES
 *
 * ⭐ THIS IS WHERE YOU MODIFY LAYOUT COMPONENT SETTINGS ⭐
 *
 * This file contains the actual configuration data for layout components.
 * To change layout behavior, responsive breakpoints, or component defaults, modify the values in this file.
 *
 * CURRENT CONFIG: GlowHost-optimized (professional header, responsive layout, accessible navigation)
 * DESIGN SYSTEM: Clean, modern, brand-consistent layout components
 *
 * FOR TYPE DEFINITIONS: See layout-types.ts
 * FOR HELPER FUNCTIONS: See layout-utils.ts
 */

import type {
  LayoutComponentConfig,
  HeaderConfig,
  FooterConfig,
  BreadcrumbConfig,
  MainLayoutConfig,
  SidebarConfig,
  GridConfig,
  ContentAreaConfig,
  LayoutAnimationConfig,
  ResponsiveConfig,
  NavigationItem,
  SocialLink,
  FooterColumn,
} from './layout-types';

/**
 * HEADER CONFIGURATION
 *
 * Controls the main site header with GlowHost branding and support information.
 * Optimized for professional hosting service presentation.
 */
export const DEFAULT_HEADER_CONFIG: HeaderConfig = {
  enableLogo: true,                   // GlowHost branding
  enableNavigation: true,             // Site navigation
  enableSearch: false,                // Not needed for contact form
  enableUserMenu: false,              // Not needed for this app
  stickyBehavior: 'none',             // Clean, non-intrusive
  height: 'md',                       // Balanced header height
  logoPosition: 'left',               // Standard layout
  navigationStyle: 'horizontal',      // Desktop-first navigation
  supportInfo: {
    phone: '1 (888) 293-HOST',        // GlowHost phone number
    hours: '24 / 7 / 365 Support',    // Always available support
    showCallToAction: true,           // Prominent support info
  },
};

/**
 * FOOTER CONFIGURATION
 *
 * Controls the site footer with minimal, professional appearance.
 * Focused on essential information and legal compliance.
 */
export const DEFAULT_FOOTER_CONFIG: FooterConfig = {
  enableSocialLinks: false,           // Simple, professional footer
  enableNewsletter: false,            // Not needed for contact form
  enableBackToTop: false,             // Short page, not necessary
  columnLayout: 'single',             // Clean, simple layout
  stickyBehavior: 'none',             // Standard footer behavior
  showLegalLinks: true,               // Privacy, terms compliance
  showContactInfo: true,              // Support contact information
  showCompanyInfo: true,              // GlowHost company info
};

/**
 * BREADCRUMB CONFIGURATION
 *
 * Controls navigation breadcrumbs for user orientation.
 * Simple, accessible navigation aids.
 */
export const DEFAULT_BREADCRUMB_CONFIG: BreadcrumbConfig = {
  separator: '»',                     // Clean, readable separator
  maxItems: 5,                        // Prevent overcrowding
  showHome: true,                     // Clear navigation starting point
  homeIcon: '🏠',                     // Universal home symbol
  enableCollapse: true,               // Handle long breadcrumb chains
  collapseThreshold: 3,               // Collapse at reasonable point
};

/**
 * MAIN LAYOUT CONFIGURATION
 *
 * Controls the primary page layout structure.
 * Optimized for form-based applications with clear content focus.
 */
export const DEFAULT_MAIN_LAYOUT_CONFIG: MainLayoutConfig = {
  enableSidebar: false,               // Clean, focused layout
  sidebarPosition: 'left',            // Standard sidebar position
  sidebarBehavior: 'auto',            // Responsive sidebar behavior
  contentMaxWidth: 'xl',              // Comfortable reading width
  contentPadding: 'md',               // Balanced content spacing
  enableContentArea: true,            // Structured content organization
  skipToContentTarget: '#main-content', // Accessibility requirement
};

/**
 * SIDEBAR CONFIGURATION
 *
 * Controls sidebar behavior for layouts that use them.
 * Configured for future extensibility.
 */
export const DEFAULT_SIDEBAR_CONFIG: SidebarConfig = {
  width: 'md',                        // Reasonable sidebar width
  collapsible: true,                  // Mobile-friendly behavior
  defaultCollapsed: false,            // Open by default on desktop
  overlay: true,                      // Mobile overlay behavior
  showToggleButton: true,             // User control over sidebar
  position: 'left',                   // Standard position
  backdrop: true,                     // Clear modal behavior on mobile
};

/**
 * GRID SYSTEM CONFIGURATION
 *
 * Controls the responsive grid system for layout consistency.
 * Based on modern CSS Grid with Tailwind breakpoints.
 */
export const DEFAULT_GRID_CONFIG: GridConfig = {
  columns: 12,                        // Standard 12-column grid
  gap: 'md',                          // Comfortable spacing
  breakpoints: {
    sm: 640,                          // Tailwind sm breakpoint
    md: 768,                          // Tailwind md breakpoint
    lg: 1024,                         // Tailwind lg breakpoint
    xl: 1280,                         // Tailwind xl breakpoint
  },
  containerMaxWidth: '1280px',        // Max content width
  gutters: {
    mobile: 'px-4',                   // Mobile gutter spacing
    tablet: 'px-6',                   // Tablet gutter spacing
    desktop: 'px-8',                  // Desktop gutter spacing
  },
};

/**
 * CONTENT AREA CONFIGURATION
 *
 * Controls content containers and spacing.
 * Optimized for readability and visual hierarchy.
 */
export const DEFAULT_CONTENT_AREA_CONFIG: ContentAreaConfig = {
  maxWidth: 'xl',                     // Comfortable content width
  padding: {
    mobile: 'p-4',                    // Mobile padding
    tablet: 'p-6',                    // Tablet padding
    desktop: 'p-8',                   // Desktop padding
  },
  margin: {
    mobile: 'm-0',                    // Mobile margins
    tablet: 'mx-auto',                // Tablet centering
    desktop: 'mx-auto',               // Desktop centering
  },
  backgroundVariant: 'none',          // Clean background
};

/**
 * LAYOUT ANIMATION CONFIGURATION
 *
 * Controls transitions and animations throughout the layout.
 * Balanced for professional feel with accessibility considerations.
 */
export const DEFAULT_LAYOUT_ANIMATION_CONFIG: LayoutAnimationConfig = {
  enableTransitions: true,            // Smooth user experience
  transitionDuration: 'normal',       // Balanced animation speed
  transitionEasing: 'ease-out',       // Natural feeling animations
  enableReducedMotion: true,          // Accessibility compliance
  animatePageTransitions: false,      // Keep it simple for forms
  animateLayoutChanges: true,         // Smooth layout adjustments
};

/**
 * COMPLETE LAYOUT CONFIGURATION
 *
 * Master configuration object that combines all layout settings.
 * This is the main export that layout components will use.
 */
export const DEFAULT_LAYOUT_COMPONENT_CONFIG: LayoutComponentConfig = {
  header: DEFAULT_HEADER_CONFIG,
  footer: DEFAULT_FOOTER_CONFIG,
  breadcrumb: DEFAULT_BREADCRUMB_CONFIG,
  mainLayout: DEFAULT_MAIN_LAYOUT_CONFIG,
  sidebar: DEFAULT_SIDEBAR_CONFIG,
  grid: DEFAULT_GRID_CONFIG,
  contentArea: DEFAULT_CONTENT_AREA_CONFIG,
  animation: DEFAULT_LAYOUT_ANIMATION_CONFIG,
};

/**
 * GLOWHOST NAVIGATION STRUCTURE
 *
 * Pre-configured navigation items for GlowHost website structure.
 * Customize these based on your site's navigation needs.
 */
export const GLOWHOST_NAVIGATION: NavigationItem[] = [
  {
    id: 'home',
    label: 'Home',
    href: '/',
  },
  {
    id: 'hosting',
    label: 'Web Hosting',
    href: '/hosting',
    children: [
      { id: 'shared', label: 'Shared Hosting', href: '/hosting/shared' },
      { id: 'vps', label: 'VPS Hosting', href: '/hosting/vps' },
      { id: 'dedicated', label: 'Dedicated Servers', href: '/hosting/dedicated' },
    ],
  },
  {
    id: 'domains',
    label: 'Domains',
    href: '/domains',
  },
  {
    id: 'support',
    label: 'Support',
    href: '/support',
    children: [
      { id: 'contact', label: 'Contact Sales', href: '/' },
      { id: 'help', label: 'Help Center', href: '/support/help' },
      { id: 'status', label: 'System Status', href: '/support/status', external: true },
    ],
  },
];

/**
 * GLOWHOST FOOTER CONTENT
 *
 * Pre-configured footer content for GlowHost branding.
 * Includes essential links and company information.
 */
export const GLOWHOST_FOOTER_COLUMNS: FooterColumn[] = [
  {
    id: 'company',
    title: 'Company',
    order: 1,
    links: [
      { id: 'about', label: 'About GlowHost', href: '/about' },
      { id: 'contact', label: 'Contact Us', href: '/contact' },
      { id: 'careers', label: 'Careers', href: '/careers' },
      { id: 'blog', label: 'Blog', href: '/blog' },
    ],
  },
  {
    id: 'support',
    title: 'Support',
    order: 2,
    links: [
      { id: 'help-center', label: 'Help Center', href: '/support' },
      { id: 'contact-support', label: 'Contact Support', href: '/support/contact' },
      { id: 'system-status', label: 'System Status', href: '/status', external: true },
      { id: 'documentation', label: 'Documentation', href: '/docs' },
    ],
  },
];

/**
 * GLOWHOST SOCIAL LINKS
 *
 * Social media presence configuration.
 * Update with actual GlowHost social media URLs.
 */
export const GLOWHOST_SOCIAL_LINKS: SocialLink[] = [
  {
    id: 'twitter',
    platform: 'Twitter',
    href: 'https://twitter.com/glowhost',
    icon: '🐦',
    label: 'Follow us on Twitter',
  },
  {
    id: 'facebook',
    platform: 'Facebook',
    href: 'https://facebook.com/glowhost',
    icon: '📘',
    label: 'Like us on Facebook',
  },
  {
    id: 'linkedin',
    platform: 'LinkedIn',
    href: 'https://linkedin.com/company/glowhost',
    icon: '💼',
    label: 'Connect on LinkedIn',
  },
];

/**
 * RESPONSIVE BREAKPOINT VALUES
 *
 * Standard breakpoint values used throughout the layout system.
 * Based on Tailwind CSS breakpoints for consistency.
 */
export const RESPONSIVE_BREAKPOINTS = {
  sm: 640,    // Small devices (landscape phones)
  md: 768,    // Medium devices (tablets)
  lg: 1024,   // Large devices (laptops)
  xl: 1280,   // Extra large devices (desktops)
  '2xl': 1536, // 2X Extra large devices (large desktops)
} as const;

/**
 * LAYOUT SPACING SCALES
 *
 * Consistent spacing values for layouts.
 * Based on a harmonious scale for visual rhythm.
 */
export const LAYOUT_SPACING = {
  // Container padding scale
  container: {
    none: 'px-0',
    sm: 'px-4',
    md: 'px-6',
    lg: 'px-8',
    xl: 'px-12',
  },

  // Section spacing scale
  section: {
    none: 'py-0',
    sm: 'py-8',
    md: 'py-12',
    lg: 'py-16',
    xl: 'py-24',
  },

  // Gap spacing scale
  gap: {
    none: 'gap-0',
    sm: 'gap-4',
    md: 'gap-6',
    lg: 'gap-8',
    xl: 'gap-12',
  },
} as const;

/**
 * LAYOUT Z-INDEX SCALE
 *
 * Consistent z-index values for layering components.
 * Prevents z-index conflicts and ensures proper stacking.
 */
export const Z_INDEX_SCALE = {
  behind: -1,
  base: 0,
  content: 10,
  header: 100,
  sidebar: 200,
  modal: 1000,
  popover: 1100,
  tooltip: 1200,
  notification: 1300,
  overlay: 1400,
  maximum: 9999,
} as const;

/**
 * EXAMPLE LAYOUT CONFIGURATIONS FOR SPECIALIZED USE CASES
 *
 * Pre-built configuration templates for specific application scenarios.
 *
 * IMPORTANT: DO NOT MODIFY these examples directly - they serve as reference templates.
 * Instead, COPY the configuration you need and customize it for your specific requirements.
 */
export const EXAMPLE_LAYOUT_CONFIGS = {
  // ADMIN DASHBOARD: Dense, functional layout for administrative interfaces
  ADMIN_DASHBOARD_CONFIG: {
    ...DEFAULT_LAYOUT_COMPONENT_CONFIG,
    header: { ...DEFAULT_HEADER_CONFIG, height: 'sm' as const, enableUserMenu: true },
    mainLayout: { ...DEFAULT_MAIN_LAYOUT_CONFIG, enableSidebar: true, contentMaxWidth: 'full' as const },
    sidebar: { ...DEFAULT_SIDEBAR_CONFIG, defaultCollapsed: false },
  },

  // CUSTOMER PORTAL: Clean, user-friendly layout for customer-facing interfaces
  CUSTOMER_PORTAL_CONFIG: {
    ...DEFAULT_LAYOUT_COMPONENT_CONFIG,
    header: { ...DEFAULT_HEADER_CONFIG, height: 'lg' as const, navigationStyle: 'horizontal' as const },
    contentArea: { ...DEFAULT_CONTENT_AREA_CONFIG, backgroundVariant: 'subtle' as const },
  },

  // MOBILE-FIRST: Optimized for mobile-first responsive design
  MOBILE_FIRST_CONFIG: {
    ...DEFAULT_LAYOUT_COMPONENT_CONFIG,
    header: { ...DEFAULT_HEADER_CONFIG, navigationStyle: 'hamburger' as const },
    grid: { ...DEFAULT_GRID_CONFIG, gap: 'sm' as const },
    contentArea: { ...DEFAULT_CONTENT_AREA_CONFIG, maxWidth: 'full' as const },
  },

  // LANDING PAGE: Marketing-focused layout with prominent calls to action
  LANDING_PAGE_CONFIG: {
    ...DEFAULT_LAYOUT_COMPONENT_CONFIG,
    header: { ...DEFAULT_HEADER_CONFIG, stickyBehavior: 'always' as const, height: 'lg' as const },
    footer: { ...DEFAULT_FOOTER_CONFIG, columnLayout: 'three' as const, enableSocialLinks: true },
  },
} as const;

/**
 * LAYOUT COMPONENT TYPE CONSTANTS
 *
 * Type-safe references for layout component identification and configuration.
 * Used throughout the layout system for component categorization.
 */
export const LAYOUT_COMPONENTS = {
  HEADER: 'header',
  FOOTER: 'footer',
  BREADCRUMB: 'breadcrumb',
  MAIN_LAYOUT: 'mainLayout',
  SIDEBAR: 'sidebar',
  GRID: 'grid',
  CONTENT_AREA: 'contentArea',
} as const;

/**
 * RESPONSIVE BREAKPOINT VALUES
 *
 * Standard breakpoint values used throughout the layout system.
 * Based on Tailwind CSS breakpoints for consistency.
 * These control responsive behavior across all layout components.
 */
export const BREAKPOINTS = {
  sm: 640,    // Small devices (landscape phones)
  md: 768,    // Medium devices (tablets)
  lg: 1024,   // Large devices (laptops)
  xl: 1280,   // Extra large devices (desktops)
  '2xl': 1536, // 2X Extra large devices (large desktops)
} as const;
