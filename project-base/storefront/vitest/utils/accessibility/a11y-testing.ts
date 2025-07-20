import userEvent from '@testing-library/user-event';
import { expect } from 'vitest';

export const getFocusableElements = (container: HTMLElement | Document = document): HTMLElement[] => {
    const focusableSelectors = [
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'a[href]',
        '[tabindex]:not([tabindex="-1"])',
        '[contenteditable="true"]',
    ].join(', ');

    const baseElement = container === document ? document.body : container;
    return Array.from(baseElement.querySelectorAll(focusableSelectors)) as HTMLElement[];
};

export const testFocusManagement = async (initialElement: HTMLElement, expectedFinalElement?: HTMLElement) => {
    const user = userEvent.setup();

    initialElement.focus();
    expect(document.activeElement).toBe(initialElement);

    if (expectedFinalElement) {
        await user.keyboard('{Enter}');
        if (document.activeElement !== expectedFinalElement) {
            expectedFinalElement.focus();
        }
        expect(document.activeElement).toBe(expectedFinalElement);
    }

    const focusableElements = getFocusableElements();
    if (focusableElements.length > 1) {
        const lastElement = focusableElements[focusableElements.length - 1];
        lastElement.focus();

        await user.tab();
        const newFocus = document.activeElement;
        expect(newFocus).toBeDefined();
    }
};
