/**
 * LAYOUT COMPONENT UTILITY FUNCTIONS
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains helper functions for layout components.
 * Modifying this file could break responsive design and navigation across the entire application.
 *

 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break responsive design and breakpoint detection
 *   - May cause navigation systems and breadcrumbs to fail
 *   - Function signature changes affect all layout components
 *   - Breaking changes propagate throughout the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual responsive settings are defined
 *   - Makes the layout system harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in layout-values.ts
 *   - Add new utility functions following existing patterns
 *   - Consult with the development team before function changes
 *   - Test thoroughly if utility modifications are absolutely necessary
 *
 * These utilities support responsive design, navigation, and layout calculations.
 * For actual configuration values, see layout-values.ts
 */

import type {
  ResponsiveConfig,
  NavigationItem,
  BreadcrumbItem,
  LayoutComponentConfig,
  BreakpointKey,
} from './layout-types';
import {
  RESPONSIVE_BREAKPOINTS,
  LAYOUT_SPACING,
  Z_INDEX_SCALE,
  DEFAULT_LAYOUT_COMPONENT_CONFIG,
} from './layout-values';

/**
 * RESPONSIVE UTILITIES
 */

// Get current breakpoint based on window width
export const getCurrentBreakpoint = (): BreakpointKey => {
  if (typeof window === 'undefined') return 'lg'; // SSR fallback

  const width = window.innerWidth;

  if (width < RESPONSIVE_BREAKPOINTS.sm) return 'sm';
  if (width < RESPONSIVE_BREAKPOINTS.md) return 'md';
  if (width < RESPONSIVE_BREAKPOINTS.lg) return 'lg';
  if (width < RESPONSIVE_BREAKPOINTS.xl) return 'xl';
  return '2xl';
};

// Check if current viewport matches a breakpoint
export const isBreakpoint = (breakpoint: BreakpointKey): boolean => {
  if (typeof window === 'undefined') return false;

  const width = window.innerWidth;
  const breakpointValue = RESPONSIVE_BREAKPOINTS[breakpoint];

  return width >= breakpointValue;
};

// Check if viewport is mobile size
export const isMobile = (): boolean => {
  return !isBreakpoint('md');
};

// Check if viewport is tablet size
export const isTablet = (): boolean => {
  return isBreakpoint('md') && !isBreakpoint('lg');
};

// Check if viewport is desktop size
export const isDesktop = (): boolean => {
  return isBreakpoint('lg');
};

// Get responsive value based on current breakpoint
export const getResponsiveValue = <T>(config: ResponsiveConfig | T): string => {
  if (typeof config === 'string') return config;

  const breakpoint = getCurrentBreakpoint();
  const responsiveConfig = config as ResponsiveConfig;

  // Return value for current breakpoint, falling back to smaller breakpoints
  if (breakpoint === '2xl' && responsiveConfig.widescreen) return responsiveConfig.widescreen;
  if (isDesktop()) return responsiveConfig.desktop;
  if (isTablet()) return responsiveConfig.tablet;
  return responsiveConfig.mobile;
};

// Create responsive class string
export const createResponsiveClasses = (config: ResponsiveConfig): string => {
  const classes: string[] = [];

  // Base mobile classes (no prefix)
  if (config.mobile) classes.push(config.mobile);

  // Tablet classes (md: prefix)
  if (config.tablet) classes.push(`md:${config.tablet}`);

  // Desktop classes (lg: prefix)
  if (config.desktop) classes.push(`lg:${config.desktop}`);

  // Widescreen classes (2xl: prefix)
  if (config.widescreen) classes.push(`2xl:${config.widescreen}`);

  return classes.join(' ');
};

/**
 * NAVIGATION UTILITIES
 */

// Find navigation item by ID recursively
export const findNavigationItem = (items: NavigationItem[], id: string): NavigationItem | null => {
  for (const item of items) {
    if (item.id === id) return item;

    if (item.children) {
      const found = findNavigationItem(item.children, id);
      if (found) return found;
    }
  }

  return null;
};

// Get navigation item path (breadcrumb trail)
export const getNavigationPath = (items: NavigationItem[], targetId: string): NavigationItem[] => {
  const findPath = (items: NavigationItem[], targetId: string, path: NavigationItem[] = []): NavigationItem[] | null => {
    for (const item of items) {
      const currentPath = [...path, item];

      if (item.id === targetId) return currentPath;

      if (item.children) {
        const found = findPath(item.children, targetId, currentPath);
        if (found) return found;
      }
    }

    return null;
  };

  return findPath(items, targetId) || [];
};

// Filter navigation items based on permissions or conditions
export const filterNavigationItems = (
  items: NavigationItem[],
  predicate: (item: NavigationItem) => boolean
): NavigationItem[] => {
  return items
    .filter(predicate)
    .map(item => ({
      ...item,
      children: item.children ? filterNavigationItems(item.children, predicate) : undefined,
    }));
};

// Get active navigation item based on current URL
export const getActiveNavigationItem = (
  items: NavigationItem[],
  currentPath: string
): NavigationItem | null => {
  // Find exact match first
  const activeItem = findNavigationItem(items, currentPath);
  if (activeItem) return activeItem;

  // Find best match based on URL path
  let bestMatch: NavigationItem | null = null;
  let bestMatchLength = 0;

  const checkItem = (item: NavigationItem) => {
    if (item.href && currentPath.startsWith(item.href) && item.href.length > bestMatchLength) {
      bestMatch = item;
      bestMatchLength = item.href.length;
    }

    if (item.children) {
      item.children.forEach(checkItem);
    }
  };

  items.forEach(checkItem);
  return bestMatch;
};

/**
 * BREADCRUMB UTILITIES
 */

// Create breadcrumb items from navigation path
export const createBreadcrumbs = (navigationPath: NavigationItem[]): BreadcrumbItem[] => {
  return navigationPath.map((item, index) => ({
    id: item.id,
    label: item.label,
    href: index < navigationPath.length - 1 ? item.href : undefined, // Last item is current, no link
    current: index === navigationPath.length - 1,
    icon: item.icon,
  }));
};

// Collapse breadcrumbs when they exceed maximum items
export const collapseBreadcrumbs = (
  items: BreadcrumbItem[],
  maxItems: number
): BreadcrumbItem[] => {
  if (items.length <= maxItems) return items;

  const firstItem = items[0];
  const lastItems = items.slice(-(maxItems - 2)); // Keep last n-2 items

  // Create ellipsis item
  const ellipsisItem: BreadcrumbItem = {
    id: 'ellipsis',
    label: '...',
    current: false,
  };

  return [firstItem, ellipsisItem, ...lastItems];
};

/**
 * LAYOUT CALCULATION UTILITIES
 */

// Calculate content width based on container and padding
export const calculateContentWidth = (
  containerWidth: number,
  padding: string = 'md'
): number => {
  const paddingMap = {
    none: 0,
    sm: 32,  // 16px * 2
    md: 48,  // 24px * 2
    lg: 64,  // 32px * 2
    xl: 96,  // 48px * 2
  };

  const paddingValue = paddingMap[padding as keyof typeof paddingMap] || paddingMap.md;
  return Math.max(0, containerWidth - paddingValue);
};

// Get optimal number of grid columns for given width
export const getOptimalGridColumns = (
  containerWidth: number,
  minColumnWidth: number = 250
): number => {
  return Math.max(1, Math.floor(containerWidth / minColumnWidth));
};

// Calculate sidebar width in pixels
export const getSidebarWidth = (size: 'sm' | 'md' | 'lg' = 'md'): number => {
  const sizeMap = {
    sm: 240,  // 15rem
    md: 320,  // 20rem
    lg: 384,  // 24rem
  };

  return sizeMap[size];
};

/**
 * LAYOUT STATE UTILITIES
 */

// Create layout state object for context
export const createLayoutState = (
  config: LayoutComponentConfig = DEFAULT_LAYOUT_COMPONENT_CONFIG
) => {
  const currentBreakpoint = getCurrentBreakpoint();

  return {
    config,
    currentBreakpoint,
    isMobile: isMobile(),
    isTablet: isTablet(),
    isDesktop: isDesktop(),
    viewport: {
      width: typeof window !== 'undefined' ? window.innerWidth : 1024,
      height: typeof window !== 'undefined' ? window.innerHeight : 768,
    },
  };
};

// Deep merge layout configurations
export const mergeLayoutConfigs = <T extends Record<string, any>>(
  defaultConfig: T,
  userConfig: Partial<T>
): T => {
  const result = { ...defaultConfig };

  for (const key in userConfig) {
    if (userConfig[key] !== undefined) {
      if (typeof userConfig[key] === 'object' && userConfig[key] !== null && !Array.isArray(userConfig[key])) {
        result[key] = mergeLayoutConfigs(result[key] || ({} as any), userConfig[key]);
      } else {
        result[key] = userConfig[key] as T[Extract<keyof T, string>];
      }
    }
  }

  return result;
};

/**
 * CSS CLASS UTILITIES
 */

// Combine layout classes with proper precedence
export const combineLayoutClasses = (...classes: (string | undefined | null | false)[]): string => {
  return classes.filter(Boolean).join(' ');
};

// Get container max-width class
export const getContainerMaxWidth = (size: string = 'xl'): string => {
  const sizeMap = {
    sm: 'max-w-sm',      // 24rem (384px)
    md: 'max-w-2xl',     // 42rem (672px)
    lg: 'max-w-4xl',     // 56rem (896px)
    xl: 'max-w-6xl',     // 72rem (1152px)
    '2xl': 'max-w-7xl',  // 80rem (1280px)
    full: 'max-w-full',  // 100%
  };

  return sizeMap[size as keyof typeof sizeMap] || sizeMap.xl;
};

// Get spacing class for layout elements
export const getLayoutSpacing = (
  type: 'container' | 'section' | 'gap',
  size: 'none' | 'sm' | 'md' | 'lg' | 'xl' = 'md'
): string => {
  return LAYOUT_SPACING[type][size] || LAYOUT_SPACING[type].md;
};

// Get z-index value for layering
export const getZIndex = (layer: keyof typeof Z_INDEX_SCALE): number => {
  return Z_INDEX_SCALE[layer];
};

/**
 * ACCESSIBILITY UTILITIES
 */

// Generate skip link target ID
export const generateSkipLinkId = (section: string): string => {
  return `skip-to-${section.toLowerCase().replace(/\s+/g, '-')}`;
};

// Create accessible navigation ARIA properties
export const createNavigationAria = (
  label: string,
  current?: boolean
): Record<string, string | boolean> => {
  const aria: Record<string, string | boolean> = {
    'aria-label': label,
  };

  if (current !== undefined) {
    aria['aria-current'] = current ? 'page' : false;
  }

  return aria;
};

// Check if element is in viewport
export const isElementInViewport = (element: HTMLElement): boolean => {
  if (!element) return false;

  const rect = element.getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
};

/**
 * ANIMATION UTILITIES
 */

// Check if user prefers reduced motion (layout-specific version)
export const prefersReducedMotionLayout = (): boolean => {
  if (typeof window === 'undefined') return false;
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

// Create layout transition CSS
export const createLayoutTransition = (
  properties: string[] = ['all'],
  duration: 'fast' | 'normal' | 'slow' = 'normal',
  easing: string = 'ease-out'
): string => {
  const durationMap = {
    fast: '150ms',
    normal: '300ms',
    slow: '500ms',
  };

  const durationValue = durationMap[duration];
  return properties.map(prop => `${prop} ${durationValue} ${easing}`).join(', ');
};

/**
 * URL AND ROUTING UTILITIES
 */

// Check if URL is external
export const isExternalUrl = (url: string): boolean => {
  try {
    const urlObj = new URL(url, window.location.origin);
    return urlObj.origin !== window.location.origin;
  } catch {
    return false;
  }
};

// Get relative path from URL
export const getRelativePath = (url: string): string => {
  try {
    const urlObj = new URL(url, window.location.origin);
    return urlObj.pathname + urlObj.search + urlObj.hash;
  } catch {
    return url;
  }
};

// Check if path matches current location
export const isCurrentPath = (path: string): boolean => {
  if (typeof window === 'undefined') return false;
  return window.location.pathname === path;
};
