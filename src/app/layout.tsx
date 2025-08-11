import type { Metadata, Viewport } from "next";
import { Toaster } from "@/components/ui/toaster";
import "./globals.css";

export const metadata: Metadata = {
  title: "Contact GlowHost Sales | Professional Web Hosting Solutions",
  description: "Contact GlowHost for professional web hosting solutions. 24/7 support, reliable hosting since 2002. Sales inquiries welcome.",
  keywords: "web hosting, contact sales, GlowHost, hosting support, professional hosting",
  authors: [{ name: "GlowHost" }],
};

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <head>
        <link rel="icon" href="https://glowhost.com/wp-content/uploads/cropped-favicon-32x32.png" />
      </head>
      <body className="antialiased">
        {children}
        <Toaster />
      </body>
    </html>
  );
}
