import { ApplicationErrors, getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { cartInputActions } from 'redux/slices/cartInput';
import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleCartErrors = (resultErrors: CombinedError | undefined, errorMessage: string): void => {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    useEffect(() => {
        if (resultErrors === undefined) {
            return;
        }

        const { userError, applicationError } = getUserFriendlyErrors(resultErrors, t);

        switch (applicationError?.type) {
            case ApplicationErrors.CART_NOT_FOUND:
                updateCartState(
                    dispatch,
                    { cart: null, payment: null, pickupPlace: null, transport: null },
                    { cartUuid: null, isCartEmpty: true, transport: null, payment: null, promoCode: null },
                );
                updateCartInputCookie(initCartInputCookie());
                break;
            case ApplicationErrors.DEFAULT:
                showErrorMessage(errorMessage);
                break;
        }

        if (userError?.validation !== undefined) {
            for (const invalidFieldName in userError.validation) {
                if (invalidFieldName === 'promoCode') {
                    dispatch(cartInputActions.setPromoCode(null));
                }
                showErrorMessage(userError.validation[invalidFieldName].message);
            }
        }
    }, [resultErrors]);
};
