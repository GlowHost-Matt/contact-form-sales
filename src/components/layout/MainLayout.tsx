import React from 'react';
import Link from 'next/link';

interface MainLayoutProps {
  children: React.ReactNode;
  breadcrumbs?: React.ReactNode;
}

export const MainLayout: React.FC<MainLayoutProps> = ({ children, breadcrumbs }) => {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      {/* Header */}
      <header className="bg-gradient-to-r from-blue-700 to-blue-800 text-white shadow-md">
        <div className="container mx-auto px-4 py-5">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <img
                src="https://glowhost.com/wp-content/uploads/page_notag.png"
                alt="GlowHost Logo"
                className="h-9 w-auto object-contain sm:h-11 md:h-13"
              />
            </div>
            <div className="text-right text-sm sm:text-base">
              <div className="text-blue-200">24 / 7 / 365 Support</div>
              <div className="font-semibold">
                Toll Free Sales <a href="tel:+18882934678" className="hover:text-blue-200 transition-colors">1 (888) 293-HOST</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Breadcrumb */}
      <div className="bg-white border-b border-gray-200">
        <div className="container mx-auto px-4 py-3">
          <div className="text-sm text-gray-600">
            {breadcrumbs ? (
              breadcrumbs
            ) : (
              <>
                <Link href="/support/" className="text-[#1a679f] font-semibold hover:underline">Web Hosting Support</Link>
                <span className="mx-2">»</span>
                <span>Contact GlowHost Sales</span>
              </>
            )}
          </div>
        </div>
      </div>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8 flex-grow">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-gray-800 text-white border-t border-gray-700">
        <div className="container mx-auto px-4 py-8">
          <div className="text-center space-y-4">
            <p className="text-sm text-gray-400">Sales Contact Form Version: 273</p>

            <div className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-blue-900 text-blue-300 border-blue-700`}>
              <div className={`w-2 h-2 rounded-full mr-2 bg-blue-500`} />
              Local Test Mode
            </div>

            <div className="text-xs text-gray-500 max-w-lg mx-auto">
                <div className="space-y-1">
                  <p>Submissions are simulated and not sent to a backend.</p>
                  <p className="text-blue-400 font-medium">⚡ Dev Mode: Pre-filled form + Skip to confirmation button</p>
                  <p>
                    <span className="text-gray-400">Switch modes: </span>
                    <Link href="?php=true" className="text-blue-400 hover:underline">Enable PHP</Link>
                    <span className="text-gray-500"> | </span>
                    <Link href="?php=false" className="text-blue-400 hover:underline">Local Testing</Link>
                  </p>
                </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
};
