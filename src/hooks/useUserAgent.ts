import { useState, useEffect } from 'react';
import type { UserAgentData } from '@/types';
import { API_CONFIG } from '../../config/app.config';

export function useUserAgent() {
  const [userAgentData, setUserAgentData] = useState<UserAgentData>({
    userAgent: '',
    ipv4Address: '',
    browserName: '',
    operatingSystem: '',
    timestamp: ''
  });

  useEffect(() => {
    // Parse browser and OS from user agent string
    function parseUserAgent(ua: string) {
      let browserName = "Unknown";
      let operatingSystem = "Unknown";

      // Browser detection (very basic)
      if (/chrome|crios/i.test(ua) && !/edge|edg|opr|opera/i.test(ua)) {
        browserName = "Chrome";
      } else if (/firefox|fxios/i.test(ua)) {
        browserName = "Firefox";
      } else if (/safari/i.test(ua) && !/chrome|crios|android/i.test(ua)) {
        browserName = "Safari";
      } else if (/edg/i.test(ua)) {
        browserName = "Edge";
      } else if (/opr|opera/i.test(ua)) {
        browserName = "Opera";
      } else if (/msie|trident/i.test(ua)) {
        browserName = "Internet Explorer";
      }

      // OS detection (very basic)
      if (/windows nt/i.test(ua)) {
        operatingSystem = "Windows";
      } else if (/macintosh|mac os x/i.test(ua)) {
        operatingSystem = "macOS";
      } else if (/android/i.test(ua)) {
        operatingSystem = "Android";
      } else if (/iphone|ipad|ipod/i.test(ua)) {
        operatingSystem = "iOS";
      } else if (/linux/i.test(ua)) {
        operatingSystem = "Linux";
      }

      return { browserName, operatingSystem };
    }

    // Get IPv4 address using a public API
    async function fetchIPv4() {
      try {
        const res = await fetch(API_CONFIG.endpoints.USER_AGENT);
        if (!res.ok) throw new Error("Failed to fetch IP");
        const data = await res.json();
        return data.ip || "";
      } catch {
        return "";
      }
    }

    const ua = typeof window !== "undefined" ? window.navigator.userAgent : "";
    const { browserName, operatingSystem } = parseUserAgent(ua);

    fetchIPv4().then(ipv4 => {
      setUserAgentData({
        userAgent: ua,
        ipv4Address: ipv4,
        browserName,
        operatingSystem,
        timestamp: new Date().toISOString()
      });
    });
  }, []);

  return userAgentData;
}
