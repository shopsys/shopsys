import { act, renderHook } from '@testing-library/react';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useComparisonProducts } from 'utils/productLists/comparison/useComparisonProducts';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const viewport = vi.hoisted(() => ({ width: 1200 }));
vi.mock('utils/ui/useGetWindowSize', () => ({ useGetWindowSize: () => viewport }));
const products = ['a', 'b', 'c', 'd'].map((uuid) => ({ uuid }) as TypeProductInProductListFragment);

describe('useComparisonProducts ordering', () => {
    beforeEach(() => {
        viewport.width = 1200;
    });

    test.each([600, 667, 669, 768])('shows all products with desktop controls at %s pixels', (width) => {
        viewport.width = width;
        const { result } = renderHook(() => useComparisonProducts(products));
        expect(result.current.visibleProducts).toHaveLength(4);
        expect(result.current.canReorder).toBe(true);
    });


    test('preserves manual order through refetches and appends new products while omitting removed ones', () => {
        viewport.width = 1200;
        const { result, rerender } = renderHook(({ items }) => useComparisonProducts(items), {
            initialProps: { items: products.slice(0, 3) },
        });
        act(() => result.current.reorderProducts(['c', 'a', 'b']));
        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['c', 'a', 'b']);
        rerender({ items: [products[1], products[2], products[3]] });
        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['c', 'b', 'd']);
    });

    test('keeps mobile pair selection and disables drag handles after switching from desktop', () => {
        viewport.width = 1200;
        const { result, rerender } = renderHook(() => useComparisonProducts(products));
        act(() => result.current.reorderProducts(['c', 'b', 'a', 'd']));
        viewport.width = 390;
        rerender();
        expect(result.current.canReorder).toBe(false);
        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['c', 'b']);
        act(() => result.current.selectMobileProduct(0, 'd'));
        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['d', 'b']);
    });
    test('queues moves and sends each of them after the in-flight request', async () => {
        viewport.width = 1200;
        let finishFirst: (success: boolean) => void = () => {};
        const save = vi
            .fn()
            .mockImplementationOnce(
                () =>
                    new Promise<boolean>((resolve) => {
                        finishFirst = resolve;
                    }),
            )
            .mockResolvedValue(true);
        const { result } = renderHook(() => useComparisonProducts(products, save));
        act(() => result.current.reorderProducts(['b', 'a', 'c', 'd']));
        let pending: Promise<void>;
        act(() => {
            pending = result.current.saveProductMove('b');
        });
        act(() => result.current.reorderProducts(['c', 'b', 'a', 'd']));
        await act(async () => {
            await result.current.saveProductMove('c');
        });
        expect(save).toHaveBeenCalledTimes(1);
        await act(async () => {
            finishFirst(true);
            await pending;
        });
        expect(save.mock.calls).toEqual([
            ['b', null],
            ['c', null],
        ]);
    });

    test('restores server order when saving fails', async () => {
        viewport.width = 1200;
        const save = vi.fn().mockResolvedValue(false);
        const { result } = renderHook(() => useComparisonProducts(products, save));
        act(() => result.current.reorderProducts(['b', 'a', 'c', 'd']));
        await act(async () => {
            await result.current.saveProductMove('b');
        });
        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['a', 'b', 'c', 'd']);
    });

    test('follows subsequent server order after a successful save', async () => {
        viewport.width = 1200;
        const save = vi.fn().mockResolvedValue(true);
        const { result, rerender } = renderHook(({ items }) => useComparisonProducts(items, save), {
            initialProps: { items: products.slice(0, 3) },
        });
        act(() => result.current.reorderProducts(['c', 'a', 'b']));

        await act(async () => {
            await result.current.saveProductMove('c');
        });
        rerender({ items: [products[2], products[0], products[1]] });
        rerender({ items: [products[1], products[2], products[0]] });

        expect(result.current.visibleProducts.map((p) => p.uuid)).toEqual(['b', 'c', 'a']);
    });

    test('keeps the parameter source order when the server confirms reordered columns', () => {
        const { result, rerender } = renderHook(({ items }) => useComparisonProducts(items), {
            initialProps: { items: products },
        });
        rerender({ items: [products[2], products[0], products[1], products[3]] });
        expect(result.current.parameterSourceProducts.map((p) => p.uuid)).toEqual(['a', 'b', 'c', 'd']);
    });
});
