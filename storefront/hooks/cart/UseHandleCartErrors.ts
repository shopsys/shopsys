import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';

export const useHandleCartErrors = (resultErrors: CombinedError | undefined): void => {
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        if (resultErrors === undefined) {
            return;
        }

        // TODO refactor
        for (const error of resultErrors.graphQLErrors) {
            if (
                /Cart "\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b" is unavailable./.test(
                    error.message,
                )
            ) {
                updateCartState(
                    dispatch,
                    { cart: null, payment: null, personalPickupStore: null, transport: null },
                    { cartUuid: null, transport: null, payment: null, promoCode: null },
                );
                updateCartInputCookie(initCartInputCookie());
            }

            if (error.extensions?.validation === undefined) {
                return;
            }

            for (const invalidFieldName in error.extensions.validation) {
                for (const validationError of error.extensions.validation[invalidFieldName]) {
                    showErrorMessage(validationError.message);
                }
            }
        }
    }, [resultErrors]);
};
