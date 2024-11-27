'use client';

import { useBroadcastChannel } from 'app/_hooks/useBroadcastChannel';
import { useRouter } from 'next/navigation';
import 'react-toastify/dist/ReactToastify.css';

export const BroadcastChannelProvider: FC = ({ children }) => {
    const router = useRouter();

    useBroadcastChannel('refreshPage', () => {
        router.refresh();
    });

    return <>{children}</>;
};

export default BroadcastChannelProvider;
