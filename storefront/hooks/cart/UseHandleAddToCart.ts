import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { AddToCartResultType } from 'connectors/cart/types';
import { getUserCookieDataFromCartResult } from 'utils/Cart/GetUserCookieDataFromCartResult';
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
        const updatedUserCookieData = getUserCookieDataFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedUserCookieData);
        updateUserDataCookie(updatedUserCookieData);
    }, [result.data]);
};
