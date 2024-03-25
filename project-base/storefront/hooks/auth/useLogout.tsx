import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { removeTokensFromCookies } from 'helpers/auth/removeTokensFromCookies';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';

export const useLogout = () => {
    const [, logoutMutation] = useLogoutMutation();

    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const router = useRouter();

    const logout = async () => {
        const logoutResult = await logoutMutation({});

        if (logoutResult.data?.Logout) {
            updateProductListUuids({});
            removeTokensFromCookies();
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            updateAuthLoadingState('logout-loading');

            router.reload();
        }

        dispatchBroadcastChannel('reloadPage');
    };

    return logout;
};
