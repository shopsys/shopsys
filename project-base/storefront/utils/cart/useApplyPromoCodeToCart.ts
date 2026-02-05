import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useApplyPromoCodeToCartMutation } from 'graphql/requests/cart/mutations/ApplyPromoCodeToCartMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ApplyPromoCodeToCart = (newPromoCode: string) => Promise<TypeCartFragment | undefined | null>;

export const useApplyPromoCodeToCart = (messages: { success: string }) => {
    const [, applyPromoCodeToCartMutation] = useApplyPromoCodeToCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);

    const applyPromoCodeToCart: ApplyPromoCodeToCart = async (newPromoCode) => {
        const applyPromoCodeResult = await applyPromoCodeToCartMutation({
            input: { promoCode: newPromoCode, cartUuid },
        });

        if (applyPromoCodeResult.error !== undefined) {
            return null;
        }

        showSuccessMessage(messages.success);

        return applyPromoCodeResult.data?.ApplyPromoCodeToCart;
    };

    return { applyPromoCodeToCart };
};
