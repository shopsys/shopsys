import { StateCreator } from 'zustand';

type AuthLoadingStatus =
    | 'login-loading'
    | 'login-loading-with-cart-modifications'
    | 'logout-loading'
    | 'registration-loading'
    | 'registration-loading-with-cart-modifications';

type AuthLoadingState = {
    authLoading: AuthLoadingStatus | null;
};

export type AuthLoadingSlice = AuthLoadingState & {
    updateAuthLoadingState: (value: AuthLoadingStatus | null) => void;
};

export const defaultAuthLoadingState: AuthLoadingState = {
    authLoading: null,
};

export const createAuthLoadingSlice: StateCreator<AuthLoadingSlice> = (set) => ({
    ...defaultAuthLoadingState,

    updateAuthLoadingState: (value) => {
        set({ authLoading: value });
    },
});
