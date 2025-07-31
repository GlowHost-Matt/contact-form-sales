import Link from 'next/link';
import { APP_CONFIG } from '../../../config/app.config';
import { decodeFromUrl } from '@/lib/utils';

interface BreadcrumbItem {
  label: string;
  href?: string;
}

interface BreadcrumbProps {
  items: BreadcrumbItem[];
}

export function Breadcrumb({ items }: BreadcrumbProps) {
  return (
    <div className="bg-white border-b">
      <div className="container mx-auto px-4 py-3">
        <div className="text-sm text-gray-600">
          <Link
            href={APP_CONFIG.links.SUPPORT_HOME}
            className="text-[#1a679f] font-semibold hover:underline"
          >
            Web Hosting Support
          </Link>

          {items.map((item, index) => (
            <span key={index}>
              <span className="mx-2">»</span>
              {item.href ? (
                <Link
                  href={item.href}
                  className="text-[#1a679f] font-semibold hover:underline"
                >
                  {item.label}
                </Link>
              ) : (
                <span>{decodeFromUrl(item.label)}</span>
              )}
            </span>
          ))}
        </div>
      </div>
    </div>
  );
}

// Common breadcrumb configurations
export const breadcrumbConfigs = {
  contactForm: [
    { label: 'Contact GlowHost Sales' }
  ],

  supportThread: (subject: string) => [
    { label: 'Contact Form', href: '/' },
    { label: subject }
  ]
};
