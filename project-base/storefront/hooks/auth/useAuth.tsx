import { Exact, LoginApi, LoginVariablesApi, LogoutApi, Maybe, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookies } from 'helpers/auth/tokens';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
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
export const useAuth = () => {
    const [, loginMutation] = useLoginApi();
    const [, logoutMutation] = useLogoutApi();
    const updateUserState = usePersistStore((store) => store.updateUserState);
    const updateWishlistUuid = usePersistStore((store) => store.updateWishlistUuid);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);

    const router = useRouter();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await loginMutation(variables);

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken);

            updateUserState({
                cartUuid: null,
            });

            updateAuthLoadingState(
                loginResult.data.Login.showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading',
            );

            window.location.href = rewriteUrl ?? router.asPath;
        }

        return loginResult;
    };

    const logout: LogoutHandler = async () => {
        const logoutResult = await logoutMutation({});

        if (logoutResult.data?.Logout) {
            updateWishlistUuid(null);
            removeTokensFromCookies();
            updateAuthLoadingState('logout-loading');

            router.reload();
        }

        return logoutResult;
    };

    return { login, logout };
};
