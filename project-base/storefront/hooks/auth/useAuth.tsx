import {
    LoginMutationVariables,
    LoginMutation,
    useLoginMutation,
} from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { removeTokensFromCookies } from 'helpers/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'helpers/auth/setTokensToCookies';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { OperationResult } from 'urql';

type LoginHandler = (
    variables: Omit<LoginMutationVariables, 'productListsUuids'>,
    rewriteUrl?: string,
) => Promise<OperationResult<LoginMutation, LoginMutationVariables>>;

type LogoutHandler = () => Promise<void>;

export const useAuth = () => {
    const [, loginMutation] = useLoginMutation();
    const [, logoutMutation] = useLogoutMutation();

    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);

    const router = useRouter();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await loginMutation({ ...variables, productListsUuids: Object.values(productListUuids) });

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken);

            updateCartUuid(null);
            updateProductListUuids({});

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
            updateProductListUuids({});
            removeTokensFromCookies();
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            updateAuthLoadingState('logout-loading');
            resetContactInformation();

            router.reload();
        }

        dispatchBroadcastChannel('reloadPage');
    };

    return { login, logout };
};
