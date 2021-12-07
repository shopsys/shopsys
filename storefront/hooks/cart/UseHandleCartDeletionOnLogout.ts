import { LogoutApi } from 'graphql/generated';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleCartDeletionOnLogout = (result: LogoutApi | undefined): void => {
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        if (result !== undefined) {
            updateCartState(dispatch);
        }
    }, [result]);
};
