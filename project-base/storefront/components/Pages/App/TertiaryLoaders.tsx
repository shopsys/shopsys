import { useProductLists } from 'components/providers/ProductListsProvider';
import { useRouter } from 'next/router';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const TertiaryLoaders = () => {
    const router = useRouter();
    const { invalidateSession } = useProductLists();

    useBroadcastChannel('reloadPage', () => {
        invalidateSession();
        router.reload();
    });

    return null;
};
