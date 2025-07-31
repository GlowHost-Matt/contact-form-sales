/**
 * SYSTEM HOOK UTILITY FUNCTIONS
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains helper functions for system-related hooks.
 * Modifying this file could break system functionality across the entire application.
 *
 * 🔧 TO MODIFY SYSTEM HOOK SETTINGS:
 *    Go to → system-values.ts
 *    This is where you change performance monitoring, user agent detection, and storage configs.
 *
 * 📚 FOR TYPE DEFINITIONS:
 *    See → system-types.ts
 *    Contains interfaces and type definitions for system hooks.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break user agent detection and device type recognition
 *   - May cause performance monitoring failures
 *   - Function signature changes affect all system-level components
 *   - Breaking changes propagate throughout the application
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual system settings are defined
 *   - Makes the system monitoring harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in system-values.ts
 *   - Add new utility functions following existing patterns
 *   - Consult with the development team before function changes
 *   - Test thoroughly if utility modifications are absolutely necessary
 *
 * These utilities support user agent detection, performance monitoring, and system operations.
 * For actual configuration values, see system-values.ts
 */

import type { UserAgentInfo, PerformanceMetrics } from './system-types';
import { USER_AGENT_CONFIG, PERFORMANCE_CONFIG, STORAGE_CONFIG } from './system-values';

/**
 * USER AGENT UTILITIES
 */

// Parse user agent string to extract browser information
export const parseUserAgent = (userAgentString?: string): UserAgentInfo => {
  const ua = userAgentString || (typeof window !== 'undefined' ? window.navigator.userAgent : '');
  const { browserPatterns, osPatterns, botPatterns } = USER_AGENT_CONFIG;

  // Detect browser
  let browserName = 'Unknown';
  let browserVersion = '';

  for (const [browser, pattern] of Object.entries(browserPatterns)) {
    if (pattern.test(ua)) {
      browserName = browser.charAt(0).toUpperCase() + browser.slice(1);
      // Extract version (simplified)
      const versionMatch = ua.match(new RegExp(`${browser}[/\\s]([\\d.]+)`, 'i'));
      browserVersion = versionMatch ? versionMatch[1] : '';
      break;
    }
  }

  // Detect operating system
  let operatingSystem = 'Unknown';
  for (const [os, pattern] of Object.entries(osPatterns)) {
    if (pattern.test(ua)) {
      operatingSystem = os.charAt(0).toUpperCase() + os.slice(1);
      break;
    }
  }

  // Detect device type
  const deviceType = getDeviceType();

  // Detect if it's a bot
  const isBot = botPatterns.some(pattern => pattern.test(ua));

  return {
    userAgent: ua,
    browserName,
    browserVersion,
    operatingSystem,
    deviceType,
    isBot,
  };
};

// Get device type based on screen size
export const getDeviceType = (): 'desktop' | 'tablet' | 'mobile' => {
  if (typeof window === 'undefined') return 'desktop';

  const { deviceBreakpoints } = USER_AGENT_CONFIG;
  const width = window.innerWidth;

  if (width < deviceBreakpoints.mobile) return 'mobile';
  if (width < deviceBreakpoints.tablet) return 'tablet';
  return 'desktop';
};

// Check if current user agent is a bot
export const isBot = (userAgentString?: string): boolean => {
  const ua = userAgentString || (typeof window !== 'undefined' ? window.navigator.userAgent : '');
  return USER_AGENT_CONFIG.botPatterns.some(pattern => pattern.test(ua));
};

/**
 * PERFORMANCE UTILITIES
 */

// Get current performance metrics
export const getCurrentPerformanceMetrics = (): PerformanceMetrics => {
  const defaultMetrics: PerformanceMetrics = {
    loadTime: 0,
    renderTime: 0,
  };

  if (typeof window === 'undefined' || !window.performance) {
    return defaultMetrics;
  }

  const perfData = window.performance;
  const timing = perfData.timing;

  const metrics: PerformanceMetrics = {
    loadTime: timing.loadEventEnd - timing.navigationStart,
    renderTime: timing.domContentLoadedEventEnd - timing.domContentLoadedEventStart,
  };

  // Add memory usage if available
  if ('memory' in perfData) {
    const memory = (perfData as any).memory;
    metrics.memoryUsage = memory.usedJSHeapSize / (1024 * 1024); // Convert to MB
  }

  // Add connection info if available
  if ('connection' in navigator) {
    const connection = (navigator as any).connection;
    metrics.connectionType = connection.effectiveType || connection.type || 'unknown';
  }

  return metrics;
};

// Check if performance metric exceeds threshold
export const isPerformanceThresholdExceeded = (
  metric: keyof typeof PERFORMANCE_CONFIG.thresholds,
  value: number
): boolean => {
  const threshold = PERFORMANCE_CONFIG.thresholds[metric];
  return value > threshold;
};

// Format performance metrics for display
export const formatPerformanceMetrics = (metrics: PerformanceMetrics): Record<string, string> => {
  return {
    loadTime: `${metrics.loadTime.toFixed(2)}ms`,
    renderTime: `${metrics.renderTime.toFixed(2)}ms`,
    memoryUsage: metrics.memoryUsage ? `${metrics.memoryUsage.toFixed(2)}MB` : 'N/A',
    connectionType: metrics.connectionType || 'N/A',
  };
};

/**
 * STORAGE UTILITIES
 */

// Generate storage key with prefix
export const generateStorageKey = (key: string): string => {
  return `${STORAGE_CONFIG.prefix}${key}`;
};

// Check if localStorage is available
export const isLocalStorageAvailable = (): boolean => {
  try {
    if (typeof window === 'undefined') return false;
    const test = '__localStorage_test__';
    localStorage.setItem(test, 'test');
    localStorage.removeItem(test);
    return true;
  } catch {
    return false;
  }
};

// Get storage usage information
export const getStorageUsage = (): { used: number; available: number; percentage: number } => {
  if (!isLocalStorageAvailable()) {
    return { used: 0, available: 0, percentage: 0 };
  }

  let used = 0;
  for (const key in localStorage) {
    if (localStorage.hasOwnProperty(key)) {
      used += localStorage[key].length + key.length;
    }
  }

  const available = STORAGE_CONFIG.maxStorageSize;
  const percentage = (used / available) * 100;

  return { used, available, percentage };
};

// Clean up expired storage items
export const cleanupExpiredStorage = (prefix?: string): number => {
  if (!isLocalStorageAvailable()) return 0;

  const targetPrefix = prefix || STORAGE_CONFIG.prefix;
  let cleaned = 0;

  for (const key in localStorage) {
    if (key.startsWith(targetPrefix)) {
      try {
        const item = JSON.parse(localStorage[key]);
        if (item.expiration && Date.now() > item.expiration) {
          localStorage.removeItem(key);
          cleaned++;
        }
      } catch {
        // Invalid JSON, remove it
        localStorage.removeItem(key);
        cleaned++;
      }
    }
  }

  return cleaned;
};

/**
 * FOCUS UTILITIES
 */

// Check if element is focusable
export const isFocusable = (element: HTMLElement): boolean => {
  const focusableSelectors = [
    'a[href]',
    'area[href]',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    'button:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
    '[contenteditable="true"]',
  ];

  return focusableSelectors.some(selector => element.matches(selector));
};

// Get all focusable elements within a container
export const getFocusableElements = (container: HTMLElement): HTMLElement[] => {
  const focusableSelectors = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"]), [contenteditable="true"]';

  return Array.from(container.querySelectorAll(focusableSelectors)) as HTMLElement[];
};

// Create a focus trap within an element
export const createFocusTrap = (element: HTMLElement): (() => void) => {
  const focusableElements = getFocusableElements(element);
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];

  const handleTabKey = (e: KeyboardEvent) => {
    if (e.key !== 'Tab') return;

    if (e.shiftKey) {
      if (document.activeElement === firstElement) {
        e.preventDefault();
        lastElement?.focus();
      }
    } else {
      if (document.activeElement === lastElement) {
        e.preventDefault();
        firstElement?.focus();
      }
    }
  };

  element.addEventListener('keydown', handleTabKey);

  // Return cleanup function
  return () => {
    element.removeEventListener('keydown', handleTabKey);
  };
};

/**
 * GENERAL SYSTEM UTILITIES
 */

// Debounce function calls
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

// Throttle function calls
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

// Generate unique ID
export const generateId = (prefix = 'id'): string => {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
};
