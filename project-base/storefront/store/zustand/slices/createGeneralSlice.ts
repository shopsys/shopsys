import { StateCreator } from 'zustand';

type LoginLoadingStatus = 'not-loading' | 'loading' | 'loading-with-cart-modifications';

export type LoginLoadingSlice = {
    loginLoading: LoginLoadingStatus;

    updateLoginLoadingState: (value: Partial<LoginLoadingSlice>) => void;
};

export const createLoginLoadingSlice: StateCreator<LoginLoadingSlice> = (set) => ({
    loginLoading: 'not-loading',

    updateLoginLoadingState: (value) => {
        set(value);
    },
});
