import { showSuccessMessage } from 'components/Helpers/toasts';
import { Exact, LoginApi, LoginVariablesApi, LogoutApi, Maybe, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'helpers/auth/tokens';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useCallback } from 'react';
import { useShopsysDispatch } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { OperationResult } from 'urql';

export type LoginHandler = (
    variables: LoginVariablesApi,
    rewriteUrl?: string,
) => Promise<
    OperationResult<
        LoginApi,
        Exact<{
            email: string;
            password: any;
            previousCartUuid: Maybe<string>;
        }>
    >
>;

export type LogoutHandler = () => Promise<
    OperationResult<
        LogoutApi,
        Exact<{
            [key: string]: never;
        }>
    >
>;
export const useAuth = (): { login: typeof login; logout: typeof logout } => {
    const [, loginMutation] = useLoginApi();
    const [, logoutMutation] = useLogoutApi();
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const router = useRouter();

    const login = useCallback<LoginHandler>(
        async (variables, rewriteUrl) => {
            const loginResult = await loginMutation(variables);

            if (loginResult.data !== undefined) {
                const accessToken = loginResult.data.Login.tokens.accessToken;
                const refreshToken = loginResult.data.Login.tokens.refreshToken;

                dispatch(userActions.setCartUuid(null));
                setTokensToCookie(accessToken, refreshToken);

                if (loginResult.data.Login.showCartMergeInfo === true) {
                    dispatch(userActions.setLoginLoading('loading-with-cart-modifications'));
                } else {
                    dispatch(userActions.setLoginLoading('loading'));
                }

                if (canUseDom()) {
                    window.location.href = rewriteUrl ?? router.asPath;
                }
            }

            return loginResult;
        },
        [dispatch, loginMutation, router.asPath],
    );

    const logout = useCallback<LogoutHandler>(async () => {
        const logoutResult = await logoutMutation({});

        if (logoutResult.data?.Logout === true) {
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));

            if (canUseDom()) {
                router.reload();
            }
        }

        return logoutResult;
    }, [logoutMutation, router, t]);

    return { login, logout };
};
