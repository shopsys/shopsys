import { CartApiType, CartInput } from 'connectors/cart/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseQueryState } from 'urql';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleCartUpdate = (
    result: UseQueryState<{ cart: CartApiType }, CartInput>,
    pickupPlaceIdentifier: string | null,
    promoCode: string | null,
): void => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    useHandleCartErrors(result.error, t('Could not load your cart'));

    useEffect(() => {
        if (result.data === undefined || result.data.cart === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.cart,
            pickupPlaceIdentifier,
            promoCode,
            currencyCode,
        );
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result.data]);
};
