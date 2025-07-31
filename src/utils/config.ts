export const USE_PHP_BACKEND = (() => {
  // Check URL parameter first: ?php=true or ?php=false
  if (typeof window !== 'undefined') {
    const urlParams = new URLSearchParams(window.location.search);
    const phpParam = urlParams.get('php');
    if (phpParam === 'true') return true;
    if (phpParam === 'false') return false;
  }

  // Check environment variable
  const envMode = process.env.NEXT_PUBLIC_USE_PHP_BACKEND;
  if (envMode === 'true') return true;
  if (envMode === 'false') return false;

  // Default: false for local testing, change to true for production
  return false;
})();
