import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { CartFragmentApi, useApplyPromoCodeToCartMutationApi } from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmMessageOriginType } from 'types/gtm/enums';

type ApplyPromoCodeHandler = (
    newPromoCode: string,
    messages: { success: string; error: string },
) => Promise<CartFragmentApi | undefined | null>;

export const useApplyPromoCodeToCart = (): [ApplyPromoCodeHandler, boolean] => {
    const [{ fetching }, applyPromoCodeToCart] = useApplyPromoCodeToCartMutationApi();
    const cartUuid = usePersistStore((s) => s.cartUuid);
    const t = useTypedTranslationFunction();

    const applyPromoCodeHandler = useCallback<ApplyPromoCodeHandler>(
        async (newPromoCode, messages) => {
            const applyPromoCodeResult = await applyPromoCodeToCart({ input: { promoCode: newPromoCode, cartUuid } });

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
        [applyPromoCodeToCart, cartUuid, t],
    );

    return [applyPromoCodeHandler, fetching];
};
