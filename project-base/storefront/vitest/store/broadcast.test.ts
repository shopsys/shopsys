import { broadcast } from 'store/broadcast';
import { afterEach, expect, test, vi } from 'vitest';
import { createStore } from 'zustand/vanilla';

type Counter = {
    count: number;
    label: string;
    increment: () => void;
    replace: () => void;
};

afterEach(() => vi.unstubAllGlobals());

test('broadcasts changed values for both merged and replaced state and accepts remote updates', () => {
    const channel = { postMessage: vi.fn(), onmessage: (_event: MessageEvent) => {} };
    const channels: string[] = [];
    vi.stubGlobal(
        'BroadcastChannel',
        class {
            constructor(name: string) {
                channels.push(name);
                return channel;
            }
        },
    );
    const store = createStore<Counter>()(
        broadcast(
            (set, get) => ({
                count: 0,
                label: 'counter',
                increment: () => set((state) => ({ count: state.count + 1 })),
                replace: () => set({ ...get(), count: 10 }, true),
            }),
            'counter',
            2,
        ),
    );

    store.getState().increment();
    expect(store.getState()).toMatchObject({ count: 1, label: 'counter' });
    expect(channel.postMessage).toHaveBeenLastCalledWith({ count: 1 });

    store.getState().replace();
    expect(store.getState()).toMatchObject({ count: 10, label: 'counter' });
    expect(channel.postMessage).toHaveBeenLastCalledWith({ count: 10 });
    expect(channels).toEqual(['counter-2']);

    channel.onmessage(new MessageEvent('message', { data: { count: 20 } }));
    expect(store.getState().count).toBe(20);
    expect(channel.postMessage).toHaveBeenCalledTimes(2);
});
