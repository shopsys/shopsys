import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useApplyPromoCodeToCartMutation } from 'graphql/requests/cart/mutations/ApplyPromoCodeToCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export type ApplyPromoCodeToCart = (newPromoCode: string) => Promise<TypeCartFragment | undefined | null>;

export const useApplyPromoCodeToCart = (messages: { success: string; error: string }) => {
    const [{ fetching: isApplyingPromoCodeToCart }, applyPromoCodeToCartMutation] = useApplyPromoCodeToCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();

    const applyPromoCodeToCart = useCallback<ApplyPromoCodeToCart>(
        async (newPromoCode) => {
            const applyPromoCodeResult = await applyPromoCodeToCartMutation({
                input: { promoCode: newPromoCode, cartUuid },
            });

            // EXTEND PROMO CODE MODIFICATIONS HERE

            if (applyPromoCodeResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(applyPromoCodeResult.error, t);
                if (userError?.validation?.promoCode !== undefined) {
                    showErrorMessage(userError.validation.promoCode.message, GtmMessageOriginType.cart);
                } else {
                    showErrorMessage(messages.error, GtmMessageOriginType.cart);
                }

                return null;
            }

            showSuccessMessage(messages.success);

            return applyPromoCodeResult.data?.ApplyPromoCodeToCart;
        },
        [applyPromoCodeToCartMutation, cartUuid, t],
    );

    return { applyPromoCodeToCart, isApplyingPromoCodeToCart };
};
