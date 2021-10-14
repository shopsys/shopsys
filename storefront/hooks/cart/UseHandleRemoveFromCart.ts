import { CartApiType, CartInput } from 'connectors/cart/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { PaymentApiType } from 'connectors/payments/types';
import { TransportApiType } from 'connectors/transports/types';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseMutationState } from 'urql';

export const useHandleRemoveFromCart = (
    result: UseMutationState<
        { RemoveFromCart: CartApiType & { transport: TransportApiType; payment: PaymentApiType } },
        {
            cartItemUuid: string;
        } & CartInput
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
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result.data]);
};
