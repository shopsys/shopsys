import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { cartActions } from 'redux/slices/cart';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useRemovePromoCodeFromCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useRemovePromoCodeFromCart = (): typeof removePromoCodeHandler => {
    const [, removePromoCode] = useRemovePromoCodeFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.cart.cartInput);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const removePromoCodeHandler = async (
        promoCodeToBeRemoved: string,
        messages: { success: string; error: string },
    ) => {
        const removePromoCodeResult = await removePromoCode({
            input: { promoCode: promoCodeToBeRemoved, cartUuid },
        });

        // EXTEND PROMO CODE MODIFICATIONS HERE

        if (removePromoCodeResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(removePromoCodeResult.error, t);
            if (userError?.validation?.promoCode !== undefined) {
                showErrorMessage(userError.validation.promoCode.message);

                return;
            }
            showErrorMessage(messages.error);
        } else if (removePromoCodeResult.data !== undefined) {
            showSuccessMessage(messages.success);
            dispatch(cartActions.setPromoCode(removePromoCodeResult.data.RemovePromoCodeFromCart.promoCode));
        }
    };

    return removePromoCodeHandler;
};
