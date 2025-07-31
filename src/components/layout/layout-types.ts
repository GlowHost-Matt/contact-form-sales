/**
 * LAYOUT COMPONENT TYPES AND INTERFACES
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains TypeScript type definitions and interfaces for layout components.
 * Modifying this file could break the entire layout component system across the application.
 *
 * 🔧 TO MODIFY LAYOUT COMPONENT SETTINGS:
 *    Go to → layout-values.ts
 *    This is where you change responsive breakpoints, component defaults, and layout behaviors.
 *
 * 📚 FOR UTILITY FUNCTIONS:
 *    See → layout-utils.ts
 *    Contains helper functions for responsive design, navigation, and layout calculations.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break TypeScript compilation across the entire application
 *   - May cause runtime errors in layout rendering and responsive behavior
 *   - Interface changes affect all layout components and navigation systems
 *   - Breaking changes propagate throughout the application architecture
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual breakpoints and settings are defined
 *   - Makes the responsive system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in layout-values.ts
 *   - Add new layout types following existing patterns and consulting the team
 *   - For utility functions: See layout-utils.ts
 *   - Test thoroughly if type modifications are absolutely necessary
 *
 * This file defines the contracts that layout configuration objects must follow.
 * Configuration values are defined in layout-values.ts
 */

import type { ReactNode } from 'react';

// Base layout props that all layout components should support
export interface BaseLayoutProps {
  className?: string;
  children?: ReactNode;
  id?: string;
  'data-testid'?: string;
}

// Layout configuration for different screen sizes
export interface ResponsiveConfig {
  mobile: string;
  tablet: string;
  desktop: string;
  widescreen?: string;
}

// Header component configuration
export interface HeaderConfig {
  enableLogo: boolean;
  enableNavigation: boolean;
  enableSearch: boolean;
  enableUserMenu: boolean;
  stickyBehavior: 'none' | 'always' | 'scroll-up' | 'scroll-down';
  height: 'sm' | 'md' | 'lg';
  logoPosition: 'left' | 'center' | 'right';
  navigationStyle: 'horizontal' | 'vertical' | 'hamburger';
  supportInfo: {
    phone: string;
    hours: string;
    showCallToAction: boolean;
  };
}

export interface HeaderProps extends BaseLayoutProps {
  logoSrc?: string;
  logoAlt?: string;
  logoHref?: string;
  navigationItems?: NavigationItem[];
  userMenuItems?: UserMenuItem[];
  searchPlaceholder?: string;
  onSearchSubmit?: (query: string) => void;
  variant?: 'default' | 'minimal' | 'branded';
  sticky?: boolean;
}

// Navigation types
export interface NavigationItem {
  id: string;
  label: string;
  href: string;
  icon?: ReactNode;
  children?: NavigationItem[];
  external?: boolean;
  badge?: string;
  disabled?: boolean;
}

export interface UserMenuItem {
  id: string;
  label: string;
  href?: string;
  onClick?: () => void;
  icon?: ReactNode;
  divider?: boolean;
  destructive?: boolean;
}

// Footer component configuration
export interface FooterConfig {
  enableSocialLinks: boolean;
  enableNewsletter: boolean;
  enableBackToTop: boolean;
  columnLayout: 'single' | 'two' | 'three' | 'four';
  stickyBehavior: 'none' | 'bottom' | 'auto-hide';
  showLegalLinks: boolean;
  showContactInfo: boolean;
  showCompanyInfo: boolean;
}

export interface FooterProps extends BaseLayoutProps {
  columns?: FooterColumn[];
  socialLinks?: SocialLink[];
  legalLinks?: LegalLink[];
  newsletterConfig?: NewsletterConfig;
  copyrightText?: string;
  variant?: 'default' | 'minimal' | 'extended';
}

export interface FooterColumn {
  id: string;
  title: string;
  links: FooterLink[];
  order?: number;
}

export interface FooterLink {
  id: string;
  label: string;
  href: string;
  external?: boolean;
  icon?: ReactNode;
}

export interface SocialLink {
  id: string;
  platform: string;
  href: string;
  icon: ReactNode;
  label: string;
}

export interface LegalLink {
  id: string;
  label: string;
  href: string;
}

export interface NewsletterConfig {
  enabled: boolean;
  title: string;
  description: string;
  placeholder: string;
  buttonText: string;
  onSubmit: (email: string) => void;
}

// Breadcrumb component configuration
export interface BreadcrumbConfig {
  separator: ReactNode;
  maxItems: number;
  showHome: boolean;
  homeIcon: ReactNode;
  enableCollapse: boolean;
  collapseThreshold: number;
}

export interface BreadcrumbProps extends BaseLayoutProps {
  items?: BreadcrumbItem[];
  separator?: ReactNode;
  maxVisible?: number;
  variant?: 'default' | 'minimal' | 'chips';
}

export interface BreadcrumbItem {
  id: string;
  label: string;
  href?: string;
  current?: boolean;
  icon?: ReactNode;
}

// Main layout component configuration
export interface MainLayoutConfig {
  enableSidebar: boolean;
  sidebarPosition: 'left' | 'right';
  sidebarBehavior: 'fixed' | 'overlay' | 'push' | 'auto';
  contentMaxWidth: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
  contentPadding: 'none' | 'sm' | 'md' | 'lg';
  enableContentArea: boolean;
  skipToContentTarget: string;
}

export interface MainLayoutProps extends BaseLayoutProps {
  header?: ReactNode;
  footer?: ReactNode;
  sidebar?: ReactNode;
  breadcrumbs?: ReactNode;
  sidebarOpen?: boolean;
  onSidebarToggle?: (open: boolean) => void;
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
  padding?: 'none' | 'sm' | 'md' | 'lg';
  variant?: 'default' | 'fluid' | 'boxed' | 'sidebar';
}

// Sidebar component types
export interface SidebarConfig {
  width: 'sm' | 'md' | 'lg';
  collapsible: boolean;
  defaultCollapsed: boolean;
  overlay: boolean;
  showToggleButton: boolean;
  position: 'left' | 'right';
  backdrop: boolean;
}

export interface SidebarProps extends BaseLayoutProps {
  open?: boolean;
  onClose?: () => void;
  variant?: 'overlay' | 'push' | 'fixed';
  position?: 'left' | 'right';
  width?: 'sm' | 'md' | 'lg';
}

// Layout grid system types
export interface GridConfig {
  columns: number;
  gap: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  breakpoints: {
    sm: number;
    md: number;
    lg: number;
    xl: number;
  };
  containerMaxWidth: string;
  gutters: ResponsiveConfig;
}

export interface GridProps extends BaseLayoutProps {
  columns?: number | ResponsiveConfig;
  gap?: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  alignItems?: 'start' | 'center' | 'end' | 'stretch';
  justifyContent?: 'start' | 'center' | 'end' | 'between' | 'around' | 'evenly';
}

// Content area types
export interface ContentAreaConfig {
  maxWidth: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
  padding: ResponsiveConfig;
  margin: ResponsiveConfig;
  backgroundVariant: 'none' | 'subtle' | 'card' | 'section';
}

export interface ContentAreaProps extends BaseLayoutProps {
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
  padding?: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  centered?: boolean;
  variant?: 'default' | 'card' | 'section' | 'full-bleed';
  background?: 'none' | 'subtle' | 'card' | 'accent';
}

// Layout animation and transition types
export interface LayoutAnimationConfig {
  enableTransitions: boolean;
  transitionDuration: 'fast' | 'normal' | 'slow';
  transitionEasing: 'ease' | 'ease-in' | 'ease-out' | 'ease-in-out';
  enableReducedMotion: boolean;
  animatePageTransitions: boolean;
  animateLayoutChanges: boolean;
}

// Complete layout configuration
export interface LayoutComponentConfig {
  header: HeaderConfig;
  footer: FooterConfig;
  breadcrumb: BreadcrumbConfig;
  mainLayout: MainLayoutConfig;
  sidebar: SidebarConfig;
  grid: GridConfig;
  contentArea: ContentAreaConfig;
  animation: LayoutAnimationConfig;
}

// Layout context types for provider
export interface LayoutContextType {
  config: LayoutComponentConfig;
  updateConfig: (newConfig: Partial<LayoutComponentConfig>) => void;
  currentBreakpoint: 'sm' | 'md' | 'lg' | 'xl';
  isMobile: boolean;
  isTablet: boolean;
  isDesktop: boolean;
  sidebarOpen: boolean;
  setSidebarOpen: (open: boolean) => void;
  theme: 'light' | 'dark' | 'auto';
  setTheme: (theme: 'light' | 'dark' | 'auto') => void;
}

// Layout component type string literals (constants defined in layout-values.ts)
export type LayoutComponentType = 'header' | 'footer' | 'breadcrumb' | 'mainLayout' | 'sidebar' | 'grid' | 'contentArea';

// Responsive breakpoint type literals (constants defined in layout-values.ts)
export type BreakpointKey = 'sm' | 'md' | 'lg' | 'xl' | '2xl';
