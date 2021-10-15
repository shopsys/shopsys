import { CartApiType, CartInput } from 'connectors/cart/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { cartInputActions } from 'redux/slices/cartInput';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseQueryState } from 'urql';

export const useHandleCartUpdate = (
    result: UseQueryState<{ cart: CartApiType }, CartInput>,
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

        if (result.data !== undefined && result.error !== undefined) {
            dispatch(cartInputActions.setPromoCode(null));
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.cart,
            personalPickupStoreUuid,
            promoCode,
            currencyCode,
        );
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result.data]);
};
