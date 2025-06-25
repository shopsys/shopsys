import { useEffect } from 'react';

// prevents Tab/Shift+Tab from escaping modal, keeps focus within modal, essential for accessibility
export const useFocusTrap = (containerRef: React.RefObject<HTMLElement> | undefined) => {
    useEffect(() => {
        const container = containerRef?.current;

        const focusableElements = container?.querySelectorAll(
            'button:not([tabindex="-1"]), a[href]:not([tabindex="-1"]), input:not([tabindex="-1"]), select:not([tabindex="-1"]), textarea:not([tabindex="-1"])',
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
