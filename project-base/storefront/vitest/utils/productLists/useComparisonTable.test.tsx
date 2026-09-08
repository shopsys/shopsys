import { act, renderHook } from '@testing-library/react';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';
import { describe, expect, test, vi } from 'vitest';

describe('useComparisonTable', () => {
    test('tracks native scrolling and disables the next arrow at the exact end', () => {
        const viewport = document.createElement('div');
        viewport.innerHTML =
            '<table><thead><tr><td><div id="js-table-compare-head"><div></div><div></div></div></td></tr></thead></table>';
        Object.defineProperties(viewport, { clientWidth: { value: 800 }, scrollWidth: { value: 1200 } });
        const cells = viewport.querySelector('#js-table-compare-head')!.children;
        vi.spyOn(cells[0], 'getBoundingClientRect').mockReturnValue({ width: 192 } as DOMRect);
        vi.spyOn(cells[1], 'getBoundingClientRect').mockReturnValue({ width: 252 } as DOMRect);
        const { result } = renderHook(() => useComparisonTable(4));
        result.current.scrollRef.current = viewport;

        act(() => result.current.calcMaxMarginLeft());

        expect(result.current.tableFirstColumnWidth).toBe(192);
        expect(result.current.productColumnWidth).toBe(252);
        expect(result.current.isArrowLeftActive).toBe(false);
        expect(result.current.isArrowRightActive).toBe(true);

        viewport.scrollLeft = 400;
        act(() => result.current.calcMaxMarginLeft());

        expect(result.current.tableMarginLeft).toBe(400);
        expect(result.current.isArrowRightActive).toBe(false);
        expect(result.current.isArrowLeftActive).toBe(true);
    });
});
