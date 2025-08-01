/**
 * COMPONENTS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE DIRECTLY ⚠️
 * This file uses "re-exporting" to provide a single import point for all component functionality.
 * Modifying this file could break the entire component architecture.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📋 COMPONENT ARCHITECTURE OVERVIEW
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * Our component system is organized into logical categories, each following the same
 * modular pattern established throughout the application:
 *
 * 🎨 UI COMPONENTS (components/ui/):
 *    - Interactive elements: buttons, forms, notifications, indicators
 *    - Content components: cards, modals, attachments, confirmation pages
 *    - Configuration: ui-types.ts, ui-values.ts, ui-utils.ts, index.ts
 *
 * 📐 LAYOUT COMPONENTS (components/layout/):
 *    - Structure elements: header, footer, main layout, breadcrumbs
 *    - Navigation: menus, sidebar, responsive containers
 *    - Configuration: layout-types.ts, layout-values.ts, layout-utils.ts, index.ts
 *
 * 🔗 PROVIDER COMPONENTS (components/providers/):
 *    - Context providers: auto-save, notifications, layout state
 *    - Application-wide state management and configuration
 *    - No separate modular structure (providers are typically single-purpose)
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 🛠️ MODIFICATION GUIDELINES
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * 🔧 TO MODIFY COMPONENT SETTINGS:
 *    - UI Components → components/ui/ui-values.ts
 *    - Layout Components → components/layout/layout-values.ts
 *    - Provider Components → modify individual provider files
 *
 * 📚 FOR TYPE DEFINITIONS:
 *    - UI Components → components/ui/ui-types.ts
 *    - Layout Components → components/layout/layout-types.ts
 *    - Provider Components → check individual provider files
 *
 * 🛠️ FOR UTILITY FUNCTIONS:
 *    - UI Components → components/ui/ui-utils.ts
 *    - Layout Components → components/layout/layout-utils.ts
 *    - Provider Components → check individual provider files
 *
 * ✅ SAFE PRACTICES:
 *   - Import from category-specific index files: @/components/ui, @/components/layout
 *   - Import from this main index for convenience: @/components
 *   - Modify configurations in designated -values.ts files
 *   - Add new components to appropriate categories
 *
 * ❌ AVOID:
 *   - Modifying this barrel export file directly
 *   - Importing individual component files directly
 *   - Hardcoding configuration values in component files
 *   - Creating components outside the established category structure
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📦 IMPORT EXAMPLES
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * // Import everything from main components entry point
 * import { Button, MainLayout, AutoSaveProvider } from '@/components'
 *
 * // Import from specific categories for better tree-shaking
 * import { Button, DEFAULT_BUTTON_CONFIG } from '@/components/ui'
 * import { MainLayout, RESPONSIVE_BREAKPOINTS } from '@/components/layout'
 *
 * // Import configurations and utilities
 * import { createNotification, getComponentClasses } from '@/components/ui'
 * import { getCurrentBreakpoint, isDesktop } from '@/components/layout'
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 🎯 CURRENT STATE: FULLY MODULARIZED ARCHITECTURE
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ✅ UI Components: Modularized with types, values, utils, and barrel export
 * ✅ Layout Components: Modularized with types, values, utils, and barrel export
 * ✅ Provider Components: Individual files with clear responsibilities
 * ✅ Main Components: Unified barrel export (this file)
 *
 * This architecture provides:
 * - Clear separation of concerns
 * - Easy configuration management
 * - Consistent patterns across component categories
 * - Type safety and developer experience
 * - Scalability for future component additions
 */

// Re-export all UI components and utilities
export * from './ui';

// Re-export all layout components and utilities
export * from './layout';

// Re-export provider components directly (no modular structure needed for providers)
export { AutoSaveProvider, useAutoSaveContext } from './providers/AutoSaveProvider';

/**
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 🚀 CONVENIENCE FUNCTIONS AND UNIFIED UTILITIES
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * These functions combine utilities from different component categories
 * for common use cases and improved developer experience.
 */

import { getDeviceType as getUIDeviceType } from './ui/ui-utils';
import { isDesktop, isMobile, isTablet } from './layout/layout-utils';
import { DEFAULT_UI_COMPONENT_CONFIG } from './ui/ui-values';
import { DEFAULT_LAYOUT_COMPONENT_CONFIG } from './layout/layout-values';

// Unified device detection combining UI and layout utilities
export const getUnifiedDeviceInfo = () => {
  return {
    type: getUIDeviceType(),
    isDesktop: isDesktop(),
    isMobile: isMobile(),
    isTablet: isTablet(),
    capabilities: {
      touch: typeof window !== 'undefined' && ('ontouchstart' in window || navigator.maxTouchPoints > 0),
      hover: typeof window !== 'undefined' && window.matchMedia('(hover: hover)').matches,
      motion: typeof window !== 'undefined' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    },
  };
};

// Unified configuration object combining all component configs
export const getUnifiedComponentConfig = () => {
  return {
    ui: DEFAULT_UI_COMPONENT_CONFIG,
    layout: DEFAULT_LAYOUT_COMPONENT_CONFIG,
  };
};

// Component health check for debugging
export const getComponentSystemHealth = () => {
  const errors: string[] = [];
  const warnings: string[] = [];

  try {
    // Check if required configs are available
    if (!DEFAULT_UI_COMPONENT_CONFIG) errors.push('UI configuration missing');
    if (!DEFAULT_LAYOUT_COMPONENT_CONFIG) warnings.push('Layout configuration missing');

    // Check browser environment
    if (typeof window === 'undefined') {
      warnings.push('Running in SSR environment - some features may be limited');
    }

    return {
      healthy: errors.length === 0,
      errors,
      warnings,
      timestamp: new Date().toISOString(),
    };
  } catch (error) {
    return {
      healthy: false,
      errors: ['Component system health check failed'],
      warnings: [],
      timestamp: new Date().toISOString(),
    };
  }
};

/**
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📊 COMPONENT SYSTEM METADATA
 * ═══════════════════════════════════════════════════════════════════════════════════
 */

// Component categories with metadata
export const COMPONENT_CATEGORIES = {
  UI: {
    name: 'UI Components',
    description: 'Interactive elements and content components',
    configurable: true,
    modularized: true,
    count: 6, // Update as components are added
  },
  LAYOUT: {
    name: 'Layout Components',
    description: 'Structure and navigation components',
    configurable: true,
    modularized: true,
    count: 4, // Update as components are added
  },
  PROVIDERS: {
    name: 'Provider Components',
    description: 'Context providers and state management',
    configurable: false,
    modularized: false,
    count: 1, // Update as providers are added
  },
} as const;

// Architecture metadata for debugging and development
export const COMPONENT_ARCHITECTURE_INFO = {
  version: '1.0.0',
  pattern: 'modular-barrel-export',
  categories: Object.keys(COMPONENT_CATEGORIES).length,
  totalComponents: Object.values(COMPONENT_CATEGORIES).reduce((sum, cat) => sum + cat.count, 0),
  features: {
    configurable: true,
    typeSafe: true,
    responsive: true,
    accessible: true,
    themeable: true,
    testable: true,
  },
  lastUpdated: '2025-01-24',
} as const;

// Category information is already exported above as const declarations

/**
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📝 USAGE NOTES FOR DEVELOPERS
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * 1. PREFERRED IMPORT PATTERNS:
 *    ✅ import { Button, MainLayout } from '@/components'
 *    ✅ import { Button } from '@/components/ui'
 *    ❌ import { Button } from '@/components/ui/Button'
 *
 * 2. CONFIGURATION CHANGES:
 *    ✅ Modify ui-values.ts for UI component defaults
 *    ✅ Modify layout-values.ts for layout component defaults
 *    ❌ Hardcode values in component files
 *
 * 3. ADDING NEW COMPONENTS:
 *    ✅ Add to appropriate category (ui/ or layout/)
 *    ✅ Follow the established patterns (types, values, utils)
 *    ✅ Export from category index.ts, then from this main index
 *    ❌ Create components outside the category structure
 *
 * 4. DEBUGGING:
 *    ✅ Use getComponentSystemHealth() for system status
 *    ✅ Use COMPONENT_ARCHITECTURE_INFO for metadata
 *    ✅ Check individual component configurations in -values.ts files
 *
 * 5. PERFORMANCE:
 *    ✅ Tree-shaking works with this barrel export pattern
 *    ✅ Import only what you need for optimal bundle size
 *    ✅ Use category-specific imports for better granularity
 */
