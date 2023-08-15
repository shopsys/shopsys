import { StateCreator } from 'zustand';

type LoginLoadingStatus = 'loading' | 'loading-with-cart-modifications';

export type LoginLoadingSlice = {
    loginLoading: LoginLoadingStatus | null;

    updateLoginLoadingState: (value: LoginLoadingStatus | null) => void;
};

export const createLoginLoadingSlice: StateCreator<LoginLoadingSlice> = (set) => ({
    loginLoading: null,

    updateLoginLoadingState: (value) => {
        set({ loginLoading: value });
    },
});
