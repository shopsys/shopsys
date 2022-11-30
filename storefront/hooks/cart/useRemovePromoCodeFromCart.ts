import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { CartFragmentApi, useRemovePromoCodeFromCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';

type RemovePromoCodeHandler = (
    promoCodeToBeRemoved: string,
    messages: { success: string; error: string },
) => Promise<CartFragmentApi | undefined | null>;

export const useRemovePromoCodeFromCart = (): [RemovePromoCodeHandler, boolean] => {
    const [{ fetching }, removePromoCode] = useRemovePromoCodeFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
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
                    showErrorMessage(userError.validation.promoCode.message, 'cart');
                } else {
                    showErrorMessage(messages.error, 'cart');
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
