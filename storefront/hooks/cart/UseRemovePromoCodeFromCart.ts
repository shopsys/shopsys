import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useRemovePromoCodeFromCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useShopsysSelector } from 'redux/main';

export const useRemovePromoCodeFromCart = (): typeof removePromoCodeHandler => {
    const [, removePromoCode] = useRemovePromoCodeFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
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
                showErrorMessage(userError.validation.promoCode.message, 'cart');
            } else {
                showErrorMessage(messages.error, 'cart');
            }

            return null;
        }

        showSuccessMessage(messages.success);

        return removePromoCodeResult.data?.RemovePromoCodeFromCart;
    };

    return removePromoCodeHandler;
};
