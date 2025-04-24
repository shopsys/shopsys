import { StateCreator } from 'zustand';

export type UserMenuSlice = {
    isUserMenuOpen: boolean;
    setIsUserMenuOpen: (value: boolean) => void;
};

export const createUserMenuSlice: StateCreator<UserMenuSlice> = (set) => ({
    isUserMenuOpen: false,

    setIsUserMenuOpen: (value) => {
        set(() => ({
            isUserMenuOpen: value,
        }));
    },
});
