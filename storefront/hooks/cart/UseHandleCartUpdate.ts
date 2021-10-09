import { CartApiType, CartInput } from 'connectors/cart/types';
import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { PaymentApiType } from 'connectors/payments/types';
import { TransportApiType } from 'connectors/transports/types';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { updateUserDataCookie } from 'helpers/Cookies';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseQueryState } from 'urql';

export const useHandleCartUpdate = (
    result: UseQueryState<{ cart: CartApiType & { transport: TransportApiType; payment: PaymentApiType } }, CartInput>,
    personalPickupStoreUuid: string | null,
    promoCode: string | null,
): void => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();

    useHandleCartErrors(result.error);

    useEffect(() => {
        if (result.data === undefined) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.cart,
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
