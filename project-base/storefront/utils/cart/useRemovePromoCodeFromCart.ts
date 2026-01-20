import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemovePromoCodeFromCartMutation } from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type RemovePromoCodeFromCart = (promoCodeToBeRemoved: string) => Promise<TypeCartFragment | undefined | null>;

export const useRemovePromoCodeFromCart = (messages: { success: string }) => {
    const [{ fetching: isRemovingPromoCodeFromCart }, removePromoCodeFromCartMutation] =
        useRemovePromoCodeFromCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);

    const removePromoCodeFromCart = useCallback<RemovePromoCodeFromCart>(
        async (promoCodeToBeRemoved: string) => {
            const removePromoCodeResult = await removePromoCodeFromCartMutation({
                input: { promoCode: promoCodeToBeRemoved, cartUuid },
            });

            // EXTEND PROMO CODE MODIFICATIONS HERE

            if (removePromoCodeResult.error !== undefined) {
                return null;
            }

            showSuccessMessage(messages.success);

            return removePromoCodeResult.data?.RemovePromoCodeFromCart;
        },
        [cartUuid, removePromoCodeFromCartMutation, messages.success],
    );

    return { removePromoCodeFromCart, isRemovingPromoCodeFromCart };
};
