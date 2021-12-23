import { CartFragmentApi, Maybe } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';

export const useHandleCartUpdate = (result: Maybe<CartFragmentApi> | undefined): void => {
    const { isUserLoggedIn } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        if (result === undefined || result === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(result, currencyCode, isUserLoggedIn);
        updateCartState(dispatch, cartResultValues);
    }, [result]);
};
