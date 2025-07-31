"use client";

import React from 'react';
import { useAutoSaveContext } from '@/components/providers/AutoSaveProvider';
import { AutoSaveStatus } from '../../../config/features.config';
import { DEFAULT_FLOATING_AUTO_SAVE_INDICATOR_CONFIG } from './ui-values';
import type { FloatingAutoSaveIndicatorConfig } from './ui-types';

interface AutoSaveIndicatorProps {
  formType: string;
  className?: string;
  showText?: boolean;
  size?: 'sm' | 'md' | 'lg';
}

const getStatusConfig = (status: AutoSaveStatus) => {
  switch (status) {
    case 'saving':
      return {
        icon: '⏳',
        text: 'Saving...',
        className: 'text-blue-600 bg-blue-50 border-blue-200',
        pulseClass: 'animate-pulse',
      };
    case 'saved':
      return {
        icon: '✓',
        text: 'Saved',
        className: 'text-green-600 bg-green-50 border-green-200',
        pulseClass: '',
      };
    case 'session-active':
      return {
        icon: '💾',
        text: 'Auto-save active',
        className: 'text-blue-500 bg-blue-50 border-blue-100',
        pulseClass: '',
      };
    case 'error':
      return {
        icon: '⚠',
        text: 'Save failed',
        className: 'text-red-600 bg-red-50 border-red-200',
        pulseClass: '',
      };
    case 'recovered':
      return {
        icon: '🔄',
        text: 'Recovered',
        className: 'text-purple-600 bg-purple-50 border-purple-200',
        pulseClass: '',
      };
    case 'idle':
    default:
      return {
        icon: '💾',
        text: 'Auto-save ready',
        className: 'text-gray-600 bg-gray-50 border-gray-200',
        pulseClass: '',
      };
  }
};

export const AutoSaveIndicator: React.FC<AutoSaveIndicatorProps> = ({
  formType,
  className = '',
  showText = true,
  size = 'md',
}) => {
  const { getStatus, isEnabled } = useAutoSaveContext();
  const status = getStatus(formType);

  if (!isEnabled) return null;

  const statusConfig = getStatusConfig(status);

  const sizeClasses = {
    sm: 'px-2 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
    lg: 'px-4 py-2 text-base',
  };

  const iconSizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-lg',
  };

  return (
    <div
      className={`
        inline-flex items-center gap-2 border rounded-lg font-medium transition-all duration-200
        ${statusConfig.className}
        ${statusConfig.pulseClass}
        ${sizeClasses[size]}
        ${className}
      `}
    >
      <span className={`${iconSizes[size]} leading-none`}>
        {statusConfig.icon}
      </span>
      {showText && (
        <span className="leading-none">
          {statusConfig.text}
        </span>
      )}
    </div>
  );
};

// Floating auto-save indicator that uses the UI component configuration system
interface FloatingAutoSaveIndicatorProps {
  formType: string;
  config?: Partial<FloatingAutoSaveIndicatorConfig>;
  className?: string;
}

export const FloatingAutoSaveIndicator: React.FC<FloatingAutoSaveIndicatorProps> = ({
  formType,
  config: userConfig,
  className = '',
}) => {
  const { getStatus, isEnabled } = useAutoSaveContext();
  const status = getStatus(formType);
  const [isVisible, setIsVisible] = React.useState(false);

  // Get configuration from UI component system with user overrides
  const config = React.useMemo(() => {
    return { ...DEFAULT_FLOATING_AUTO_SAVE_INDICATOR_CONFIG, ...userConfig };
  }, [userConfig]);

  // Show the indicator when there's activity (saving, saved, error, recovered)
  React.useEffect(() => {
    if (!config.enabled) return;

    if (status === 'saving') {
      setIsVisible(true);
    } else if (status === 'saved') {
      setIsVisible(true);
      const timer = setTimeout(() => {
        setIsVisible(false);
      }, config.showDuration);
      return () => clearTimeout(timer);
    } else if (status === 'session-active') {
      setIsVisible(true);
    } else if (status === 'error') {
      setIsVisible(true);
    } else if (status === 'recovered') {
      setIsVisible(true);
    } else if (status === 'idle') {
      if (config.hideOnIdle) {
        setIsVisible(false);
      }
    }
  }, [status, config.enabled, config.showDuration, config.hideOnIdle, formType, isEnabled]);

  // Don't render if not enabled or auto-save is disabled
  if (!config.enabled || !isEnabled) return null;

  const statusConfig = getStatusConfig(status);

  // Generate responsive position classes from configuration
  const getPositionClasses = () => {
    const positions = config.position.split('-'); // ['bottom', 'right']
    const vPos = positions[0]; // 'bottom' | 'top'
    const hPos = positions[1]; // 'right' | 'left'

    // Map config spacing to Tailwind classes (16px = 4, 24px = 6)
    const spacingToTailwind = (spacing: number) => {
      if (spacing <= 8) return '2';   // 8px
      if (spacing <= 12) return '3';  // 12px
      if (spacing <= 16) return '4';  // 16px
      if (spacing <= 20) return '5';  // 20px
      if (spacing <= 24) return '6';  // 24px
      if (spacing <= 32) return '8';  // 32px
      return '6'; // Default fallback
    };

    const mobileClass = spacingToTailwind(config.spacing.mobile);
    const desktopClass = spacingToTailwind(config.spacing.desktop);

    return [
      vPos === 'top' ? `top-${mobileClass}` : `bottom-${mobileClass}`,
      hPos === 'left' ? `left-${mobileClass}` : `right-${mobileClass}`,
      vPos === 'top' ? `sm:top-${desktopClass}` : `sm:bottom-${desktopClass}`,
      hPos === 'left' ? `sm:left-${desktopClass}` : `sm:right-${desktopClass}`,
    ].join(' ');
  };

  // Don't render if not visible and not in an active state
  if (!isVisible && status !== 'saving' && status !== 'session-active' && status !== 'error') {
    return null;
  }

  return (
    <div
      className={`
        fixed pointer-events-none
        ${getPositionClasses()}
        ${config.enableAnimation ? 'transition-all duration-300 ease-in-out' : ''}
        ${isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'}
        ${className}
      `}
      style={{ zIndex: config.zIndex }}
      role="status"
      aria-live="polite"
      aria-atomic="true"
    >
      <div
        className={`
          inline-flex items-center gap-2 border rounded-lg font-medium
          px-3 py-2 text-sm pointer-events-auto
          ${config.enableShadow ? 'shadow-lg' : ''}
          ${config.enableBackdropBlur ? 'backdrop-blur-sm' : ''}
          ${statusConfig.className}
          ${config.enableAnimation ? statusConfig.pulseClass : ''}
        `}
      >
        <span className="text-sm leading-none">
          {statusConfig.icon}
        </span>
        {config.showText && (
          <span className="leading-none whitespace-nowrap">
            {statusConfig.text}
          </span>
        )}
      </div>
    </div>
  );
};



interface AutoSaveStatusBarProps {
  formType: string;
  position?: 'top' | 'bottom';
  className?: string;
}

export const AutoSaveStatusBar: React.FC<AutoSaveStatusBarProps> = ({
  formType,
  position = 'top',
  className = '',
}) => {
  const { getStatus, config, isEnabled } = useAutoSaveContext();
  const status = getStatus(formType);

  if (!isEnabled || !config.ui.showIndicator) return null;

  const isVisible = status === 'saving' || status === 'saved' || status === 'error';

  if (!isVisible) return null;

  const positionClasses = {
    top: 'top-0',
    bottom: 'bottom-0',
  };

  return (
    <div className={`
      fixed left-0 right-0 z-50 transition-all duration-300
      ${positionClasses[position]}
      ${className}
    `}>
      <div className="bg-white border-b border-gray-200 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 py-2">
          <div className="flex items-center justify-center">
            <AutoSaveIndicator formType={formType} size="sm" />
          </div>
        </div>
      </div>
    </div>
  );
};
