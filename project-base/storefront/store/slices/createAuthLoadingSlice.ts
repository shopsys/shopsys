import { StateCreator } from 'zustand';

type AuthLoadingStatus =
    | 'login-loading'
    | 'login-loading-with-cart-modifications'
    | 'logout-loading'
    | 'registration-loading'
    | 'registration-loading-with-cart-modifications';

export type AuthLoadingSlice = {
    authLoading: AuthLoadingStatus | null;

    updateAuthLoadingState: (value: AuthLoadingStatus | null) => void;
};

export const createAuthLoadingSlice: StateCreator<AuthLoadingSlice> = (set) => ({
    authLoading: null,

    updateAuthLoadingState: (value) => {
        set({ authLoading: value });
    },
});
