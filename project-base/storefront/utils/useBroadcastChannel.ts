import { useEffect } from 'react';
import { isClient } from 'utils/isClient';
import { v4 as uuid } from 'uuid';

type BroadcastChannelsType = 'reloadPage' | 'refetchCart' | 'refetchComparison';
const tabId = uuid();

const broadcastChannelSameTabConfig: Record<BroadcastChannelsType, boolean> = {
    refetchCart: false,
    reloadPage: false,
    refetchComparison: false,
};

export const dispatchBroadcastChannel = (name: BroadcastChannelsType, messageEventPayloadData?: any) => {
    const channel = isClient ? new BroadcastChannel(name) : undefined;

    channel?.postMessage({ tabId, ...messageEventPayloadData });
    channel?.close();
};

export const useBroadcastChannel = (name: BroadcastChannelsType, callBack: (messageEventData: any) => void) => {
    useEffect(() => {
        const channel = isClient ? new BroadcastChannel(name) : null;

        if (!channel) {
            return void null;
        }

        channel.onmessage = (messageEvent) => {
            if (messageEvent.data.tabId !== tabId || broadcastChannelSameTabConfig[name]) {
                callBack(messageEvent.data);
            }
        };

        return () => {
            channel.close();
        };
    }, []);
};
