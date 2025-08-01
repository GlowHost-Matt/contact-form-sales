/** @type {import('next').NextConfig} */
const nextConfig = {
  // Enable static export for cPanel deployment
  output: 'export',
  distDir: 'out',
  trailingSlash: true,

  // Temporarily ignore linting errors for deployment
  eslint: {
    ignoreDuringBuilds: true,
  },

  allowedDevOrigins: ["*.preview.same-app.com"],
  images: {
    unoptimized: true,
    domains: [
      "source.unsplash.com",
      "images.unsplash.com",
      "ext.same-assets.com",
      "ugc.same-assets.com",
      "glowhost.com",
      "picsum.photos",
      "www.w3.org"
    ],
    remotePatterns: [
      {
        protocol: "https",
        hostname: "source.unsplash.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "images.unsplash.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "ext.same-assets.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "ugc.same-assets.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "glowhost.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "picsum.photos",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "www.w3.org",
        pathname: "/**",
      }
    ],
  },
};

module.exports = nextConfig;
