import { useEffect, useRef } from 'react';

/**
 * A hook to programmatically move focus to a page's main heading upon render.
 * This improves accessibility and user experience, especially for single-page applications
 * where a "page" change doesn't trigger a full browser navigation.
 *
 * @returns A React ref object to be attached to the main heading element (e.g., an <h1>).
 */
export function usePageFocus<T extends HTMLElement>() {
  const headingRef = useRef<T>(null);

  useEffect(() => {
    // Ensure the ref is attached to an element
    if (headingRef.current) {
      // Make the element programmatically focusable if it isn't already
      if (headingRef.current.tabIndex === -1) {
        headingRef.current.setAttribute('tabindex', '-1');
      }
      // Move focus to the heading
      headingRef.current.focus();
    }
  }, []); // Runs only once when the component mounts

  return headingRef;
}
