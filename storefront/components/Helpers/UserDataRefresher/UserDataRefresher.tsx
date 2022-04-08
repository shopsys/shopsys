import { FC, useEffect } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { destroyCookie } from 'nookies';
import { useCurrentCustomerUser } from 'connectors/customer/CurrentCustomerUser';

const UserDataRefresher: FC = () => {
    const dispatch = useShopsysDispatch();
    const userState = useShopsysSelector((state) => state.user);
    const currentCustomerUser = useCurrentCustomerUser();
    const isUserNameEmpty = userState.userName.firstName === '' && userState.userName.lastName === '';

    useEffect(() => {
        if (currentCustomerUser !== undefined) {
            dispatch(contactInformationActions.setContactInformation(currentCustomerUser));
        }
    }, [currentCustomerUser]);

    useEffect(() => {
        if (!userState.isUserLoggedIn) {
            destroyCookie(null, 'contactInformation');

            if (!isUserNameEmpty) {
                dispatch(userActions.setUserName(initialState.userName));
            }
        }
    }, [userState]);

    return null;
};

export default UserDataRefresher;
