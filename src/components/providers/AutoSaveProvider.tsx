"use client";

import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';
import { AutoSaveConfig, AutoSaveStatus, generateStorageKey, isDataExpired, COMPLETE_AUTO_SAVE_CONFIG } from '../../../config/features.config';

interface AutoSaveData {
  [key: string]: unknown;
}

interface AutoSaveItem {
  data: AutoSaveData;
  timestamp: number;
  formType: string;
}

interface AutoSaveContextType {
  // Configuration
  config: AutoSaveConfig;
  updateConfig: (newConfig: Partial<AutoSaveConfig>) => void;

  // Auto-save operations
  saveData: (formType: string, data: AutoSaveData, userId?: string) => Promise<void>;
  loadData: (formType: string, userId?: string) => AutoSaveData | null;
  clearData: (formType: string, userId?: string) => void;
  clearAllData: () => void;

  // Status management
  getStatus: (formType: string) => AutoSaveStatus;
  setStatus: (formType: string, status: AutoSaveStatus) => void;



  // Global state
  isEnabled: boolean;
  setEnabled: (enabled: boolean) => void;
}

const AutoSaveContext = createContext<AutoSaveContextType | undefined>(undefined);

interface AutoSaveProviderProps {
  children: React.ReactNode;
  initialConfig?: Partial<AutoSaveConfig>;
}

export const AutoSaveProvider: React.FC<AutoSaveProviderProps> = ({
  children,
  initialConfig = {}
}) => {
  const [config, setConfig] = useState<AutoSaveConfig>({
    ...COMPLETE_AUTO_SAVE_CONFIG,
    ...initialConfig,
  });

  const [statusMap, setStatusMap] = useState<Record<string, AutoSaveStatus>>({});
  const [isEnabled, setEnabled] = useState(config.enabled);

  // Status management
  const setStatus = useCallback((formType: string, status: AutoSaveStatus) => {
    setStatusMap(prev => ({ ...prev, [formType]: status }));
  }, []);

  // Clean up expired data on mount
  useEffect(() => {
    const cleanupExpiredData = () => {
      try {
        const keys = Object.keys(localStorage);
        const prefix = config.storage.prefix;

        keys.forEach(key => {
          if (key.startsWith(prefix)) {
            try {
              const stored = localStorage.getItem(key);
              if (stored) {
                const parsed: AutoSaveItem = JSON.parse(stored);
                if (isDataExpired(parsed.timestamp)) {
                  localStorage.removeItem(key);
                  console.log(`Cleaned up expired auto-save data: ${key}`);
                }
              }
            } catch (error) {
              // Remove corrupted data
              localStorage.removeItem(key);
              console.warn(`Removed corrupted auto-save data: ${key}`, error);
            }
          }
        });
      } catch (error) {
        console.warn('Failed to cleanup expired auto-save data:', error);
      }
    };

    cleanupExpiredData();
  }, [config.storage.prefix]);

  const updateConfig = useCallback((newConfig: Partial<AutoSaveConfig>) => {
    setConfig(prev => ({ ...prev, ...newConfig }));
  }, []);

  const saveData = useCallback(async (
    formType: string,
    data: AutoSaveData,
    userId?: string
  ): Promise<void> => {
    if (!isEnabled || !config.forms[formType]?.enabled) return;

    try {
      setStatus(formType, 'saving');

      const storageKey = generateStorageKey(formType, userId);
      const saveItem: AutoSaveItem = {
        data,
        timestamp: Date.now(),
        formType,
      };

      const serializedData = JSON.stringify(saveItem);
      localStorage.setItem(storageKey, serializedData);

      setStatus(formType, 'saved');

      // Auto-clear status after configured time
      setTimeout(() => {
        setStatus(formType, 'idle');
      }, config.timeouts.showStatus);

    } catch (error) {
      console.error('Failed to auto-save data:', error);
      setStatus(formType, 'error');

      setTimeout(() => {
        setStatus(formType, 'idle');
      }, config.timeouts.showStatus);
    }
  }, [isEnabled, config.forms, config.timeouts.showStatus, setStatus]);

  const loadData = useCallback((formType: string, userId?: string): AutoSaveData | null => {
    if (!isEnabled) return null;

    try {
      const storageKey = generateStorageKey(formType, userId);
      const stored = localStorage.getItem(storageKey);

      if (!stored) return null;

      const parsed: AutoSaveItem = JSON.parse(stored);

      // Check if data is expired
      if (isDataExpired(parsed.timestamp)) {
        localStorage.removeItem(storageKey);
        return null;
      }

      return parsed.data;
    } catch (error) {
      console.warn('Failed to load auto-saved data:', error);
      return null;
    }
  }, [isEnabled]);

  const clearData = useCallback((formType: string, userId?: string) => {
    try {
      const storageKey = generateStorageKey(formType, userId);
      localStorage.removeItem(storageKey);
      setStatus(formType, 'idle');
    } catch (error) {
      console.warn('Failed to clear auto-save data:', error);
    }
  }, [setStatus]);

  const clearAllData = useCallback(() => {
    try {
      const keys = Object.keys(localStorage);
      const prefix = config.storage.prefix;

      keys.forEach(key => {
        if (key.startsWith(prefix)) {
          localStorage.removeItem(key);
        }
      });

      setStatusMap({});
    } catch (error) {
      console.warn('Failed to clear all auto-save data:', error);
    }
  }, [config.storage.prefix]);

  const getStatus = useCallback((formType: string): AutoSaveStatus => {
    return statusMap[formType] || 'idle';
  }, [statusMap]);



  const contextValue: AutoSaveContextType = {
    config,
    updateConfig,
    saveData,
    loadData,
    clearData,
    clearAllData,
    getStatus,
    setStatus,
    isEnabled,
    setEnabled,
  };

  return (
    <AutoSaveContext.Provider value={contextValue}>
      {children}
    </AutoSaveContext.Provider>
  );
};

// Custom hook to use auto-save context
export const useAutoSaveContext = (): AutoSaveContextType => {
  const context = useContext(AutoSaveContext);
  if (context === undefined) {
    throw new Error('useAutoSaveContext must be used within an AutoSaveProvider');
  }
  return context;
};
