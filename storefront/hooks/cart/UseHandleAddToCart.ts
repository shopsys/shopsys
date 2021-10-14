import { AddToCartResultType, CartInput } from 'connectors/cart/types';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseMutationState } from 'urql';

export const useHandleAddToCart = (
    result: UseMutationState<
        { AddToCart: AddToCartResultType },
        {
            productUuid: string;
            quantity: number;
            isAbsoluteQuantity: boolean;
        } & CartInput
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
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result.data]);
};
