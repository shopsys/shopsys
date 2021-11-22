import { RemoveFromCartMutationApi, RemoveFromCartMutationVariablesApi } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getCartInputFromCartResult } from 'utils/Cart/GetCartInputFromCartResult';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartInputCookie } from 'helpers/Cookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useHandleCartErrors } from './UseHandleCartErrors';
import { UseMutationState } from 'urql';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleRemoveFromCart = (
    result: UseMutationState<RemoveFromCartMutationApi, RemoveFromCartMutationVariablesApi>,
    pickupPlaceIdentifier: string | null,
    promoCode: string | null,
): void => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    useHandleCartErrors(result.error, t('Could not remove the product from cart'));

    useEffect(() => {
        if (result.data === undefined || result.data.RemoveFromCart === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(
            result.data.RemoveFromCart,
            pickupPlaceIdentifier,
            promoCode,
            currencyCode,
        );
        const updatedCartInputData = getCartInputFromCartResult(cartResultValues);
        updateCartState(dispatch, cartResultValues, updatedCartInputData);
        updateCartInputCookie(updatedCartInputData);
    }, [result.data]);
};
