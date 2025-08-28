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
        const currentElement = document.activeElement as HTMLElement;

        // Only store focus if the current element is a button
        if (currentElement.tagName === 'BUTTON') {
            set(() => ({
                storedFocusElement: currentElement,
            }));
        }
    },

    restoreStoredFocus: () => {
        const { storedFocusElement } = get();
        if (storedFocusElement) {
            storedFocusElement.focus();

            // Clear the stored focus after restoring
            set(() => ({
                storedFocusElement: null,
            }));
        }
    },

    clearStoredFocus: () => {
        set(() => ({
            storedFocusElement: null,
        }));
    },
});
