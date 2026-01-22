import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemovePromoCodeFromCartMutation } from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type RemovePromoCodeFromCart = (promoCodeToBeRemoved: string) => Promise<TypeCartFragment | undefined | null>;

export const useRemovePromoCodeFromCart = (messages: { success: string }) => {
    const [{ fetching: isRemovingPromoCodeFromCart }, removePromoCodeFromCartMutation] =
        useRemovePromoCodeFromCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);

    const removePromoCodeFromCart: RemovePromoCodeFromCart = async (promoCodeToBeRemoved) => {
        const removePromoCodeResult = await removePromoCodeFromCartMutation({
            input: { promoCode: promoCodeToBeRemoved, cartUuid },
        });

        // EXTEND PROMO CODE MODIFICATIONS HERE

        if (removePromoCodeResult.error !== undefined) {
            return null;
        }

        showSuccessMessage(messages.success);

        return removePromoCodeResult.data?.RemovePromoCodeFromCart;
    };

    return { removePromoCodeFromCart, isRemovingPromoCodeFromCart };
};
