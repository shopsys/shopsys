import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useProductLists } from 'components/providers/ProductListsProvider';
import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { clearOrderConfirmationContext } from 'utils/order/orderConfirmationContextStorage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const useLogout = () => {
    const { invalidateSession } = useProductLists();
    const [, logoutMutation] = useLogoutMutation();

    const resetContactInformation = usePersistStore((s) => s.resetContactInformation);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const domainConfig = useDomainConfig();

    const logout = async () => {
        const logoutResult = await logoutMutation({}, { fetch: getAuthMutationFetcher(domainConfig) });

        if (logoutResult.data?.Logout) {
            invalidateSession();
            dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
            resetContactInformation();
            clearOrderConfirmationContext();
            updateProductListUuids({});
            storeAuthNotification(domainConfig.domainId, 'logout');

            performAuthHardNavigation();
        }
    };

    return logout;
};
