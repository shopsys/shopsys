import { cartActions } from 'redux/slices/cart';
import { LoginApi } from 'graphql/generated';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleCartUuidDeletionOnLogin = (result: LoginApi | undefined): void => {
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        if (result !== undefined) {
            dispatch(cartActions.setCartUuid(null));
        }
    }, [result]);
};
