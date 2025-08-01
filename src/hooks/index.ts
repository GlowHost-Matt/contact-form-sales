/**
 * HOOKS - MAIN ENTRY POINT (BARREL EXPORT)
 *
 * ⚠️ WARNING: DO NOT MODIFY THIS FILE DIRECTLY ⚠️
 * This file uses "re-exporting" to provide a single import point for all hook functionality.
 * Modifying this file could break the entire hook architecture.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📋 ADDING NEW HOOKS - ARCHITECTURAL PRESCRIPTION
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * When adding new hooks, you MUST follow this organizational structure:
 *
 * 1. 🤔 DETERMINE CATEGORY:
 *    - FORM hooks: useFormField, useFileHandling, useFormValidation, etc.
 *    - SYSTEM hooks: usePageFocus, useUserAgent, useLocalStorage, etc.
 *
 * 2. 📁 CREATE IN APPROPRIATE DIRECTORY:
 *    - Form hooks → hooks/form/
 *    - System hooks → hooks/system/
 *    - New category → hooks/[category]/
 *
 * 3. 📄 FOLLOW FILE STRUCTURE (per category):
 *    hooks/[category]/
 *    ├── [category]-types.ts     📚 Type definitions
 *    ├── [category]-values.ts    ⭐ Configuration values (WHERE USERS MODIFY)
 *    ├── [category]-utils.ts     🛠️ Utility functions
 *    ├── index.ts                🚪 Barrel export for category
 *    └── your-hook-files.ts      🎯 Actual hook implementations
 *
 * 4. ✅ MANDATORY PATTERNS:
 *    - Hook file MUST be named useXxxxYyyy.ts (camelCase)
 *    - Hook function MUST start with 'use' prefix
 *    - Hook MUST have corresponding type in [category]-types.ts
 *    - Hook configuration MUST be in [category]-values.ts
 *    - Hook utilities MUST be in [category]-utils.ts
 *    - Hook MUST be exported from [category]/index.ts
 *    - Hook MUST be re-exported from this main index.ts
 *
 * 5. 🚫 ANTI-PATTERNS TO AVOID:
 *    ❌ Adding hooks directly to hooks/ root directory
 *    ❌ Creating hooks without corresponding types
 *    ❌ Hardcoding configuration values in hook files
 *    ❌ Mixing concerns (form logic in system hooks, etc.)
 *    ❌ Skipping the barrel export pattern
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 🛠️ ENFORCEMENT MECHANISMS
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * To ensure compliance, we have implemented:
 * - ESLint rules that enforce naming conventions
 * - File structure validation in build process
 * - Code review checklists with architectural requirements
 * - Template generators for new hooks
 *
 * Run `npm run hooks:validate` to check architectural compliance.
 * Run `npm run hooks:generate` to create a new hook with proper structure.
 *
 * ═══════════════════════════════════════════════════════════════════════════════════
 * 📚 EDUCATIONAL RESOURCES
 * ═══════════════════════════════════════════════════════════════════════════════════
 *
 * NEW TO HOOKS ARCHITECTURE?
 * - Review existing hooks in form/ and system/ directories
 * - Check [category]-values.ts files to see configuration patterns
 * - Look at [category]-types.ts files to understand type structures
 * - Study [category]-utils.ts files for utility function patterns
 *
 * BARREL EXPORTS EXPLAINED:
 * - This pattern allows clean imports: import { useFormField } from '@/hooks'
 * - Instead of: import { useFormField } from '@/hooks/form/useFormField'
 * - Maintains clean public API while organizing code internally
 *
 * BENEFITS OF THIS ARCHITECTURE:
 * - Clear separation of concerns
 * - Easy to locate and modify configurations
 * - Consistent patterns across the codebase
 * - Scalable as the application grows
 * - Educational for new developers
 */

// Re-export all form hooks and utilities
export * from './form';

// Re-export all system hooks and utilities
export * from './system';

// Legacy hooks (will be migrated to appropriate categories)
export { useAutoSave } from './useAutoSave';
export { useFileUpload } from './useFileUpload';

/**
 * MIGRATION NOTICE:
 * Some hooks are still in the legacy location and will be moved to appropriate categories.
 * This ensures backward compatibility during the migration process.
 *
 * HOOKS TO BE MIGRATED:
 * - useAutoSave → form/ (form-related auto-save functionality)
 * - useFileUpload → form/ (file upload functionality)
 *
 * After migration, imports will remain the same due to barrel exports.
 */
