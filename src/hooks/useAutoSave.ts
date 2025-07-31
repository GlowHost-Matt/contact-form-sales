import { useState, useEffect, useCallback, useRef } from 'react';
import { useAutoSaveContext } from '@/components/providers/AutoSaveProvider';
import { AutoSaveStatus } from '../../config/features.config';

interface UseAutoSaveOptions {
  formType: string;
  userId?: string;
  fields?: string[];
  enabled?: boolean;
  onSave?: () => void;
  onError?: (error: Error) => void;
}

interface UseAutoSaveReturn {
  // Status
  status: AutoSaveStatus;
  isLoading: boolean;

  // Operations
  save: (data: Record<string, unknown>) => Promise<void>;
  load: () => Record<string, unknown> | null;
  clear: () => void;

  // Auto-save management
  enableAutoSave: () => void;
  disableAutoSave: () => void;
  isAutoSaveEnabled: boolean;

  // Session management (NEW for Option C)
  isInSession: boolean;
  startSession: () => void;
  endSession: () => void;
}

export const useAutoSave = (
  dataToSave: Record<string, unknown>,
  options: UseAutoSaveOptions
): UseAutoSaveReturn => {
  const {
    config,
    saveData,
    loadData,
    clearData,
    getStatus,
    isEnabled: globalEnabled,
    setStatus,
  } = useAutoSaveContext();

  const {
    formType,
    userId,
    fields,
    enabled = true,
    onSave,
    onError,
  } = options;

  const [isAutoSaveEnabled, setIsAutoSaveEnabled] = useState(enabled);
  const [isInSession, setIsInSession] = useState(false);

  const saveTimeoutRef = useRef<NodeJS.Timeout>();
  const debounceTimeoutRef = useRef<NodeJS.Timeout>();
  const sessionTimeoutRef = useRef<NodeJS.Timeout>();
  const immediateResponseTimeoutRef = useRef<NodeJS.Timeout>();
  const lastSavedDataRef = useRef<string>('');

  // Get form-specific configuration
  const formConfig = config.forms[formType];
  const saveTimeout = formConfig?.saveTimeout ?? config.timeouts.save;
  const debounceTime = config.timeouts.debounce;
  const sessionTimeout = formConfig?.sessionBehavior?.inactivityTimeout ?? config.timeouts.sessionTimeout;
  const immediateResponseTime = config.timeouts.immediateResponse;

  // Current status from context
  const status = getStatus(formType);
  const isLoading = status === 'saving';

  // Session management functions
  const startSession = useCallback(() => {


    if (!config.ui.enableSessionMode || !formConfig?.sessionBehavior?.enabled) {
      return;
    }

    setIsInSession(true);

    // Clear any existing session timeout
    if (sessionTimeoutRef.current) {
      clearTimeout(sessionTimeoutRef.current);
    }

    // Show immediate "session-active" feedback
    if (formConfig.sessionBehavior.immediateStart) {
      if (immediateResponseTimeoutRef.current) {
        clearTimeout(immediateResponseTimeoutRef.current);
      }

      immediateResponseTimeoutRef.current = setTimeout(() => {
        setStatus(formType, 'session-active');

      }, immediateResponseTime);
    }
  }, [formType, config.ui.enableSessionMode, formConfig, setStatus, immediateResponseTime]);

  const endSession = useCallback(() => {


    setIsInSession(false);
    setStatus(formType, 'idle');

    // Clear all timeouts
    if (sessionTimeoutRef.current) {
      clearTimeout(sessionTimeoutRef.current);
    }
    if (immediateResponseTimeoutRef.current) {
      clearTimeout(immediateResponseTimeoutRef.current);
    }
  }, [formType, setStatus]);

  const resetSessionTimeout = useCallback(() => {
    if (!isInSession || !config.ui.enableSessionMode) return;

    // Clear existing session timeout
    if (sessionTimeoutRef.current) {
      clearTimeout(sessionTimeoutRef.current);
    }

    // Set new session timeout
    sessionTimeoutRef.current = setTimeout(() => {

      endSession();
    }, sessionTimeout);
  }, [isInSession, sessionTimeout, endSession, formType, config.ui.enableSessionMode]);



  // Filter data by configured fields
  const getFilteredData = useCallback((data: Record<string, unknown>) => {
    if (!fields || fields.length === 0) return data;

    const filtered: Record<string, unknown> = {};
    fields.forEach(field => {
      if (data[field] !== undefined) {
        filtered[field] = data[field];
      }
    });
    return filtered;
  }, [fields]);

  // Check if data has actually changed
  const hasDataChanged = useCallback((data: Record<string, unknown>) => {
    const filteredData = getFilteredData(data);
    const serialized = JSON.stringify(filteredData);
    const changed = serialized !== lastSavedDataRef.current;

    if (changed) {

      // Start session on data change
      if (!isInSession) {
        startSession();
      } else {
        resetSessionTimeout();
      }
    }

    return changed;
  }, [getFilteredData, formType, isInSession, startSession, resetSessionTimeout]);

  // Manual save function
  const save = useCallback(async (data: Record<string, unknown>) => {
    if (!globalEnabled || !isAutoSaveEnabled) return;

    try {
      const filteredData = getFilteredData(data);
      await saveData(formType, filteredData, userId);
      lastSavedDataRef.current = JSON.stringify(filteredData);
      onSave?.();

      // After save, return to session-active if in session, otherwise idle
      if (isInSession && config.ui.enableSessionMode) {
        // Brief "saved" confirmation, then back to session-active
        setTimeout(() => {
          setStatus(formType, 'session-active');
          resetSessionTimeout();
        }, config.timeouts.showStatus);
      }

    } catch (error) {
      onError?.(error as Error);
    }
  }, [globalEnabled, isAutoSaveEnabled, getFilteredData, saveData, formType, userId, onSave, onError, isInSession, config.ui.enableSessionMode, config.timeouts.showStatus, setStatus, resetSessionTimeout]);

  // Load function
  const load = useCallback(() => {
    if (!globalEnabled) return null;
    return loadData(formType, userId);
  }, [globalEnabled, loadData, formType, userId]);

  // Clear function
  const clear = useCallback(() => {
    clearData(formType, userId);
    lastSavedDataRef.current = '';
    endSession();

    // Clear all timeouts
    if (saveTimeoutRef.current) {
      clearTimeout(saveTimeoutRef.current);
    }
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }
    if (sessionTimeoutRef.current) {
      clearTimeout(sessionTimeoutRef.current);
    }
    if (immediateResponseTimeoutRef.current) {
      clearTimeout(immediateResponseTimeoutRef.current);
    }
  }, [clearData, formType, userId, endSession]);



  // Auto-save management
  const enableAutoSave = useCallback(() => {
    setIsAutoSaveEnabled(true);
  }, []);

  const disableAutoSave = useCallback(() => {
    setIsAutoSaveEnabled(false);
    clear();
  }, [clear]);

  // Auto-save effect with debouncing and session management
  useEffect(() => {
    if (!globalEnabled || !isAutoSaveEnabled || !formConfig?.enabled) {
      return;
    }

    // Clear existing timeouts
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }

    // Check if data has changed (this also manages session start/continue)
    if (!hasDataChanged(dataToSave)) {
      return;
    }

    // Debounce rapid changes
    debounceTimeoutRef.current = setTimeout(() => {
      // Clear existing save timeout
      if (saveTimeoutRef.current) {
        clearTimeout(saveTimeoutRef.current);
      }

      // Set new save timeout
      saveTimeoutRef.current = setTimeout(() => {
        save(dataToSave);
      }, saveTimeout);
    }, debounceTime);

    // Cleanup function
    return () => {
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current);
      }
      if (saveTimeoutRef.current) {
        clearTimeout(saveTimeoutRef.current);
      }
    };
  }, [globalEnabled, isAutoSaveEnabled, formConfig?.enabled, dataToSave, hasDataChanged, save, saveTimeout, debounceTime]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (sessionTimeoutRef.current) {
        clearTimeout(sessionTimeoutRef.current);
      }
      if (immediateResponseTimeoutRef.current) {
        clearTimeout(immediateResponseTimeoutRef.current);
      }
    };
  }, []);

  return {
    // Status
    status,
    isLoading,

    // Operations
    save,
    load,
    clear,

    // Auto-save management
    enableAutoSave,
    disableAutoSave,
    isAutoSaveEnabled,

    // Session management (NEW for Option C)
    isInSession,
    startSession,
    endSession,
  };
};
