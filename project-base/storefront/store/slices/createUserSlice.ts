import { StateCreator } from 'zustand';

export type UserSlice = {
    cartUuid: string | null;
    wishlistUuid: string | null;
    comparisonUuid: string | null;

    updateCartUuid: (value: string | null) => void;
    updateWishlistUuid: (value: string | null) => void;
    updateComparisonUuid: (value: string | null) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    wishlistUuid: null,
    comparisonUuid: null,

    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateWishlistUuid: (wishlistUuid) => {
        set({ wishlistUuid });
    },
    updateComparisonUuid: (comparisonUuid) => {
        set({ comparisonUuid });
    },
});
