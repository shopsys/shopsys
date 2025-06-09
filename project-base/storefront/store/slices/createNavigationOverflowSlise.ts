import { StateCreator } from 'zustand';

export type NavigationOverflowSlice = {
    showNavigationShadow: boolean;
    setShowNavigationShadow: (value: boolean) => void;
};

export const createNavigationOverflowSlise: StateCreator<NavigationOverflowSlice> = (set) => ({
    showNavigationShadow: false,

    setShowNavigationShadow: (value) => {
        set(() => ({
            showNavigationShadow: value,
        }));
    },
});
