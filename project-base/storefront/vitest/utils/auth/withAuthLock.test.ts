import { useLogoutStore } from 'store/useLogoutStore';
import { withAuthLock } from 'utils/auth/withAuthLock';
import { afterEach, expect, test, vi } from 'vitest';

afterEach(() => {
    useLogoutStore.setState({ logoutDeadlineByDomain: {} });
    vi.unstubAllGlobals();
    vi.useRealTimers();
});

test('a logout in one domain does not extend the ordinary lock timeout in another domain', async () => {
    vi.useFakeTimers();
    useLogoutStore.getState().setLogoutDeadline(1, Date.now() + 60_000);
    vi.stubGlobal('navigator', {
        locks: {
            request: vi.fn(
                (_name, options: LockOptions) =>
                    new Promise((_resolve, reject) => {
                        options.signal?.addEventListener('abort', () =>
                            reject(new DOMException('Aborted', 'AbortError')),
                        );
                    }),
            ),
        },
    });
    const action = vi.fn();
    const result = expect(withAuthLock(2, action)).rejects.toThrow('Authentication lock timed out');

    await vi.advanceTimersByTimeAsync(10_000);

    await result;
    expect(action).not.toHaveBeenCalled();
});

test('a late lock grant cannot start logout after the deadline even when timers were suspended', async () => {
    vi.useFakeTimers();
    useLogoutStore.getState().setLogoutDeadline(1, Date.now() + 60_000);
    let grant: () => void = () => {};
    vi.stubGlobal('navigator', {
        locks: {
            request: vi.fn(
                (name: string, _options: LockOptions, callback: LockGrantedCallback<unknown>) =>
                    new Promise((resolve) => {
                        grant = () => resolve(callback({ name, mode: 'exclusive' }));
                    }),
            ),
        },
    });
    const action = vi.fn();
    const result = expect(withAuthLock(1, action)).rejects.toThrow('Authentication lock timed out');

    vi.setSystemTime(Date.now() + 60_001);
    grant();

    await result;
    expect(action).not.toHaveBeenCalled();
});

test('an acquired request retains its ten-second network timeout after a long lock wait', async () => {
    vi.useFakeTimers();
    useLogoutStore.getState().setLogoutDeadline(1, Date.now() + 60_000);
    let grant: () => void = () => {};
    vi.stubGlobal('navigator', {
        locks: {
            request: vi.fn(
                (name: string, _options: LockOptions, callback: LockGrantedCallback<unknown>) =>
                    new Promise((resolve) => {
                        grant = () => resolve(callback({ name, mode: 'exclusive' }));
                    }),
            ),
        },
    });
    let signal: AbortSignal | undefined;
    const result = expect(
        withAuthLock(1, (requestSignal) => {
            signal = requestSignal;
            return new Promise(() => {});
        }),
    ).rejects.toThrow('Authentication request timed out');

    await vi.advanceTimersByTimeAsync(59_000);
    grant();
    await vi.advanceTimersByTimeAsync(9_999);
    expect(signal?.aborted).toBe(false);
    await vi.advanceTimersByTimeAsync(1);

    await result;
    expect(signal?.aborted).toBe(true);
});

test('an expired logout cannot start a fallback request without Web Locks', async () => {
    vi.useFakeTimers();
    useLogoutStore.getState().setLogoutDeadline(1, Date.now() - 1);
    vi.stubGlobal('navigator', {});
    const action = vi.fn();

    await expect(withAuthLock(1, action)).rejects.toThrow('Authentication lock timed out');

    expect(action).not.toHaveBeenCalled();
});
