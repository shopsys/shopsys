import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeLoginMutation,
    TypeLoginMutationVariables,
    useLoginMutation,
} from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { OperationResult } from 'urql';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
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
        const loginResult = await loginMutation(
            {
                ...variables,
                productListsUuids: Object.values(productListUuids),
            },
            { fetch: getAuthMutationFetcher(domainConfig) },
        );

        if (loginResult.data) {
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

export const useLoginAfterPasswordRecovery = () => {
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const domainConfig = useDomainConfig();

    const handleActionsAfterPasswordRecovery = (showCartMergeInfo: boolean) => {
        updateCartUuid(null);
        updateProductListUuids({});

        updateAuthLoadingState(showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading');
        updateUserEntryState('login');
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });

        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
        window.location.href = '/';
    };

    return handleActionsAfterPasswordRecovery;
};
