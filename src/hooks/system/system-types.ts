/**
 * SYSTEM HOOK TYPES AND INTERFACES
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE ⚠️
 * This file contains TypeScript type definitions and interfaces for system-related hooks.
 * Modifying this file could break the entire system hook functionality across the application.
 *
 * 🔧 TO MODIFY SYSTEM HOOK SETTINGS:
 *    Go to → system-values.ts
 *    This is where you change performance monitoring, user agent detection, and storage configs.
 *
 * 📚 FOR UTILITY FUNCTIONS:
 *    See → system-utils.ts
 *    Contains helper functions for user agent detection, performance monitoring, and system operations.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * REPERCUSSIONS OF MODIFYING THIS FILE:
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * ❌ BREAKING CHANGES:
 *   - Could break TypeScript compilation across the entire application
 *   - May cause runtime errors in user agent detection and performance monitoring
 *   - Interface changes affect all system-level components and functionality
 *   - Breaking changes propagate to device detection and responsive behavior
 *
 * ❌ ARCHITECTURAL ISSUES:
 *   - Defeats the purpose of configuration separation
 *   - Creates confusion about where actual system settings are defined
 *   - Makes the system monitoring harder to maintain and debug
 *   - Violates the single responsibility principle
 *
 * ✅ SAFE PRACTICES:
 *   - Modify configurations in system-values.ts
 *   - Add new types following existing patterns and consulting the team
 *   - For utility functions: See system-utils.ts
 *   - Test thoroughly if type modifications are absolutely necessary
 *
 * This file defines the contracts that system hook configuration objects must follow.
 * Configuration values are defined in system-values.ts
 */

// Page focus hook interface
export interface PageFocusState {
  isVisible: boolean;
  hasFocus: boolean;
  lastFocusTime: number | null;
  lastBlurTime: number | null;
}

export interface PageFocusReturn<T = HTMLElement> {
  ref: React.RefObject<T>;
  isVisible: boolean;
  hasFocus: boolean;
  focusElement: () => void;
}

// User agent detection types
export interface UserAgentInfo {
  userAgent: string;
  browserName: string;
  browserVersion: string;
  operatingSystem: string;
  deviceType: 'desktop' | 'tablet' | 'mobile';
  isBot: boolean;
}

export interface UserAgentReturn {
  userAgent: UserAgentInfo;
  isLoading: boolean;
  error: string | null;
  refresh: () => void;
}

// Auto-save hook types (these might already exist but including for completeness)
export interface AutoSaveOptions {
  formType: string;
  userId?: string;
  fields?: string[];
  enabled?: boolean;
  onRecover?: (data: Record<string, unknown>) => void;
  onSave?: () => void;
  onError?: (error: Error) => void;
}

export interface AutoSaveReturn {
  status: 'idle' | 'saving' | 'saved' | 'error' | 'recovered';
  isLoading: boolean;
  hasRecoverableData: boolean;
  save: (data: Record<string, unknown>) => Promise<void>;
  load: () => Record<string, unknown> | null;
  clear: () => void;
  recover: () => boolean;
  enableAutoSave: () => void;
  disableAutoSave: () => void;
  isAutoSaveEnabled: boolean;
  showRecoveryPrompt: boolean;
  dismissRecoveryPrompt: () => void;
  acceptRecovery: () => void;
}

// System performance monitoring types
export interface PerformanceMetrics {
  loadTime: number;
  renderTime: number;
  memoryUsage?: number;
  connectionType?: string;
}

export interface PerformanceReturn {
  metrics: PerformanceMetrics;
  isMonitoring: boolean;
  startMonitoring: () => void;
  stopMonitoring: () => void;
  resetMetrics: () => void;
}

// Local storage hook types
export interface StorageOptions {
  key: string;
  defaultValue?: unknown;
  serializer?: {
    parse: (value: string) => unknown;
    stringify: (value: unknown) => string;
  };
  syncAcrossTabs?: boolean;
}

export interface StorageReturn<T> {
  value: T;
  setValue: (value: T | ((prev: T) => T)) => void;
  remove: () => void;
  isLoading: boolean;
  error: string | null;
}
