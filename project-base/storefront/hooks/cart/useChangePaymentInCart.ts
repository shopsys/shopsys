import { showErrorMessage } from 'helpers/visual/toasts';
import { CartFragmentApi, useChangePaymentInCartMutationApi } from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { onGtmPaymentChangeEventHandler } from 'helpers/gtm/eventHandlers';
import { useGtmCartInfo } from 'helpers/gtm/gtm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useLatest } from 'hooks/ui/useLatest';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { GtmMessageOriginType } from 'types/gtm/enums';

export type ChangePaymentHandler = (
    newPaymentUuid: string | null,
    newGoPayBankSwift: string | null,
) => Promise<CartFragmentApi | undefined | null>;

export const useChangePaymentInCart = (): [ChangePaymentHandler, boolean] => {
    const [{ fetching }, changePaymentInCart] = useChangePaymentInCartMutationApi();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const t = useTypedTranslationFunction();
    const { gtmCartInfo } = useGtmCartInfo();

    const gtmCart = useLatest(gtmCartInfo);

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
                    showErrorMessage(
                        userError.validation.payment.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }
                if (userError?.validation?.goPaySwift !== undefined) {
                    showErrorMessage(
                        userError.validation.goPaySwift.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }

                return null;
            }

            onGtmPaymentChangeEventHandler(
                gtmCart.current,
                changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
            );

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCart, gtmCart, t],
    );

    return [changePaymentHandler, fetching];
};
