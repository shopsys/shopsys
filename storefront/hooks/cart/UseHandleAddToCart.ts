import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { AddToCartResultType } from 'connectors/cart/types';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { PaymentInputType } from 'connectors/payments/types';
import { TransportInputType } from 'connectors/transports/types';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { updateUserDataCookie } from 'helpers/Cookies';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseMutationState } from 'urql';

export const useHandleAddToCart = (
    result: UseMutationState<
        { AddToCart: AddToCartResultType },
        {
            cartUuid: string | null;
            productUuid: string;
            quantity: number;
            isAbsoluteQuantity: boolean;
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
        if (result.data === undefined || result.data.AddToCart === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.AddToCart,
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
