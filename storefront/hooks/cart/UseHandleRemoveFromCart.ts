import { PaymentApiType, PaymentInputType } from 'connectors/payments/types';
import { TransportApiType, TransportInputType } from 'connectors/transports/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { CartApiType } from 'connectors/cart/types';
import { getUserCookieDataFromCartResult } from 'utils/Cart/GetUserCookieDataFromCartResult';
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
        const updatedUserCookieData = getUserCookieDataFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedUserCookieData);
        updateUserDataCookie(updatedUserCookieData);
    }, [result.data]);
};
