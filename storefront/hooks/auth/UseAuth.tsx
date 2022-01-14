import {
    LoginApi,
    LoginVariablesApi,
    LogoutApi,
    LogoutVariablesApi,
    useLoginApi,
    useLogoutApi,
} from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { cartActions } from 'redux/slices/cart';
import { useEffect } from 'react';
import { useHandleCartDeletionOnLogout } from 'hooks/cart/UseHandleCartDeletionOnLogout';
import { useHandleCartUuidDeletionOnLogin } from 'hooks/cart/UseHandleCartUuidDeletionOnLogin';
import { UseMutationResponse } from 'urql';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useAuth = (): [
    UseMutationResponse<LoginApi, LoginVariablesApi>,
    UseMutationResponse<LogoutApi, LogoutVariablesApi>,
] => {
    const [loginUseMutationResponse, login] = useLoginApi();
    const [logoutUseMutationResponse, logout] = useLogoutApi();
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const router = useRouter();

    useEffect(() => {
        const accessToken = loginUseMutationResponse.data?.Login.accessToken;
        const refreshToken = loginUseMutationResponse.data?.Login.refreshToken;

        if (accessToken !== undefined && refreshToken !== undefined) {
            dispatch(userActions.setIsUserLoggedIn(true));
            dispatch(cartActions.setIsCartEmpty(false));
            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Successfully logged in'));
            window.location.href = router.asPath;
        }
    }, [loginUseMutationResponse.data?.Login]);

    useHandleCartUuidDeletionOnLogin(loginUseMutationResponse.data);

    useEffect(() => {
        if (loginUseMutationResponse.error !== undefined) {
            showErrorMessage(t('You have entered an incorrect email or password.'));
        }
    }, [loginUseMutationResponse.error]);

    useEffect(() => {
        if (logoutUseMutationResponse.data?.Logout === true) {
            dispatch(userActions.setIsUserLoggedIn(false));
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));
            window.location.href = router.asPath;
        }
    }, [logoutUseMutationResponse.data]);

    useHandleCartDeletionOnLogout(logoutUseMutationResponse.data);

    return [
        [loginUseMutationResponse, login],
        [logoutUseMutationResponse, logout],
    ];
};
