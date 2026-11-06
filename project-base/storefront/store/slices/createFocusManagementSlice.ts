import { StateCreator } from 'zustand';

export type FocusManagementSlice = {
    storedFocusElement: HTMLElement | null;
    storedFocusFallbackSelector: string | null;
    storeCurrentFocus: (fallbackSelector?: string) => void;
    restoreStoredFocus: () => void;
    clearStoredFocus: () => void;
};

export const createFocusManagementSlice: StateCreator<FocusManagementSlice> = (set, get) => ({
    storedFocusElement: null,
    storedFocusFallbackSelector: null,

    storeCurrentFocus: (fallbackSelector) => {
        const currentElement = document.activeElement;

        if (
            currentElement instanceof HTMLElement &&
            currentElement !== document.body &&
            !currentElement.closest('[role="dialog"], [role="alertdialog"]')
        ) {
            set(() => ({
                storedFocusElement: currentElement,
                storedFocusFallbackSelector: fallbackSelector ?? null,
            }));
        }
    },

    restoreStoredFocus: () => {
        const { storedFocusElement, storedFocusFallbackSelector } = get();
        if (storedFocusElement?.isConnected) {
            storedFocusElement.focus({ preventScroll: true });
        }

        // A responsive switch can hide or unmount the original popup trigger.
        if (storedFocusFallbackSelector && document.activeElement !== storedFocusElement) {
            for (const element of Array.from(document.querySelectorAll<HTMLElement>(storedFocusFallbackSelector))) {
                element.focus({ preventScroll: true });
                if (document.activeElement === element) {
                    break;
                }
            }
        }

        // Clear the stored focus after restoring
        set(() => ({
            storedFocusElement: null,
            storedFocusFallbackSelector: null,
        }));
    },

    clearStoredFocus: () => {
        set(() => ({
            storedFocusElement: null,
            storedFocusFallbackSelector: null,
        }));
    },
});
