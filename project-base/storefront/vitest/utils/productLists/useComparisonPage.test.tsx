import { act, renderHook } from '@testing-library/react';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import { ReactElement } from 'react';
import { useComparisonPage } from 'utils/productLists/comparison/useComparisonPage';
import { beforeEach, expect, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    callbacks: {} as {
        onProductRemoved: (uuid: string) => void;
        onProductAdded: (uuid: string, list: TypeProductListFragment) => void;
    },
    comparison: null as TypeProductListFragment | null,
    move: vi.fn(),
    toggle: vi.fn(),
    success: vi.fn(),
}));
vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: (callbacks: typeof mocks.callbacks) => {
        mocks.callbacks = callbacks;
        return {
            comparison: mocks.comparison,
            toggleProductInComparison: mocks.toggle,
            isProductInComparison: (uuid: string) => mocks.comparison?.products.some((p) => p.uuid === uuid),
        };
    },
}));
vi.mock('graphql/requests/productLists/mutations/MoveProductInListMutation.generated', () => ({
    useMoveProductInListMutation: () => [null, mocks.move],
}));
vi.mock('utils/toasts/showSuccessMessage', () => ({ showSuccessMessage: mocks.success }));
vi.mock('utils/toasts/showErrorMessage', () => ({ showErrorMessage: vi.fn() }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent', () => ({
    useGtmSliderProductListViewEvent: vi.fn(),
}));

const list = (uuids: string[], uuid = 'list') =>
    ({ uuid, products: uuids.map((uuid) => ({ uuid, fullName: uuid })) }) as TypeProductListFragment;

beforeEach(() => {
    vi.clearAllMocks();
    mocks.comparison = list(['a', 'b', 'c']);
    mocks.move.mockResolvedValue({ data: { MoveProductInList: list(['a', 'b', 'c']) } });
});

test.each([
    ['list', ['a', 'b', 'c'], 'b', ['b', 'a', 'c'], 'a'],
    ['new-list', ['b'], 'b', ['b'], null],
])('restores the removed position using the returned list %s', async (uuid, original, removed, added, afterUuid) => {
    mocks.comparison = list(original);
    renderHook(() => useComparisonPage());
    act(() => mocks.callbacks.onProductRemoved(removed));
    mocks.comparison = list(original.filter((item) => item !== removed));
    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;
    let pending: Promise<boolean>;
    act(() => {
        pending = action.props.onUndo();
    });
    await act(async () => {
        mocks.callbacks.onProductAdded(removed, list(added, uuid));
        expect(await pending).toBe(true);
    });
    if (afterUuid === null) {
        expect(mocks.move).not.toHaveBeenCalled();
    } else {
        expect(mocks.move).toHaveBeenCalledWith({
            input: {
                productListInput: { uuid, type: 'COMPARISON' },
                productUuid: removed,
                afterProductUuid: afterUuid,
            },
        });
    }
});

test('retries a failed order save without removing the restored product', async () => {
    const { rerender } = renderHook(() => useComparisonPage());
    act(() => mocks.callbacks.onProductRemoved('b'));
    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;
    mocks.comparison = list(['b', 'a', 'c']);
    rerender();
    mocks.move.mockResolvedValueOnce({ error: new Error('Unavailable') });

    await act(async () => {
        expect(await action.props.onUndo()).toBe(false);
        expect(await action.props.onUndo()).toBe(true);
    });

    expect(mocks.toggle).not.toHaveBeenCalled();
    expect(mocks.move).toHaveBeenLastCalledWith({
        input: { productListInput: { uuid: 'list', type: 'COMPARISON' }, productUuid: 'b', afterProductUuid: 'a' },
    });
});

test('keeps the restored product in its original position while saving the order', async () => {
    let finishSave!: (value: unknown) => void;
    mocks.move.mockImplementation(
        () =>
            new Promise((resolve) => {
                finishSave = resolve;
            }),
    );
    const { result, rerender } = renderHook(() => useComparisonPage());
    act(() => mocks.callbacks.onProductRemoved('b'));
    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;
    mocks.comparison = list(['a', 'c']);
    rerender();
    let pending!: Promise<boolean>;
    act(() => {
        pending = action.props.onUndo();
    });

    await act(async () => {
        mocks.comparison = list(['b', 'a', 'c']);
        mocks.callbacks.onProductAdded('b', mocks.comparison);
    });
    rerender();
    expect(result.current.products.map((product) => product.uuid)).toEqual(['a', 'b', 'c']);

    await act(async () => {
        mocks.comparison = list(['a', 'b', 'c']);
        finishSave({ data: { MoveProductInList: mocks.comparison } });
        expect(await pending).toBe(true);
    });
    expect(result.current.products.map((product) => product.uuid)).toEqual(['a', 'b', 'c']);
});
