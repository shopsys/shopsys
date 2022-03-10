import { FC, useEffect } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import nookies, { destroyCookie } from 'nookies';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { getCurrentCustomerUser } from 'connectors/customer/CurrentCustomerUser';

const UserContactRefresher: FC = () => {
    const dispatch = useShopsysDispatch();
    const userState = useShopsysSelector((state) => state.user);
    const contactInformationState = useShopsysSelector((state) => state.contactInformation);
    const currentCustomerUser = getCurrentCustomerUser();
    const cookies = nookies.get();
    const isContactInformationCacheSet =
        'contactInformation' in cookies && JSON.parse(cookies.contactInformation).email !== '';
    const isUserNameEmpty = userState.userName.firstName === '' && userState.userName.lastName === '';

    useEffect(() => {
        if (!userState.isUserLoggedIn) {
            destroyCookie(null, 'contactInformation');

            if (!isUserNameEmpty) {
                dispatch(userActions.setUserName(initialState.userName));
            }

            return;
        }

        if (currentCustomerUser !== undefined && isUserNameEmpty) {
            dispatch(
                userActions.setUserName({
                    firstName: currentCustomerUser.firstName,
                    lastName: currentCustomerUser.lastName,
                }),
            );
        }

        if (isContactInformationCacheSet && JSON.stringify(contactInformationState) !== cookies.contactInformation) {
            dispatch(contactInformationActions.setContactInformation(JSON.parse(cookies.contactInformation)));
        } else if (currentCustomerUser && !isContactInformationCacheSet) {
            dispatch(contactInformationActions.setContactInformation(currentCustomerUser));
        }
    }, [currentCustomerUser, userState]);

    return null;
};

export default UserContactRefresher;
