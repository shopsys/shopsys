import { showSuccessMessage } from 'components/Helpers/toasts';
import { Exact, LoginApi, LoginVariablesApi, LogoutApi, Maybe, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'helpers/auth/tokens';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/zustand/usePersistStore';
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
    const t = useTypedTranslationFunction();
    const updateUserState = usePersistStore((store) => store.updateUserState);
    const updateLoginLoadingState = usePersistStore((store) => store.updateLoginLoadingState);

    const router = useRouter();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await loginMutation(variables);

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookie(accessToken, refreshToken);

            updateUserState({
                cartUuid: null,
            });

            updateLoginLoadingState(
                loginResult.data.Login.showCartMergeInfo ? 'loading-with-cart-modifications' : 'loading',
            );

            if (canUseDom()) {
                window.location.href = rewriteUrl ?? router.asPath;
            }
        }

        return loginResult;
    };

    const logout: LogoutHandler = async () => {
        const logoutResult = await logoutMutation({});

        if (logoutResult.data?.Logout) {
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));

            if (canUseDom()) {
                router.reload();
            }
        }

        return logoutResult;
    };

    return { login, logout };
};
