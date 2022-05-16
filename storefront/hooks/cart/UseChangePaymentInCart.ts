import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useChangePaymentInCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useShopsysSelector } from 'redux/main';

export const useChangePaymentInCart = (): typeof changePaymentHandler => {
    const [, changePaymentInCart] = useChangePaymentInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();

    const changePaymentHandler = async (newPaymentUuid: string | null, newGoPayBankSwift: string | null) => {
        const changePaymentResult = await changePaymentInCart({
            input: { paymentUuid: newPaymentUuid, paymentGoPayBankSwift: newGoPayBankSwift, cartUuid },
        });

        // EXTEND PAYMENT MODIFICATIONS HERE

        if (changePaymentResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(changePaymentResult.error, t);
            if (userError?.validation?.payment !== undefined) {
                showErrorMessage(userError.validation.payment.message);
            }
            if (userError?.validation?.goPaySwift !== undefined) {
                showErrorMessage(userError.validation.goPaySwift.message);
            }

            return null;
        }

        return changePaymentResult.data?.ChangePaymentInCart;
    };

    return changePaymentHandler;
};
