import React from 'react';

interface BreadcrumbItem {
  label: string;
  href?: string;
  isActive?: boolean;
}

interface BreadcrumbProps {
  items: readonly BreadcrumbItem[];
  className?: string;
}

export function Breadcrumb({ items, className = "" }: BreadcrumbProps) {
  return (
    <div className={`bg-white border-b ${className}`}>
      <div className="container mx-auto px-4 py-3">
        <div className="text-sm text-gray-600">
          {items.map((item, index) => (
            <React.Fragment key={index}>
              {item.href && !item.isActive ? (
                <a
                  href={item.href}
                  className="text-[#1a679f] font-semibold hover:underline"
                >
                  {item.label}
                </a>
              ) : (
                <span className={item.isActive ? "text-gray-900" : "text-gray-600"}>
                  {item.label}
                </span>
              )}
              {index < items.length - 1 && (
                <span className="mx-2">»</span>
              )}
            </React.Fragment>
          ))}
        </div>
      </div>
    </div>
  );
}

// Admin settings integration for breadcrumb configurations
interface DepartmentBreadcrumbConfig {
  items: readonly BreadcrumbItem[];
  pageTitle: string;
}

// Default breadcrumb configurations (fallback when admin settings not available)
const DEFAULT_BREADCRUMB_CONFIGS = {
  'Sales Questions': {
    items: [
      { label: 'Web Hosting Support', href: '/support/' },
      { label: 'Contact GlowHost Sales', isActive: true }
    ],
    pageTitle: 'Contact GlowHost Sales: New Inquiry'
  },
  'Technical Support': {
    items: [
      { label: 'Web Hosting Support', href: '/support/' },
      { label: 'Contact GlowHost Technical Support', isActive: true }
    ],
    pageTitle: 'Contact GlowHost Technical Support: New Inquiry'
  },
  'Billing Support': {
    items: [
      { label: 'Web Hosting Support', href: '/support/' },
      { label: 'Contact GlowHost Billing', isActive: true }
    ],
    pageTitle: 'Contact GlowHost Billing: New Inquiry'
  },
  'General Inquiry': {
    items: [
      { label: 'Web Hosting Support', href: '/support/' },
      { label: 'Contact GlowHost Support', isActive: true }
    ],
    pageTitle: 'Contact GlowHost Support: New Inquiry'
  }
} as const;

// Get admin settings for advanced department configuration
function getAdminDepartmentSettings(): Record<string, {
  name: string;
  enabled?: boolean;
  order?: number;
  breadcrumbs?: readonly BreadcrumbItem[];
  pageTitle?: string;
}> | null {
  if (typeof window !== 'undefined') {
    try {
      const savedSettings = localStorage.getItem('mockAdminSettings');
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        return settings.advancedDepartments || null;
      }
    } catch (error) {
      console.warn('Failed to load admin department settings:', error);
    }
  }
  return null;
}

// Helper to get breadcrumb config for a department (reads from advanced admin settings)
export function getBreadcrumbConfig(department: string): DepartmentBreadcrumbConfig {
  const advancedDepartments = getAdminDepartmentSettings();

  // If admin has advanced department configurations, use those
  if (advancedDepartments && advancedDepartments[department]) {
    const adminDept = advancedDepartments[department];
    return {
      items: adminDept.breadcrumbs || DEFAULT_BREADCRUMB_CONFIGS[department as keyof typeof DEFAULT_BREADCRUMB_CONFIGS]?.items || DEFAULT_BREADCRUMB_CONFIGS['Sales Questions'].items,
      pageTitle: adminDept.pageTitle || DEFAULT_BREADCRUMB_CONFIGS[department as keyof typeof DEFAULT_BREADCRUMB_CONFIGS]?.pageTitle || DEFAULT_BREADCRUMB_CONFIGS['Sales Questions'].pageTitle
    };
  }

  // Fallback to default configurations
  return DEFAULT_BREADCRUMB_CONFIGS[department as keyof typeof DEFAULT_BREADCRUMB_CONFIGS] || DEFAULT_BREADCRUMB_CONFIGS['Sales Questions'];
}

// Get available departments from advanced admin settings (unlimited departments)
export function getAvailableDepartments(): string[] {
  const advancedDepartments = getAdminDepartmentSettings();

  // If admin has configured unlimited departments, use those (only enabled ones)
  if (advancedDepartments && typeof advancedDepartments === 'object') {
    const enabledDepartments = Object.values(advancedDepartments)
      .filter((dept: { enabled?: boolean }) => dept.enabled !== false)
      .sort((a: { order?: number }, b: { order?: number }) => (a.order || 100) - (b.order || 100))
      .map((dept: { name: string }) => dept.name);

    if (enabledDepartments.length > 0) {
      return enabledDepartments;
    }
  }

  // Fallback to default departments
  return Object.keys(DEFAULT_BREADCRUMB_CONFIGS);
}
