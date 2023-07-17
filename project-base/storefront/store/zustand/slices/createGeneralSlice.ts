import { StateCreator } from 'zustand';

type LoginLoadingStatus = 'loading' | 'loading-with-cart-modifications';

export type LoginLoadingSlice = {
    loginLoading: LoginLoadingStatus | null;
    lastVisitedSlug: string | null;

    updateLoginLoadingState: (value: LoginLoadingStatus | null) => void;
    updateLastVisitedSlug: (value: string) => void;
};

export const createLoginLoadingSlice: StateCreator<LoginLoadingSlice> = (set) => ({
    loginLoading: null,
    lastVisitedSlug: null,

    updateLoginLoadingState: (value) => {
        set({ loginLoading: value });
    },
    updateLastVisitedSlug: (value) => {
        set({ lastVisitedSlug: value });
    },
});
