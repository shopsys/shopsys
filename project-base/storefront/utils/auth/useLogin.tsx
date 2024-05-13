import {
    TypeLoginMutationVariables,
    TypeLoginMutation,
    useLoginMutation,
} from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { OperationResult } from 'urql';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

type LoginHandler = (
    variables: Omit<TypeLoginMutationVariables, 'productListsUuids'>,
    rewriteUrl?: string,
) => Promise<OperationResult<TypeLoginMutation, TypeLoginMutationVariables>>;

export const useLogin = () => {
    const [, TypeLoginMutation] = useLoginMutation();

    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const router = useRouter();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await TypeLoginMutation({
            ...variables,
            productListsUuids: Object.values(productListUuids),
        });

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken);

            updateCartUuid(null);
            updateProductListUuids({});

            updateAuthLoadingState(
                loginResult.data.Login.showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading',
            );

            dispatchBroadcastChannel('reloadPage');
            if (rewriteUrl) {
                router.replace(rewriteUrl).then(() => router.reload());
            } else {
                router.reload();
            }
        }

        return loginResult;
    };

    return login;
};
