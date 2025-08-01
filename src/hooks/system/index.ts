/**
 * SYSTEM HOOKS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file uses "re-exporting" to provide a single import point for all system hook functionality.
 * Modifying this file could break the entire system hook architecture.
 *

 *
 * This file maintains backward compatibility by re-exporting everything.
 * You can import from '@/hooks/system' for all system-related functionality.
 */

// Re-export types and interfaces
export type {
  PageFocusState,
  PageFocusReturn,
  UserAgentInfo,
  UserAgentReturn,
  AutoSaveOptions,
  AutoSaveReturn,
  PerformanceMetrics,
  PerformanceReturn,
  StorageOptions,
  StorageReturn,
} from './system-types';

// Re-export configuration values ⭐ MODIFY THESE IN system-values.ts ⭐
export {
  PAGE_FOCUS_CONFIG,
  USER_AGENT_CONFIG,
  PERFORMANCE_CONFIG,
  STORAGE_CONFIG,
  SYSTEM_HOOK_DEFAULTS,
} from './system-values';

// Re-export utility functions
export {
  parseUserAgent,
  getDeviceType,
  isBot,
  getCurrentPerformanceMetrics,
  isPerformanceThresholdExceeded,
  formatPerformanceMetrics,
  generateStorageKey,
  isLocalStorageAvailable,
  getStorageUsage,
  cleanupExpiredStorage,
  isFocusable,
  getFocusableElements,
  createFocusTrap,
  debounce,
  throttle,
  generateId,
} from './system-utils';

// Re-export actual hooks (will be moved here)
export { usePageFocus } from '../usePageFocus';
export { useUserAgent } from '../useUserAgent';
