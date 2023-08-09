import { showErrorMessage, showSuccessMessage } from 'helpers/visual/toasts';
import { CartFragmentApi, useRemovePromoCodeFromCartMutationApi } from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { GtmMessageOriginType } from 'gtm/types/enums';

type RemovePromoCodeHandler = (
    promoCodeToBeRemoved: string,
    messages: { success: string; error: string },
) => Promise<CartFragmentApi | undefined | null>;

export const useRemovePromoCodeFromCart = (): [RemovePromoCodeHandler, boolean] => {
    const [{ fetching }, removePromoCode] = useRemovePromoCodeFromCartMutationApi();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const t = useTypedTranslationFunction();

    const removePromoCodeHandler = useCallback<RemovePromoCodeHandler>(
        async (promoCodeToBeRemoved: string, messages: { success: string; error: string }) => {
            const removePromoCodeResult = await removePromoCode({
                input: { promoCode: promoCodeToBeRemoved, cartUuid },
            });

            // EXTEND PROMO CODE MODIFICATIONS HERE

            if (removePromoCodeResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(removePromoCodeResult.error, t);
                if (userError?.validation?.promoCode !== undefined) {
                    showErrorMessage(userError.validation.promoCode.message, GtmMessageOriginType.cart);
                } else {
                    showErrorMessage(messages.error, GtmMessageOriginType.cart);
                }

                return null;
            }

            showSuccessMessage(messages.success);

            return removePromoCodeResult.data?.RemovePromoCodeFromCart;
        },
        [cartUuid, removePromoCode, t],
    );

    return [removePromoCodeHandler, fetching];
};
