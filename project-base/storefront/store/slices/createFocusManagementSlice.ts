import { StateCreator } from 'zustand';

export type FocusManagementSlice = {
    storedFocusElement: HTMLElement | null;
    storeCurrentFocus: () => void;
    restoreStoredFocus: () => void;
    clearStoredFocus: () => void;
};

export const createFocusManagementSlice: StateCreator<FocusManagementSlice> = (set, get) => ({
    storedFocusElement: null,

    storeCurrentFocus: () => {
        const currentElement = document.activeElement;

        if (
            currentElement instanceof HTMLElement &&
            currentElement !== document.body &&
            !currentElement.closest('[role="dialog"], [role="alertdialog"]')
        ) {
            set(() => ({
                storedFocusElement: currentElement,
            }));
        }
    },

    restoreStoredFocus: () => {
        const { storedFocusElement } = get();
        if (storedFocusElement?.isConnected) {
            storedFocusElement.focus({ preventScroll: true });
        }

        // Clear the stored focus after restoring
        set(() => ({
            storedFocusElement: null,
        }));
    },

    clearStoredFocus: () => {
        set(() => ({
            storedFocusElement: null,
        }));
    },
});
