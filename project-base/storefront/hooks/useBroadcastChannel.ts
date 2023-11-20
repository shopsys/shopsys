import { isClient } from 'helpers/isClient';
import { useEffect } from 'react';

type BroadcastChannelsType = 'reloadPage' | 'refetchCart';

export const dispatchBroadcastChannel = (name: BroadcastChannelsType, data?: any) => {
    const channel = isClient ? new BroadcastChannel(name) : undefined;

    channel?.postMessage(data);
    channel?.close();
};

export const useBroadcastChannel = (name: BroadcastChannelsType, callBack: (data: any) => void) => {
    useEffect(() => {
        const channel = isClient ? new BroadcastChannel(name) : null;

        if (!channel) {
            return void null;
        }

        channel.onmessage = (event) => {
            callBack(event.data);
        };

        return () => {
            channel.close();
        };
    }, []);
};
