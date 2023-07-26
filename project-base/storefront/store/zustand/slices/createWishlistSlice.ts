import { StateCreator } from 'zustand';

export type WishlistSlice = {
    wishlistUuid: string | null;

    updateWishlistUuid: (value: string) => void;
    clearWishlistUuid: () => void;
};

export const createWishlistSlice: StateCreator<WishlistSlice> = (set) => ({
    wishlistUuid: null,

    updateWishlistUuid: (wishlistUuid) => {
        set({ wishlistUuid });
    },
    clearWishlistUuid: () => {
        set({ wishlistUuid: null });
    },
});
