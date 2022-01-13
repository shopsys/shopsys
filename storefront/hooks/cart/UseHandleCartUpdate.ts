import { CartFragmentApi, Maybe } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { getCurrentCustomerUser } from 'connectors/customer/CurrentCustomerUser';
import { getValuesFromCartResult } from 'utils/Cart/GetValuesFromCartResult';
import nookies from 'nookies';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useEffect } from 'react';

export const useHandleCartUpdate = (result: Maybe<CartFragmentApi> | undefined): void => {
    const { isUserLoggedIn } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const currentCustomerUser = getCurrentCustomerUser();
    const cookies = nookies.get();

    useEffect(() => {
        if (result === undefined || result === null) {
            return;
        }

        // TODO handle modifications
        const cartResultValues = getValuesFromCartResult(result, currencyCode, isUserLoggedIn);
        updateCartState(dispatch, cartResultValues);

        if (isUserLoggedIn) {
            if (cookies.contactInformation !== undefined) {
                dispatch(contactInformationActions.setContactInformation(JSON.parse(cookies.contactInformation)));
            } else if (currentCustomerUser !== undefined) {
                dispatch(contactInformationActions.setContactInformation(currentCustomerUser));
            }
        }
    }, [result]);
};
