import { LoginApi, LoginVariablesApi, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookies } from 'helpers/auth/tokens';
import { useProductListUuids } from 'hooks/productLists/useProductListUuids';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { OperationResult } from 'urql';

type LoginHandler = (
    variables: Omit<LoginVariablesApi, 'productListsUuids'>,
    rewriteUrl?: string,
) => Promise<OperationResult<LoginApi, LoginVariablesApi>>;

type LogoutHandler = () => Promise<void>;

export const useAuth = () => {
    const [, loginMutation] = useLoginApi();
    const [, logoutMutation] = useLogoutApi();

    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const { getAllProductListUuids, removeAllProductListUuids } = useProductListUuids();

    const router = useRouter();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await loginMutation({ ...variables, productListsUuids: getAllProductListUuids() });

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken);

            updateCartUuid(null);
            removeAllProductListUuids();

            updateAuthLoadingState(
                loginResult.data.Login.showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading',
            );

            if (rewriteUrl) {
                router.replace(rewriteUrl).then(() => router.reload());
            } else {
                router.reload();
            }

            dispatchBroadcastChannel('reloadPage');
        }

        return loginResult;
    };

    const logout: LogoutHandler = async () => {
        const logoutResult = await logoutMutation({});

        if (logoutResult.data?.Logout) {
            removeAllProductListUuids();
            removeTokensFromCookies();
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            updateAuthLoadingState('logout-loading');

            router.reload();
        }

        dispatchBroadcastChannel('reloadPage');
    };

    return { login, logout };
};
