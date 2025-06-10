import { useEffect } from 'react';

// prevents Tab/Shift+Tab from escaping modal, keeps focus within modal, essential for accessibility
export const useFocusTrap = (containerRef: React.RefObject<HTMLElement>) => {
    useEffect(() => {
        const container = containerRef.current;

        const focusableElements = container?.querySelectorAll(
            'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
        );

        const firstElement = focusableElements?.[0] as HTMLElement;
        const lastElement = focusableElements?.[focusableElements.length - 1] as HTMLElement;

        const trapFocus = (e: KeyboardEvent) => {
            if (e.key !== 'Tab') {
                return;
            }

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        document.addEventListener('keydown', trapFocus);

        return () => {
            document.removeEventListener('keydown', trapFocus);
        };
    }, [containerRef]);
};
