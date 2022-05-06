import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useApplyPromoCodeToCartMutationApi } from 'graphql/generated';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useApplyPromoCodeToCart = (): typeof applyPromoCodeHandler => {
    const [, applyPromoCodeToCart] = useApplyPromoCodeToCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();

    const applyPromoCodeHandler = async (newPromoCode: string, messages: { success: string; error: string }) => {
        const applyPromoCodeResult = await applyPromoCodeToCart({ input: { promoCode: newPromoCode, cartUuid } });

        // EXTEND PROMO CODE MODIFICATIONS HERE

        if (applyPromoCodeResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(applyPromoCodeResult.error, t);
            if (userError?.validation?.promoCode !== undefined) {
                showErrorMessage(userError.validation.promoCode.message);
            } else {
                showErrorMessage(messages.error);
            }

            return null;
        }

        showSuccessMessage(messages.success);

        return applyPromoCodeResult.data?.ApplyPromoCodeToCart;
    };

    return applyPromoCodeHandler;
};
