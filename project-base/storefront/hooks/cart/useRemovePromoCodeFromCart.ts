import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemovePromoCodeFromCartMutation } from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'helpers/toasts/showErrorMessage';
import { showSuccessMessage } from 'helpers/toasts/showSuccessMessage';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';

type RemovePromoCodeHandler = (
    promoCodeToBeRemoved: string,
    messages: { success: string; error: string },
) => Promise<CartFragment | undefined | null>;

export const useRemovePromoCodeFromCart = (): [RemovePromoCodeHandler, boolean] => {
    const [{ fetching }, removePromoCode] = useRemovePromoCodeFromCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();

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
