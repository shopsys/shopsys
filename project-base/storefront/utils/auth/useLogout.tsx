import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const useLogout = () => {
    const [, logoutMutation] = useLogoutMutation();

    const resetContactInformation = usePersistStore((s) => s.resetContactInformation);
    const updateAuthLoadingState = usePersistStore((s) => s.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const router = useRouter();
    const domainConfig = useDomainConfig();

    const logout = async () => {
        const logoutResult = await logoutMutation({}, { fetch: getAuthMutationFetcher(domainConfig) });

        if (logoutResult.data?.Logout) {
            resetContactInformation();
            updateProductListUuids({});
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            updateAuthLoadingState('logout-loading');

            dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
            router.reload();
        }
    };

    return logout;
};
