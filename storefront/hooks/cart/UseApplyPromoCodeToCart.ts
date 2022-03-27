import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { cartActions } from 'redux/slices/cart';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useApplyPromoCodeToCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useApplyPromoCodeToCart = (): typeof applyPromoCodeHandler => {
    const [, applyPromoCodeToCart] = useApplyPromoCodeToCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.cart.cartInput);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const applyPromoCodeHandler = async (newPromoCode: string, messages: { success: string; error: string }) => {
        const applyPromoCodeResult = await applyPromoCodeToCart({ input: { promoCode: newPromoCode, cartUuid } });

        // EXTEND PROMO CODE MODIFICATIONS HERE

        if (applyPromoCodeResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(applyPromoCodeResult.error, t);
            if (userError?.validation?.promoCode !== undefined) {
                showErrorMessage(userError.validation.promoCode.message);

                return;
            }
            showErrorMessage(messages.error);
        } else if (applyPromoCodeResult.data !== undefined) {
            showSuccessMessage(messages.success);
            dispatch(cartActions.setPromoCode(applyPromoCodeResult.data.ApplyPromoCodeToCart.promoCode));
        }
    };

    return applyPromoCodeHandler;
};
