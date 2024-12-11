'use client';

import { dispatchBroadcastChannel } from 'app/_hooks/useBroadcastChannel';
import { useRouter } from 'next/navigation';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';

export const useHandleActionsAfterLogout = () => {
    const router = useRouter();
    const resetContactInformation = usePersistStore((s) => s.resetContactInformation);
    const updateAuthLoadingState = usePersistStore((s) => s.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const handleActionsAfterLogout = () => {
        resetContactInformation();
        updateProductListUuids({});
        removeTokensFromCookies();
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
        updateAuthLoadingState('logout-loading');

        dispatchBroadcastChannel('refreshPage');

        router.refresh();
    };

    return handleActionsAfterLogout;
};
