import { StateCreator } from 'zustand';

export type UserMenuSlice = {
    isUserMenuOpen: boolean;
    loginFormEmail: string;
    setIsUserMenuOpen: (value: boolean) => void;
    setLoginFormEmail: (value: string) => void;
    resetLoginFormEmail: () => void;
};

export const createUserMenuSlice: StateCreator<UserMenuSlice> = (set) => ({
    isUserMenuOpen: false,
    loginFormEmail: '',

    setIsUserMenuOpen: (value) => {
        set(() => ({
            isUserMenuOpen: value,
        }));
    },
    setLoginFormEmail: (value) => {
        set(() => ({
            loginFormEmail: value,
        }));
    },
    resetLoginFormEmail: () => {
        set(() => ({
            loginFormEmail: '',
        }));
    },
});
