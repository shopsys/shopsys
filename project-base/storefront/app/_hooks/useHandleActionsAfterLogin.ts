'use client';

import { dispatchBroadcastChannel } from 'app/_hooks/useBroadcastChannel';
import { useRouter } from 'next/navigation';
import { usePersistStore } from 'store/usePersistStore';

export const useHandleActionsAfterLogin = () => {
    const router = useRouter();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);

    const handleActionsAfterLogin = (showCartMergeInfo: boolean, rewriteUrl: string | undefined) => {
        updateCartUuid(null);
        updateProductListUuids({});

        updateAuthLoadingState(showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading');
        updateUserEntryState('login');

        dispatchBroadcastChannel('refreshPage');

        if (rewriteUrl) {
            router.replace(rewriteUrl);
            router.refresh();
        } else {
            router.refresh();
        }
    };

    return handleActionsAfterLogin;
};
