import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangePaymentInCartMutation } from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { useLatest } from 'utils/ui/useLatest';

export type ChangePaymentInCart = (
    newPaymentUuid: string | null,
    newGoPayBankSwift: string | null,
) => Promise<TypeCartFragment | undefined | null>;

export const useChangePaymentInCart = () => {
    const [{ fetching: isChangingPaymentInOrder }, changePaymentInCartMutation] = useChangePaymentInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();
    const { gtmCartInfo } = useGtmCartInfo();
    const currentCustomerData = useCurrentCustomerData();

    const gtmCart = useLatest(gtmCartInfo);

    const changePaymentInCart = useCallback<ChangePaymentInCart>(
        async (newPaymentUuid, newGoPayBankSwift) => {
            const changePaymentResult = await changePaymentInCartMutation(
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

            import('gtm/handlers/onGtmPaymentChangeEventHandler').then(({ onGtmPaymentChangeEventHandler }) => {
                onGtmPaymentChangeEventHandler(
                    gtmCart.current,
                    changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
                    !!currentCustomerData?.arePricesHidden,
                );
            });

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCartMutation, gtmCart, t],
    );

    return { changePaymentInCart, isChangingPaymentInOrder };
};
