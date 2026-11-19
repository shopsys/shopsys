import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { StateCreator } from 'zustand';

export type CartModificationsBatch = {
    // The API returns null for the cart of a logged-in customer
    cartUuid: string | null;
    modifications: TypeCartModificationsFragment;
    hasPayment: boolean;
};

/**
 * The API reports a cart modification only in the one response that detected it, so the storefront keeps it
 * for the current document until the customer dismisses it. Modifications of a different cart replace the kept
 * ones and a created order clears them (see cartModificationsExchange).
 */
export type CartModificationsSlice = {
    cartModificationsBatches: CartModificationsBatch[];
    unhandledCartModificationsBatches: CartModificationsBatch[];
    // The contact information page unmounts while the order is being created, so the refusal is kept here
    isOrderRejectedByCartModifications: boolean;
    receiveCartModifications: (batch: CartModificationsBatch) => void;
    takeUnhandledCartModificationsBatches: () => CartModificationsBatch[];
    dismissCartModifications: () => void;
    clearCartModifications: () => void;
    setIsOrderRejectedByCartModifications: (isOrderRejectedByCartModifications: boolean) => void;
};

export const createCartModificationsSlice: StateCreator<CartModificationsSlice> = (set, get) => ({
    cartModificationsBatches: [],
    unhandledCartModificationsBatches: [],
    isOrderRejectedByCartModifications: false,

    receiveCartModifications: (batch) => {
        const { cartModificationsBatches, unhandledCartModificationsBatches } = get();
        const isSameCart = cartModificationsBatches.every((keptBatch) => keptBatch.cartUuid === batch.cartUuid);

        set({
            cartModificationsBatches: isSameCart ? [...cartModificationsBatches, batch] : [batch],
            unhandledCartModificationsBatches: [...unhandledCartModificationsBatches, batch],
        });
    },

    // Takes the batches out of the queue, so each of them is handled only once
    takeUnhandledCartModificationsBatches: () => {
        const { unhandledCartModificationsBatches } = get();

        if (unhandledCartModificationsBatches.length > 0) {
            set({ unhandledCartModificationsBatches: [] });
        }

        return unhandledCartModificationsBatches;
    },

    dismissCartModifications: () => set({ cartModificationsBatches: [], isOrderRejectedByCartModifications: false }),

    clearCartModifications: () =>
        set({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        }),

    setIsOrderRejectedByCartModifications: (isOrderRejectedByCartModifications) =>
        set({ isOrderRejectedByCartModifications }),
});
