import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { CartFragmentApi, useChangePaymentInCartMutationApi } from 'graphql/generated';
import { onPaymentChangeGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { useGtmCartEventInfo } from 'helpers/gtm/gtm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useLatest } from 'hooks/ui/useLatest';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';

export type ChangePaymentHandler = (
    newPaymentUuid: string | null,
    newGoPayBankSwift: string | null,
) => Promise<CartFragmentApi | undefined | null>;

export const useChangePaymentInCart = (): [ChangePaymentHandler, boolean] => {
    const [{ fetching }, changePaymentInCart] = useChangePaymentInCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();
    const gtmCartEventInfo = useGtmCartEventInfo();

    const gtmCart = useLatest(gtmCartEventInfo.cart);

    const changePaymentHandler = useCallback<ChangePaymentHandler>(
        async (newPaymentUuid, newGoPayBankSwift) => {
            const changePaymentResult = await changePaymentInCart(
                {
                    input: { paymentUuid: newPaymentUuid, paymentGoPayBankSwift: newGoPayBankSwift, cartUuid },
                },
                { additionalTypenames: ['dedup'] },
            );

            // EXTEND PAYMENT MODIFICATIONS HERE

            if (changePaymentResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(changePaymentResult.error, t);
                if (userError?.validation?.payment !== undefined) {
                    showErrorMessage(userError.validation.payment.message, 'transport pay');
                }
                if (userError?.validation?.goPaySwift !== undefined) {
                    showErrorMessage(userError.validation.goPaySwift.message, 'transport pay');
                }

                return null;
            }

            onPaymentChangeGtmEventHandler(
                gtmCart.current,
                changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
                currencyCode,
            );

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCart, currencyCode, gtmCart, t],
    );

    return [changePaymentHandler, fetching];
};
