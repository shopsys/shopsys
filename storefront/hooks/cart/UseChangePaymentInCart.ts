import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useChangePaymentInCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { onPaymentChangeGtmEventHandler } from 'utils/Gtm/EventHandlers';
import { useGtmCartEventInfo } from 'utils/Gtm/Gtm';

export const useChangePaymentInCart = (): typeof changePaymentHandler => {
    const [, changePaymentInCart] = useChangePaymentInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();
    const gtmCartEventInfo = useGtmCartEventInfo();

    const changePaymentHandler = useCallback(
        async (newPaymentUuid: string | null, newGoPayBankSwift: string | null) => {
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

            onPaymentChangeGtmEventHandler(
                gtmCartEventInfo.cart,
                changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
                currencyCode,
            );

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCart, currencyCode, gtmCartEventInfo.cart, t],
    );

    return changePaymentHandler;
};
