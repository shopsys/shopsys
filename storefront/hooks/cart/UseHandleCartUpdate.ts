import { CartFragmentApi, Maybe } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';

export const useHandleCartUpdate = (result: Maybe<CartFragmentApi> | undefined): void => {
    const { transport, promoCode } = useShopsysSelector((state) => state.cartInput);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        if (result === undefined || result === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result,
            transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
            promoCode,
            currencyCode,
        );
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result]);
};
