/**
 * LAYOUT COMPONENTS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file uses "re-exporting" to provide a single import point for all layout component functionality.
 * Modifying this file could break the entire layout component architecture.
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
 *   import { MainLayout, RESPONSIVE_BREAKPOINTS, HeaderProps } from '@/components/layout'
 *
 * Instead of having to import from multiple files:
 *
 *   import { MainLayout } from '@/components/layout/MainLayout'
 *   import { RESPONSIVE_BREAKPOINTS } from '@/components/layout/layout-values'
 *   import { HeaderProps } from '@/components/layout/layout-types'
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
 *   - Harder to understand the layout architecture
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * SAFE MODIFICATION PRACTICES:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ✅ MODIFY CONFIGURATIONS: Go to layout-values.ts
 * ✅ MODIFY TYPES: Go to layout-types.ts
 * ✅ MODIFY UTILITIES: Go to layout-utils.ts
 * ✅ ADD NEW COMPONENTS: Create component file, then re-export here
 * ✅ ADD NEW EXPORTS: Add to the appropriate specialized file, then re-export here
 *
 * This file maintains backward compatibility while keeping the codebase organized.
 * You can continue importing from '@/components/layout' as before.
 *
 * CURRENT CONFIG: GlowHost-optimized (professional header, responsive layout, accessible navigation)
 */

// Re-export types and interfaces
export type {
  BaseLayoutProps,
  ResponsiveConfig,
  HeaderConfig,
  HeaderProps,
  NavigationItem,
  UserMenuItem,
  FooterConfig,
  FooterProps,
  FooterColumn,
  FooterLink,
  SocialLink,
  LegalLink,
  NewsletterConfig,
  BreadcrumbConfig,
  BreadcrumbProps,
  BreadcrumbItem,
  MainLayoutConfig,
  MainLayoutProps,
  SidebarConfig,
  SidebarProps,
  GridConfig,
  GridProps,
  ContentAreaConfig,
  ContentAreaProps,
  LayoutAnimationConfig,
  LayoutComponentConfig,
  LayoutContextType,
  BreakpointKey,
} from './layout-types';

export type { LayoutComponentType } from './layout-types';

// Re-export configuration values ⭐ MODIFY THESE IN layout-values.ts ⭐
export {
  DEFAULT_HEADER_CONFIG,
  DEFAULT_FOOTER_CONFIG,
  DEFAULT_BREADCRUMB_CONFIG,
  DEFAULT_MAIN_LAYOUT_CONFIG,
  DEFAULT_SIDEBAR_CONFIG,
  DEFAULT_GRID_CONFIG,
  DEFAULT_CONTENT_AREA_CONFIG,
  DEFAULT_LAYOUT_ANIMATION_CONFIG,
  DEFAULT_LAYOUT_COMPONENT_CONFIG,
  GLOWHOST_NAVIGATION,
  GLOWHOST_FOOTER_COLUMNS,
  GLOWHOST_SOCIAL_LINKS,
  RESPONSIVE_BREAKPOINTS,
  LAYOUT_SPACING,
  Z_INDEX_SCALE,
  EXAMPLE_LAYOUT_CONFIGS,
  LAYOUT_COMPONENTS,
  BREAKPOINTS,
} from './layout-values';

// Re-export utility functions
export {
  getCurrentBreakpoint,
  isBreakpoint,
  isMobile,
  isTablet,
  isDesktop,
  getResponsiveValue,
  createResponsiveClasses,
  findNavigationItem,
  getNavigationPath,
  filterNavigationItems,
  getActiveNavigationItem,
  createBreadcrumbs,
  collapseBreadcrumbs,
  calculateContentWidth,
  getOptimalGridColumns,
  getSidebarWidth,
  createLayoutState,
  mergeLayoutConfigs,
  combineLayoutClasses,
  getContainerMaxWidth,
  getLayoutSpacing,
  getZIndex,
  generateSkipLinkId,
  createNavigationAria,
  isElementInViewport,
  prefersReducedMotionLayout,
  createLayoutTransition,
  isExternalUrl,
  getRelativePath,
  isCurrentPath,
} from './layout-utils';

// Re-export actual component files
export { Breadcrumb } from './Breadcrumb';
export { Footer } from './Footer';
export { Header } from './Header';
export { MainLayout } from './MainLayout';

// Convenience functions that use the default layout configuration
import {
  DEFAULT_LAYOUT_COMPONENT_CONFIG,
  RESPONSIVE_BREAKPOINTS,
  GLOWHOST_NAVIGATION,
} from './layout-values';
import {
  getCurrentBreakpoint,
  createLayoutState,
  mergeLayoutConfigs,
  getActiveNavigationItem,
} from './layout-utils';
import type { LayoutComponentConfig, NavigationItem } from './layout-types';

// Create layout state with default config
export const createDefaultLayoutState = () => {
  return createLayoutState(DEFAULT_LAYOUT_COMPONENT_CONFIG);
};

// Get current responsive breakpoint
export const getCurrentResponsiveBreakpoint = () => {
  return getCurrentBreakpoint();
};

// Check if current device is mobile-sized
export const isCurrentlyMobile = (): boolean => {
  if (typeof window === 'undefined') return false;
  return window.innerWidth < RESPONSIVE_BREAKPOINTS.md;
};

// Merge user layout config with defaults
export const createLayoutConfig = (userConfig: Partial<LayoutComponentConfig> = {}): LayoutComponentConfig => {
  return mergeLayoutConfigs(DEFAULT_LAYOUT_COMPONENT_CONFIG, userConfig);
};

// Get active navigation item from current URL
export const getCurrentActiveNavigation = (
  navigationItems: NavigationItem[] = GLOWHOST_NAVIGATION,
  currentPath?: string
): NavigationItem | null => {
  const path = currentPath || (typeof window !== 'undefined' ? window.location.pathname : '/');
  return getActiveNavigationItem(navigationItems, path);
};

// Responsive utility for checking device capabilities
export const getDeviceCapabilities = () => {
  if (typeof window === 'undefined') {
    return {
      hasTouch: false,
      hasHover: false,
      prefersReducedMotion: false,
      supportsGrid: false,
    };
  }

  return {
    hasTouch: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
    hasHover: window.matchMedia('(hover: hover)').matches,
    prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    supportsGrid: CSS.supports('display', 'grid'),
  };
};

// Layout component display names for debugging
export const LAYOUT_COMPONENT_NAMES = {
  BREADCRUMB: 'Breadcrumb',
  FOOTER: 'Footer',
  HEADER: 'Header',
  MAIN_LAYOUT: 'MainLayout',
} as const;

// Version info for debugging and development
export const LAYOUT_MODULE_INFO = {
  version: '1.0.0',
  architecture: 'modular-barrel-export',
  configurable: true,
  responsive: true,
  accessible: true,
  themeable: true,
} as const;

// Layout performance utilities
export const LAYOUT_PERFORMANCE = {
  // Debounce resize events for better performance
  debounceResize: (callback: () => void, delay = 150) => {
    let timeoutId: NodeJS.Timeout;
    return () => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(callback, delay);
    };
  },

  // Throttle scroll events for better performance
  throttleScroll: (callback: () => void, limit = 16) => {
    let inThrottle: boolean;
    return () => {
      if (!inThrottle) {
        callback();
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  },

  // Check if animations should be enabled
  shouldAnimate: (): boolean => {
    return !getDeviceCapabilities().prefersReducedMotion;
  },
} as const;
