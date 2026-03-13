import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useEffect, useRef } from 'react';
import { isClient } from 'utils/isClient';
import { v4 as uuid } from 'uuid';

type BroadcastChannelsType = 'reloadPage' | 'refetchCart';
const tabId = uuid();

const broadcastChannelSameTabConfig: Record<BroadcastChannelsType, boolean> = {
    refetchCart: false,
    reloadPage: false,
};

export const dispatchBroadcastChannel = (
    name: BroadcastChannelsType,
    domainId: number,
    messageEventPayloadData?: any,
) => {
    const channelName = `${name}_${domainId}`;
    const channel = isClient && 'BroadcastChannel' in window ? new BroadcastChannel(channelName) : undefined;

    channel?.postMessage({ tabId, domainId, ...messageEventPayloadData });
    channel?.close();
};

export const useBroadcastChannel = (name: BroadcastChannelsType, callBack: (messageEventData: any) => void) => {
    const domainConfig = useDomainConfig();
    const callbackRef = useRef(callBack);

    // Keep the callback ref up-to-date with the latest callback
    useEffect(() => {
        callbackRef.current = callBack;
    }, [callBack]);

    useEffect(() => {
        const domainId = domainConfig.domainId;
        const channelName = `${name}_${domainId}`;
        const channel = isClient && 'BroadcastChannel' in window ? new BroadcastChannel(channelName) : null;

        if (!channel) {
            return void null;
        }

        channel.onmessage = (messageEvent) => {
            // Only process messages from same domain and different tabs (or same tab if configured)
            if (
                messageEvent.data.domainId === domainId &&
                (messageEvent.data.tabId !== tabId || broadcastChannelSameTabConfig[name])
            ) {
                callbackRef.current(messageEvent.data);
            }
        };

        return () => {
            channel.close();
        };
    }, [domainConfig.domainId, name]);
};
