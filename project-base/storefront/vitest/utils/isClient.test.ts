import { afterEach, describe, expect, test, vi } from 'vitest';

afterEach(() => {
    vi.unstubAllGlobals();
    vi.resetModules();
});

describe('isClient', () => {
    test('returns false when window is null', async () => {
        vi.stubGlobal('window', null);
        vi.resetModules();

        const { isClient } = await import('utils/isClient');

        expect(isClient).toBe(false);
    });
});
