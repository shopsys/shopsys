import { isClient } from 'utils/isClient';
import { StateCreator, StoreMutatorIdentifier } from 'zustand';

type Broadcast = <
    T,
    Mps extends [StoreMutatorIdentifier, unknown][] = [],
    Mcs extends [StoreMutatorIdentifier, unknown][] = [],
>(
    f: StateCreator<T, Mps, Mcs>,
    name: string,
    domainId: number,
) => StateCreator<T, Mps, Mcs>;

type BroadcastImpl = <T>(f: StateCreator<T, [], []>, name: string, domainId: number) => StateCreator<T, [], []>;

const broadcastImpl: BroadcastImpl = (f, name, domainId) => (set, get, store) => {
    type Item = { [key: string]: unknown };
    if (!isClient || !('BroadcastChannel' in window)) {
        return f(set, get, store);
    }

    // Create domain-specific channel name
    const channelName = `${name}-${domainId}`;
    const channel = new BroadcastChannel(channelName);

    const onSet: typeof set = (...args) => {
        const previous = get() as Item;
        set(...args);

        const updated = get() as Item;

        // Get only state that changed
        const state = Object.entries(updated).reduce((obj, [key, val]) => {
            let newObj = { ...obj };
            if (previous[key] !== val) {
                newObj = { ...newObj, [key]: val };
            }
            return newObj;
        }, {} as Item);

        channel.postMessage(state);
    };

    channel.onmessage = (messageEvent) => {
        if ((messageEvent.data as { sync: string }).sync === channelName) {
            // Remove all functions and symbols from the store
            const state = Object.entries(get() as Item).reduce((obj, [key, val]) => {
                let newObj = { ...obj };
                if (typeof val !== 'function' && typeof val !== 'symbol') {
                    newObj = { ...newObj, [key]: val };
                }
                return newObj;
            }, {});

            channel.postMessage(state);

            return;
        }

        set(messageEvent.data);
    };

    return f(onSet, get, store);
};

export const broadcast = broadcastImpl as Broadcast;
