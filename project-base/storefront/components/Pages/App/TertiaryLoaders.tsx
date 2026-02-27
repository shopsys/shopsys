import { useRouter } from 'next/router';
import { useRefetchComparedProducts } from 'utils/productLists/comparison/useRefetchComparedProducts';
import { useRefetchWishedProducts } from 'utils/productLists/wishlist/useRefetchWishedProducts';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const TertiaryLoaders = () => {
    const router = useRouter();

    useRefetchComparedProducts();
    useRefetchWishedProducts();
    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    return null;
};
