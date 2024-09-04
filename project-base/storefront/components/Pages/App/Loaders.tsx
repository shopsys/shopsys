import { useRouter } from 'next/router';
import { useAfterUserEntry } from 'utils/app/useAfterUserEntry';
import { useAuthLoader } from 'utils/app/useAuthLoader';
import { usePageLoader } from 'utils/app/usePageLoader';
import { useReloadCart } from 'utils/cart/useReloadCart';
import { useRefetchComparedProducts } from 'utils/productLists/comparison/useRefetchComparedProducts';
import { useRefetchWishedProducts } from 'utils/productLists/wishlist/useRefetchWishedProducts';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const Loaders = () => {
    const router = useRouter();

    useAuthLoader();
    usePageLoader();
    useReloadCart();
    useAfterUserEntry();
    useRefetchComparedProducts();
    useRefetchWishedProducts();
    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    return null;
};
