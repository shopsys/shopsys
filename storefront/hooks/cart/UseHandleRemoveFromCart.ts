import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { PaymentApiType, PaymentInputType } from 'connectors/payments/types';
import { TransportApiType, TransportInputType } from 'connectors/transports/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { CartApiType } from 'connectors/cart/types';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { updateUserDataCookie } from 'helpers/Cookies';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseMutationState } from 'urql';

export const useHandleRemoveFromCart = (
    result: UseMutationState<
        { RemoveFromCart: CartApiType & { transport: TransportApiType; payment: PaymentApiType } },
        {
            cartUuid: string;
            cartItemUuid: string;
            transport: TransportInputType | null;
            payment: PaymentInputType | null;
            promoCode: string | null;
        }
    >,
    personalPickupStoreUuid: string | null,
    promoCode: string | null,
): void => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();

    useHandleCartErrors(result.error);

    useEffect(() => {
        if (result.data === undefined || result.data.RemoveFromCart === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.RemoveFromCart,
            personalPickupStoreUuid,
            promoCode,
            currencyCode,
        );
        const updatedUserCookieData = {
            cartUuid: cartResultValues.cart?.uuid === undefined ? null : cartResultValues.cart.uuid,
            transport:
                cartResultValues.transport === null
                    ? null
                    : mapTransportToTransportInput(cartResultValues.transport, cartResultValues.personalPickupStore),
            payment: cartResultValues.payment === null ? null : mapPaymentToPaymentInput(cartResultValues.payment),
            promoCode: cartResultValues.promoCode,
        };
        updateCartState(
            dispatch,
            cartResultValues.cart,
            cartResultValues.transport,
            cartResultValues.personalPickupStore,
            cartResultValues.payment,
            cartResultValues.promoCode,
            updatedUserCookieData,
        );
        updateUserDataCookie(updatedUserCookieData);
    }, [result.data]);
};
