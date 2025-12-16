import { StateCreator } from 'zustand';

export type CartStaleSlice = {
    isCartStale: boolean;
    setCartStale: (stale: boolean) => void;
};

export const createCartStaleSlice: StateCreator<CartStaleSlice> = (set) => ({
    isCartStale: false,
    setCartStale: (stale) => set({ isCartStale: stale }),
});
