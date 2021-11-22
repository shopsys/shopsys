import { Exact, LoginApi, LogoutApi, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { UseMutationResponse } from 'urql';
import { userActions } from 'redux/slices/user';
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

    useEffect(() => {
        if (
            loginUseMutationResponse[0].data?.Login.accessToken !== undefined &&
            loginUseMutationResponse[0].data?.Login.refreshToken !== undefined
        ) {
            dispatch(userActions.setIsUserLoggedIn(true));
            setTokensToCookie(
                loginUseMutationResponse[0].data.Login.accessToken,
                loginUseMutationResponse[0].data?.Login.refreshToken,
            );
            showSuccessMessage(t('Successfully logged in'));
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
        }
    }, [logoutUseMutationResponse[0].data]);

    return [loginUseMutationResponse, logoutUseMutationResponse];
};
