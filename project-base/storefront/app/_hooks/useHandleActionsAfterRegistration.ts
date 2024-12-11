'use client';

import { useRouter } from 'next/navigation';
import { usePersistStore } from 'store/usePersistStore';

export const useHandleActionsAfterRegistration = () => {
    const router = useRouter();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    // const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const handleActionsAfterRegistration = (showCartMergeInfo: boolean) => {
        updateCartUuid(null);
        updateProductListUuids({});

        updateAuthLoadingState(
            showCartMergeInfo ? 'registration-loading-with-cart-modifications' : 'registration-loading',
        );

        // TODO: loading state
        // updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });

        updateUserEntryState('registration');

        // TODO: gtm
        // onGtmSendFormEventHandler(GtmFormType.registration);

        router.replace('/');
    };

    return handleActionsAfterRegistration;
};
