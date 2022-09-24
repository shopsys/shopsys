import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { CartFragmentApi, useApplyPromoCodeToCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';

type ApplyPromoCodeHandler = (
    newPromoCode: string,
    messages: { success: string; error: string },
) => Promise<CartFragmentApi | undefined | null>;

export const useApplyPromoCodeToCart = (): [ApplyPromoCodeHandler, boolean] => {
    const [{ fetching }, applyPromoCodeToCart] = useApplyPromoCodeToCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();

    const applyPromoCodeHandler = useCallback<ApplyPromoCodeHandler>(
        async (newPromoCode, messages) => {
            const applyPromoCodeResult = await applyPromoCodeToCart({ input: { promoCode: newPromoCode, cartUuid } });

            // EXTEND PROMO CODE MODIFICATIONS HERE

            if (applyPromoCodeResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(applyPromoCodeResult.error, t);
                if (userError?.validation?.promoCode !== undefined) {
                    showErrorMessage(userError.validation.promoCode.message, 'cart');
                } else {
                    showErrorMessage(messages.error, 'cart');
                }

                return null;
            }

            showSuccessMessage(messages.success);

            return applyPromoCodeResult.data?.ApplyPromoCodeToCart;
        },
        [applyPromoCodeToCart, cartUuid, t],
    );

    return [applyPromoCodeHandler, fetching];
};
