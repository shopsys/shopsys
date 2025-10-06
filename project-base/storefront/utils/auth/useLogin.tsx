import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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
    const [, loginMutation] = useLoginMutation();
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const handleActionsAfterLogin = useHandleActionsAfterLogin();
    const domainConfig = useDomainConfig();

    const login: LoginHandler = async (variables, rewriteUrl) => {
        const loginResult = await loginMutation({
            ...variables,
            productListsUuids: Object.values(productListUuids),
        });

        if (loginResult.data) {
            const accessToken = loginResult.data.Login.tokens.accessToken;
            const refreshToken = loginResult.data.Login.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken, domainConfig);

            handleActionsAfterLogin(loginResult.data.Login.showCartMergeInfo, rewriteUrl);
        }

        return loginResult;
    };

    return login;
};

export const useHandleActionsAfterLogin = () => {
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const router = useRouter();
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const domainConfig = useDomainConfig();

    const handleActionsAfterLogin = (showCartMergeInfo: boolean, rewriteUrl: string | undefined) => {
        updateCartUuid(null);
        updateProductListUuids({});

        updateAuthLoadingState(showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading');
        updateUserEntryState('login');

        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
        if (rewriteUrl) {
            router.replace(rewriteUrl).then(() => router.reload());
        } else {
            router.reload();
        }
    };

    return handleActionsAfterLogin;
};
