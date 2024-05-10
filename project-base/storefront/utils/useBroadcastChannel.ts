import { useEffect } from 'react';
import { isClient } from 'utils/isClient';
import { v4 as uuid } from 'uuid';

type BroadcastChannelsType = 'reloadPage' | 'refetchCart';
const tabId = uuid();

const broadcastChannelSameTabConfig: Record<BroadcastChannelsType, boolean> = {
    refetchCart: false,
    reloadPage: false,
};

export const dispatchBroadcastChannel = (name: BroadcastChannelsType, data?: any) => {
    const channel = isClient ? new BroadcastChannel(name) : undefined;

    channel?.postMessage({ tabId, ...data });
    channel?.close();
};

export const useBroadcastChannel = (name: BroadcastChannelsType, callBack: (data: any) => void) => {
    useEffect(() => {
        const channel = isClient ? new BroadcastChannel(name) : null;

        if (!channel) {
            return void null;
        }

        channel.onmessage = (event) => {
            if (event.data.tabId !== tabId || broadcastChannelSameTabConfig[name]) {
                callBack(event.data);
            }
        };

        return () => {
            channel.close();
        };
    }, []);
};
