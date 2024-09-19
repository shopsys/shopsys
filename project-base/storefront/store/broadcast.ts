import { isClient } from 'utils/isClient';
import { StateCreator, StoreMutatorIdentifier } from 'zustand';

export type Broadcast = <
    T,
    Mps extends [StoreMutatorIdentifier, unknown][] = [],
    Mcs extends [StoreMutatorIdentifier, unknown][] = [],
>(
    f: StateCreator<T, Mps, Mcs>,
    name: string,
) => StateCreator<T, Mps, Mcs>;

type BroadcastImpl = <T>(f: StateCreator<T, [], []>, name: string) => StateCreator<T, [], []>;

const broadcastImpl: BroadcastImpl = (f, name) => (set, get, store) => {
    type Item = { [key: string]: unknown };
    if (!isClient || !('BroadcastChannel' in window)) {
        return f(set, get, store);
    }

    const channel = new BroadcastChannel(name);

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
        if ((messageEvent.data as { sync: string }).sync === name) {
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
