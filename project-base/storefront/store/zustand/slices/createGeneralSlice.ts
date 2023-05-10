import { StateCreator } from 'zustand';

type LoginLoadingStatus = 'not-loading' | 'loading' | 'loading-with-cart-modifications';

export type GeneralSlice = {
    loginLoading: LoginLoadingStatus;

    updateGeneralState: (value: Partial<GeneralSlice>) => void;
};

export const createGeneralSlice: StateCreator<GeneralSlice> = (set) => ({
    loginLoading: 'not-loading',

    updateGeneralState: (value) => {
        set(value);
    },
});
