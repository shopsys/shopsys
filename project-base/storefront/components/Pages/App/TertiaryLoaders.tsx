import { useProductLists } from 'components/providers/ProductListsProvider';
import { useRouter } from 'next/router';
import { useRef } from 'react';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';

export const TertiaryLoaders = () => {
    const router = useRouter();
    const { invalidateSession } = useProductLists();
    const reloadPending = useRef(false);

    const reloadWhenVisible = () => {
        if (reloadPending.current && !document.hidden) {
            reloadPending.current = false;
            router.reload();
        }
    };

    useBroadcastChannel('reloadPage', () => {
        invalidateSession();
        reloadPending.current = true;
        reloadWhenVisible();
    });
    useRefetchOnTabFocus(reloadWhenVisible);

    return null;
};
