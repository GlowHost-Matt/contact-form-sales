"use client";

import { useEffect } from "react";
import { NotificationProvider } from "@/components/ui/notification-context";
import { NotificationContainer } from "@/components/ui/notification";
import { AutoSaveProvider } from "@/components/providers/AutoSaveProvider";

export default function ClientBody({
  children,
}: {
  children: React.ReactNode;
}) {
  // Remove any extension-added classes during hydration
  useEffect(() => {
    // This runs only on the client after hydration
    document.body.className = "antialiased";
  }, []);

  return (
    <AutoSaveProvider>
      <NotificationProvider>
        <div className="antialiased">
          {children}
          <NotificationContainer />
        </div>
      </NotificationProvider>
    </AutoSaveProvider>
  );
}
