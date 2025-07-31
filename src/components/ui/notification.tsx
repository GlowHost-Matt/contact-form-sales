import React, { useEffect, useState } from 'react';
import { useNotifications } from './notification-context';

interface NotificationItemProps {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  dismissible?: boolean;
  onRemove: (id: string) => void;
}

const NotificationItem: React.FC<NotificationItemProps> = ({
  id,
  type,
  title,
  message,
  dismissible = true,
  onRemove,
}) => {
  const [isVisible, setIsVisible] = useState(false);
  const [isLeaving, setIsLeaving] = useState(false);

  useEffect(() => {
    // Trigger entrance animation
    setTimeout(() => setIsVisible(true), 10);
  }, []);

  const handleDismiss = () => {
    if (!dismissible) return;

    setIsLeaving(true);
    setTimeout(() => onRemove(id), 300);
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape' || e.key === 'Enter') {
      handleDismiss();
    }
  };

  const getTypeStyles = () => {
    switch (type) {
      case 'success':
        return {
          bg: 'bg-green-50 border-green-200',
          icon: '✅',
          iconBg: 'bg-green-100',
          iconColor: 'text-green-600',
          title: 'text-green-800',
          message: 'text-green-700',
          dismiss: 'text-green-400 hover:text-green-600',
        };
      case 'error':
        return {
          bg: 'bg-red-50 border-red-200',
          icon: '🚨',
          iconBg: 'bg-red-100',
          iconColor: 'text-red-600',
          title: 'text-red-800',
          message: 'text-red-700',
          dismiss: 'text-red-400 hover:text-red-600',
        };
      case 'warning':
        return {
          bg: 'bg-yellow-50 border-yellow-200',
          icon: '⚠️',
          iconBg: 'bg-yellow-100',
          iconColor: 'text-yellow-600',
          title: 'text-yellow-800',
          message: 'text-yellow-700',
          dismiss: 'text-yellow-400 hover:text-yellow-600',
        };
      case 'info':
        return {
          bg: 'bg-blue-50 border-blue-200',
          icon: 'ℹ️',
          iconBg: 'bg-blue-100',
          iconColor: 'text-blue-600',
          title: 'text-blue-800',
          message: 'text-blue-700',
          dismiss: 'text-blue-400 hover:text-blue-600',
        };
    }
  };

  const styles = getTypeStyles();

  return (
    <div
      role="alert"
      aria-live="polite"
      aria-labelledby={`notification-title-${id}`}
      aria-describedby={`notification-message-${id}`}
      tabIndex={dismissible ? 0 : -1}
      onKeyDown={handleKeyDown}
      className={`
        ${styles.bg} border rounded-xl p-4 shadow-lg backdrop-blur-sm
        transform transition-all duration-300 ease-out
        ${isVisible && !isLeaving
          ? 'translate-x-0 opacity-100 scale-100'
          : 'translate-x-full opacity-0 scale-95'
        }
        ${dismissible ? 'cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' : ''}
        max-w-sm w-full sm:max-w-md
      `}
      onClick={dismissible ? handleDismiss : undefined}
    >
      <div className="flex items-start space-x-3">
        {/* Icon */}
        <div className={`${styles.iconBg} rounded-full p-2 flex-shrink-0`}>
          <span className="text-xl" role="img" aria-hidden="true">
            {styles.icon}
          </span>
        </div>

        {/* Content */}
        <div className="flex-1 min-w-0">
          <h4
            id={`notification-title-${id}`}
            className={`${styles.title} font-semibold text-sm sm:text-base leading-tight`}
          >
            {title}
          </h4>
          <p
            id={`notification-message-${id}`}
            className={`${styles.message} text-sm mt-1 leading-relaxed break-words`}
          >
            {message}
          </p>
        </div>

        {/* Dismiss Button */}
        {dismissible && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              handleDismiss();
            }}
            className={`
              ${styles.dismiss} transition-colors duration-200 flex-shrink-0
              hover:bg-black hover:bg-opacity-5 rounded-full p-1
              focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
            `}
            aria-label="Dismiss notification"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        )}
      </div>
    </div>
  );
};

export const NotificationContainer: React.FC = () => {
  const { notifications, removeNotification } = useNotifications();

  if (notifications.length === 0) return null;

  return (
    <div
      className="fixed top-4 right-4 z-50 space-y-3 pointer-events-none"
      role="region"
      aria-label="Notifications"
    >
      <div className="pointer-events-auto space-y-3">
        {notifications.map((notification) => (
          <NotificationItem
            key={notification.id}
            {...notification}
            onRemove={removeNotification}
          />
        ))}
      </div>
    </div>
  );
};

// Helper hooks for common notification types
export const useNotificationHelpers = () => {
  const { addNotification } = useNotifications();

  return {
    showSuccess: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'success', title, message, duration }),

    showError: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'error', title, message, duration }),

    showWarning: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'warning', title, message, duration }),

    showInfo: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'info', title, message, duration }),
  };
};
