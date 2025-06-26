'use client';

import { useBroadcastChannel } from 'app/_hooks/useBroadcastChannel';
import { useRouter } from 'next/navigation';

export const BroadcastChannelProvider: FC = () => {
    const router = useRouter();

    useBroadcastChannel('refreshPage', () => {
        router.refresh();
    });

    return null;
};

export default BroadcastChannelProvider;
