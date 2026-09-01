import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeLoginMutation,
    TypeLoginMutationVariables,
    useLoginMutation,
} from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { OperationResult } from 'urql';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
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
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const domainConfig = useDomainConfig();

    const handleActionsAfterLogin = (showCartMergeInfo: boolean, rewriteUrl: string | undefined) => {
        updateCartUuid(null);
        updateProductListUuids({});

        storeAuthNotification(domainConfig.domainId, showCartMergeInfo ? 'login-with-cart-modifications' : 'login');
        updateUserEntryState('login');

        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
        performAuthHardNavigation(rewriteUrl);
    };

    return handleActionsAfterLogin;
};

export const useLoginAfterPasswordRecovery = () => {
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const domainConfig = useDomainConfig();

    const handleActionsAfterPasswordRecovery = (showCartMergeInfo: boolean) => {
        updateCartUuid(null);
        updateProductListUuids({});

        storeAuthNotification(domainConfig.domainId, showCartMergeInfo ? 'login-with-cart-modifications' : 'login');
        updateUserEntryState('login');

        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
        performAuthHardNavigation('/');
    };

    return handleActionsAfterPasswordRecovery;
};
