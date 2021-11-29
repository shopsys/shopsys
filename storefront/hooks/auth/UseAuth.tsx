import { Exact, LoginApi, LogoutApi, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { UseMutationResponse } from 'urql';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useAuth = (): [
    UseMutationResponse<
        LoginApi,
        Exact<{
            email: string;
            password: any;
        }>
    >,
    UseMutationResponse<
        LogoutApi,
        Exact<{
            [key: string]: never;
        }>
    >,
] => {
    const loginUseMutationResponse = useLoginApi();
    const logoutUseMutationResponse = useLogoutApi();
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const router = useRouter();

    useEffect(() => {
        const accessToken = loginUseMutationResponse[0].data?.Login.accessToken;
        const refreshToken = loginUseMutationResponse[0].data?.Login.refreshToken;

        if (accessToken !== undefined && refreshToken !== undefined) {
            dispatch(userActions.setIsUserLoggedIn(true));
            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Successfully logged in'));
            window.location.href = router.asPath;
        }
    }, [loginUseMutationResponse[0].data?.Login]);

    useEffect(() => {
        if (loginUseMutationResponse[0].error !== undefined) {
            showErrorMessage(t('You have entered an incorrect email or password.'));
        }
    }, [loginUseMutationResponse[0].error]);

    useEffect(() => {
        if (logoutUseMutationResponse[0].data?.Logout === true) {
            dispatch(userActions.setIsUserLoggedIn(false));
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));
            window.location.href = router.asPath;
        }
    }, [logoutUseMutationResponse[0].data]);

    return [loginUseMutationResponse, logoutUseMutationResponse];
};
