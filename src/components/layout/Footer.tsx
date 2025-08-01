import { APP_CONFIG, getBackendMode } from '../../../config/app.config';
interface FooterProps {
  pageType?: 'form' | 'thread' | 'confirmation';
}

export function Footer({ pageType = 'form' }: FooterProps) {
  const usePhpBackend = getBackendMode();

  return (
    <footer className="bg-gray-100 border-t">
      <div className="container mx-auto px-4 py-6">
        <div className="text-center space-y-2">
          <p className="text-sm text-gray-600">
            {pageType === 'form' && 'Sales Contact Form'}
            {pageType === 'thread' && 'Support Thread View'}
            {pageType === 'confirmation' && 'Confirmation Page'}
            {' '}Version: {APP_CONFIG.identity.VERSION}
          </p>

          {pageType === 'form' && (
            <>
              {/* Testing Mode Indicator */}
              <div className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                usePhpBackend
                  ? 'bg-green-100 text-green-800 border border-green-200'
                  : 'bg-blue-100 text-blue-800 border border-blue-200'
              }`}>
                <div className={`w-2 h-2 rounded-full mr-2 ${usePhpBackend ? 'bg-green-500' : 'bg-blue-500'}`} />
                {usePhpBackend ? '🔧 PHP Backend Mode' : '🧪 Local Testing Mode'}
              </div>

              <div className="text-xs text-gray-500 max-w-md mx-auto">
                {usePhpBackend ? (
                  <p>Form submissions will be sent to <code className="bg-gray-200 px-1 rounded">/api/submit-form.php</code></p>
                ) : (
                  <div className="space-y-1">
                    <p>Form submissions are simulated (not sent to PHP backend)</p>
                    <p className="text-purple-600 font-medium">⚡ Dev Mode: Pre-filled form (fully editable for testing)</p>
                    <p>
                      <span className="text-gray-400">Switch modes: </span>
                      <a href="?php=true" className="text-blue-600 hover:underline">Enable PHP</a>
                      <span className="text-gray-400"> | </span>
                      <a href="?php=false" className="text-blue-600 hover:underline">Local Testing</a>
                    </p>
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </div>
    </footer>
  );
}
