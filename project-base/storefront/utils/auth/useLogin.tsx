import {
    LoginMutationVariables,
    LoginMutation,
    useLoginMutation,
} from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { OperationResult } from 'urql';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

type LoginHandler = (
    variables: Omit<LoginMutationVariables, 'productListsUuids'>,
    rewriteUrl?: string,
) => Promise<OperationResult<LoginMutation, LoginMutationVariables>>;

export const useLogin = () => {
    const [, loginMutation] = useLoginMutation();

    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

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

    return login;
};
