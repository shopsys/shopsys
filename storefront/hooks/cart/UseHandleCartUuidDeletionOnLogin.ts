import { cartInputActions } from 'redux/slices/cartInput';
import { LoginApi } from 'graphql/generated';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleCartUuidDeletionOnLogin = (result: LoginApi | undefined): void => {
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        dispatch(cartInputActions.setCartUuid(null));
    }, [result]);
};
