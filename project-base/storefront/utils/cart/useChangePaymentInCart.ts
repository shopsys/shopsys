import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useChangePaymentInCartMutation } from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useLatest } from 'utils/ui/useLatest';

export type ChangePaymentInCart = (
    newPaymentUuid: string | null,
    newGoPayBankSwift: string | null,
) => Promise<TypeCartFragment | undefined | null>;

export const useChangePaymentInCart = () => {
    const [{ fetching: isChangingPaymentInOrder }, changePaymentInCartMutation] = useChangePaymentInCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { gtmCartInfo } = useGtmCartInfo();
    const { canSeePrices } = useAuthorization();

    const gtmCart = useLatest(gtmCartInfo);

    const changePaymentInCart = useCallback<ChangePaymentInCart>(
        async (newPaymentUuid) => {
            const changePaymentResult = await changePaymentInCartMutation(
                {
                    input: { paymentUuid: newPaymentUuid, paymentGoPayBankSwift: null, cartUuid },
                },
                { additionalTypenames: ['dedup'] },
            );

            // EXTEND PAYMENT MODIFICATIONS HERE

            if (changePaymentResult.error !== undefined) {
                return null;
            }

            import('gtm/handlers/onGtmPaymentChangeEventHandler').then(({ onGtmPaymentChangeEventHandler }) => {
                onGtmPaymentChangeEventHandler(
                    gtmCart.current,
                    changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
                    !canSeePrices,
                );
            });

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCartMutation, gtmCart, canSeePrices],
    );

    return { changePaymentInCart, isChangingPaymentInOrder };
};
