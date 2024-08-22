import { useRouter } from 'next/router';
import { useAfterUserEntry } from 'utils/app/useAfterUserEntry';
import { useAuthLoader } from 'utils/app/useAuthLoader';
import { usePageLoader } from 'utils/app/usePageLoader';
import { useReloadCart } from 'utils/cart/useReloadCart';
import { useRefetchComparison } from 'utils/productLists/comparison/useRefetchComparison';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const Loaders = () => {
    const router = useRouter();

    useAuthLoader();
    usePageLoader();
    useReloadCart();
    useAfterUserEntry();
    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });
    useRefetchComparison();

    return null;
};
