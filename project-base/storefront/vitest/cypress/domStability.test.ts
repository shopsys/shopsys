import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { waitForDomStability } from '../../cypress/support/domStability';

describe('DOM stability observation', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        document.body.replaceChildren();
    });

    test('waits for a complete quiet interval after the last mutation and disconnects', async () => {
        const disconnect = vi.spyOn(MutationObserver.prototype, 'disconnect');
        const settled = vi.fn();
        const pending = waitForDomStability(document, { quietPeriod: 100 }).then(settled);
        await vi.advanceTimersByTimeAsync(80);
        document.body.appendChild(document.createElement('div'));
        await Promise.resolve();
        await vi.advanceTimersByTimeAsync(80);
        expect(settled).not.toHaveBeenCalled();
        await vi.advanceTimersByTimeAsync(20);
        await pending;
        expect(settled).toHaveBeenCalledOnce();
        expect(disconnect).toHaveBeenCalledOnce();
        expect(vi.getTimerCount()).toBe(0);
    });

    test('rejects and releases observers and timers at the deadline', async () => {
        const disconnect = vi.spyOn(MutationObserver.prototype, 'disconnect');
        const pending = waitForDomStability(document, { quietPeriod: 500, timeout: 200 });
        const rejected = expect(pending).rejects.toThrow('DOM did not settle within 200ms');
        await vi.advanceTimersByTimeAsync(200);
        await rejected;
        expect(disconnect).toHaveBeenCalledOnce();
        expect(vi.getTimerCount()).toBe(0);
    });

    test('rejects and cleans up when navigating away', async () => {
        const disconnect = vi.spyOn(MutationObserver.prototype, 'disconnect');
        const pending = waitForDomStability(document);
        const rejected = expect(pending).rejects.toThrow('The page changed');
        window.dispatchEvent(new Event('pagehide'));
        await rejected;
        expect(disconnect).toHaveBeenCalledOnce();
        expect(vi.getTimerCount()).toBe(0);
    });
});
