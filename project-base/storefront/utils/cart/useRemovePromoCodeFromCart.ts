import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemovePromoCodeFromCartMutation } from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type RemovePromoCodeFromCart = (promoCodeToBeRemoved: string) => Promise<TypeCartFragment | undefined | null>;

export const useRemovePromoCodeFromCart = (messages: { success: string; error: string }) => {
    const [{ fetching: isRemovingPromoCodeFromCart }, removePromoCodeFromCartMutation] =
        useRemovePromoCodeFromCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();

    const removePromoCodeFromCart = useCallback<RemovePromoCodeFromCart>(
        async (promoCodeToBeRemoved: string) => {
            const removePromoCodeResult = await removePromoCodeFromCartMutation({
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
        [cartUuid, removePromoCodeFromCartMutation, t],
    );

    return { removePromoCodeFromCart, isRemovingPromoCodeFromCart };
};
