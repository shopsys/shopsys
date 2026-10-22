import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { withAuthLock } from 'utils/auth/withAuthLock';
import { clearOrderConfirmationContext } from 'utils/order/orderConfirmationContextStorage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const useLogout = () => {
    const [, logoutMutation] = useLogoutMutation();

    const resetContactInformation = usePersistStore((s) => s.resetContactInformation);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const domainConfig = useDomainConfig();

    const logout = async () => {
        const logoutResult = await logoutMutation(
            {},
            {
                // URQL may need to refresh first; lock only the request that clears the cookies.
                fetch: (input, init) =>
                    withAuthLock(domainConfig.domainId, (signal) =>
                        getAuthMutationFetcher(domainConfig)(input, { ...init, signal }),
                    ),
            },
        );

        if (logoutResult.data?.Logout) {
            resetContactInformation();
            clearOrderConfirmationContext();
            updateProductListUuids({});
            storeAuthNotification(domainConfig.domainId, 'logout');

            dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
            performAuthHardNavigation();
        }
    };

    return logout;
};
