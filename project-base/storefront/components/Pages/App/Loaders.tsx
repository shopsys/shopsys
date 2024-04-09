import { useRouter } from 'next/router';
import { useAuthLoader } from 'utils/app/useAuthLoader';
import { usePageLoader } from 'utils/app/usePageLoader';
import { useReloadCart } from 'utils/cart/useReloadCart';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const Loaders = () => {
    const router = useRouter();

    useAuthLoader();
    usePageLoader();
    useReloadCart();
    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    return null;
};
