import { useEffect } from 'react';

const focusableElementsSelector = [
    'button:not([disabled]):not([tabindex="-1"])',
    'a[href]:not([tabindex="-1"])',
    'input:not([disabled]):not([tabindex="-1"])',
    'select:not([disabled]):not([tabindex="-1"])',
    'textarea:not([disabled]):not([tabindex="-1"])',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

const getFocusableElements = (container: HTMLElement): HTMLElement[] =>
    Array.from(container.querySelectorAll<HTMLElement>(focusableElementsSelector)).filter(
        (element) => !element.closest('[aria-hidden="true"]'),
    );

// prevents Tab/Shift+Tab from escaping modal, keeps focus within modal, essential for accessibility
export const useFocusTrap = (containerRef: React.RefObject<HTMLElement | null> | undefined) => {
    useEffect(() => {
        const container = containerRef?.current;

        if (!container) {
            return undefined;
        }

        const trapFocus = (e: KeyboardEvent) => {
            if (e.key !== 'Tab') {
                return;
            }

            if (!container.contains(document.activeElement)) {
                e.preventDefault();
                (getFocusableElements(container)[0] ?? container).focus();
                return;
            }

            const focusableElements = getFocusableElements(container);
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (!firstElement || !lastElement) {
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

        const keepFocusInside = (e: FocusEvent) => {
            if (e.target instanceof Node && container.contains(e.target)) {
                return;
            }

            if (
                // Password-manager and browser autofill popups render outside the trapped container.
                e.relatedTarget instanceof HTMLInputElement &&
                container.contains(e.relatedTarget) &&
                e.relatedTarget.autocomplete !== '' &&
                e.relatedTarget.autocomplete !== 'off'
            ) {
                return;
            }

            (getFocusableElements(container)[0] ?? container).focus();
        };

        document.addEventListener('keydown', trapFocus);
        document.addEventListener('focusin', keepFocusInside);

        return () => {
            document.removeEventListener('keydown', trapFocus);
            document.removeEventListener('focusin', keepFocusInside);
        };
    }, [containerRef]);
};
