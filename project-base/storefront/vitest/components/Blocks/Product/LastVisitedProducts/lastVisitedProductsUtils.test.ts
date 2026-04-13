import { renderHook } from '@testing-library/react';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockSetCookiesStoreState = vi.fn();
let mockStoredCatnums: string[] | undefined;

vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (state: any) => unknown) =>
        selector({
            lastVisitedProductsCatnums: mockStoredCatnums,
            setCookiesStoreState: mockSetCookiesStoreState,
        }),
}));

const getLastStoredCatnums = (): string[] => {
    const lastCall = mockSetCookiesStoreState.mock.calls.at(-1);
    return lastCall?.[0].lastVisitedProductsCatnums;
};

const buildCatnums = (count: number, prefix = 'product-'): string[] =>
    Array.from({ length: count }, (_, i) => `${prefix}${i + 1}`);

describe('useLastVisitedProductView', () => {
    beforeEach(() => {
        mockStoredCatnums = undefined;
        mockSetCookiesStoreState.mockClear();
    });

    test('stores the visited product when the cookie is empty', () => {
        mockStoredCatnums = undefined;

        renderHook(() => useLastVisitedProductView('new-product'));

        expect(getLastStoredCatnums()).toEqual(['new-product']);
    });

    test('prepends the visited product to existing catnums', () => {
        mockStoredCatnums = ['existing-1', 'existing-2'];

        renderHook(() => useLastVisitedProductView('new-product'));

        expect(getLastStoredCatnums()).toEqual(['new-product', 'existing-1', 'existing-2']);
    });

    test('deduplicates when the visited product is already stored', () => {
        mockStoredCatnums = ['existing-1', 'revisited', 'existing-2'];

        renderHook(() => useLastVisitedProductView('revisited'));

        expect(getLastStoredCatnums()).toEqual(['revisited', 'existing-1', 'existing-2']);
    });

    test('keeps lists shorter than the storage cap intact', () => {
        mockStoredCatnums = buildCatnums(10, 'existing-');

        renderHook(() => useLastVisitedProductView('new-product'));

        const stored = getLastStoredCatnums();
        expect(stored).toHaveLength(11);
        expect(stored[0]).toBe('new-product');
    });

    test('stores up to 15 items when adding to a full list of 14', () => {
        mockStoredCatnums = buildCatnums(14, 'existing-');

        renderHook(() => useLastVisitedProductView('new-product'));

        const stored = getLastStoredCatnums();
        expect(stored).toHaveLength(15);
        expect(stored[0]).toBe('new-product');
        expect(stored.at(-1)).toBe('existing-14');
    });

    test('caps storage at 15 items and drops the oldest when a new product is added to a full list', () => {
        mockStoredCatnums = buildCatnums(15, 'existing-');

        renderHook(() => useLastVisitedProductView('new-product'));

        const stored = getLastStoredCatnums();
        expect(stored).toHaveLength(15);
        expect(stored[0]).toBe('new-product');
        expect(stored).not.toContain('existing-15');
        expect(stored.at(-1)).toBe('existing-14');
    });
});
