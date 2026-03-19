import { useRouter } from 'next/router';
import { useBroadcastChannel } from 'utils/useBroadcastChannel';

export const TertiaryLoaders = () => {
    const router = useRouter();

    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    return null;
};
