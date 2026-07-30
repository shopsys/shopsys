import { act, renderHook } from '@testing-library/react';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';
import { afterEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/ui/useGetWindowSize', () => ({
    useGetWindowSize: () => ({ height: 800, width: 1280 }),
}));

vi.mock('utils/useComponentUpdate', () => ({
    useComponentUpdate: vi.fn(),
}));

const createRect = (width: number): DOMRect => ({
    bottom: 0,
    height: 0,
    left: 0,
    right: width,
    top: 0,
    width,
    x: 0,
    y: 0,
    toJSON: vi.fn(),
});

describe('useComparisonTable', () => {
    afterEach(() => {
        vi.restoreAllMocks();
        document.body.replaceChildren();
    });

    test('measures the first column width for the sticky comparison head', () => {
        document.body.innerHTML = `
            <div id="js-table-compare-wrap"></div>
            <table id="js-table-compare">
                <thead>
                    <tr id="js-table-compare-head"><td></td><th></th></tr>
                </thead>
            </table>
        `;
        const tableWrapper = document.getElementById('js-table-compare-wrap');
        const table = document.getElementById('js-table-compare');
        const firstColumn = document.querySelector('#js-table-compare-head > td');
        vi.spyOn(tableWrapper!, 'getBoundingClientRect').mockReturnValue(createRect(800));
        vi.spyOn(table!, 'getBoundingClientRect').mockReturnValue(createRect(1_236));
        vi.spyOn(firstColumn!, 'getBoundingClientRect').mockReturnValue(createRect(256));
        const { result } = renderHook(() => useComparisonTable(4));

        act(() => result.current.calcMaxMarginLeft());

        expect(result.current.tableFirstColumnWidth).toBe(256);
    });
});
