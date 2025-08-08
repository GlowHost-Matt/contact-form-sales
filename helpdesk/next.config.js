/** @type {import('next').NextConfig} */
const nextConfig = {
  // Enable static export for deployment as static files
  output: 'export',

  // Disable image optimization for static export
  images: {
    unoptimized: true,
  },

  // Configure for subdirectory deployment (/helpdesk/)
  basePath: '/helpdesk',
  assetPrefix: '/helpdesk/',

  // Ensure trailing slashes for proper routing
  trailingSlash: true,

  // Optimize for production
  experimental: {
    optimizeCss: true,
  },

  // Configure build output directory
  distDir: 'out',

  // Environment variables for API integration
  env: {
    NEXT_PUBLIC_API_BASE_URL: '../api',
    NEXT_PUBLIC_CONTACT_FORM_VERSION: '2.0.0',
    NEXT_PUBLIC_USE_PHP_BACKEND: 'true',
  },

  // Redirects for better UX
  async redirects() {
    return [
      {
        source: '/helpdesk',
        destination: '/helpdesk/',
        permanent: true,
      },
    ];
  },

  // Headers for security and API integration
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          {
            key: 'X-Frame-Options',
            value: 'SAMEORIGIN',
          },
          {
            key: 'X-Content-Type-Options',
            value: 'nosniff',
          },
          {
            key: 'Referrer-Policy',
            value: 'strict-origin-when-cross-origin',
          },
        ],
      },
    ];
  },

  // Webpack configuration for optimal builds
  webpack: (config, { isServer }) => {
    // Optimize bundle size
    config.optimization.splitChunks = {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
        },
      },
    };

    return config;
  },
};

module.exports = nextConfig;
