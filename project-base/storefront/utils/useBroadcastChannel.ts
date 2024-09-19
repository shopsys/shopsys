import { useEffect } from 'react';
import { isClient } from 'utils/isClient';
import { v4 as uuid } from 'uuid';

type BroadcastChannelsType = 'reloadPage' | 'refetchCart' | 'refetchComparedProducts' | 'refetchWishedProducts';
const tabId = uuid();

const broadcastChannelSameTabConfig: Record<BroadcastChannelsType, boolean> = {
    refetchCart: false,
    reloadPage: false,
    refetchComparedProducts: false,
    refetchWishedProducts: false,
};

export const dispatchBroadcastChannel = (name: BroadcastChannelsType, messageEventPayloadData?: any) => {
    const channel = isClient && 'BroadcastChannel' in window ? new BroadcastChannel(name) : undefined;

    channel?.postMessage({ tabId, ...messageEventPayloadData });
    channel?.close();
};

export const useBroadcastChannel = (name: BroadcastChannelsType, callBack: (messageEventData: any) => void) => {
    useEffect(() => {
        const channel = isClient && 'BroadcastChannel' in window ? new BroadcastChannel(name) : null;

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
