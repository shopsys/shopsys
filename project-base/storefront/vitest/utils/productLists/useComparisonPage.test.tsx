import { act, renderHook } from '@testing-library/react';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import { TypeProductListSummaryFragment } from 'graphql/requests/productLists/fragments/ProductListSummaryFragment.generated';
import { ReactElement } from 'react';
import { useComparisonPage } from 'utils/productLists/comparison/useComparisonPage';
import { beforeEach, expect, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    callbacks: {} as {
        onProductRemoved: (uuid: string) => void;
        onProductRemoveError: (uuid: string) => void;
    },
    summary: undefined as TypeProductListSummaryFragment | undefined,
    comparison: null as (TypeProductListFragment & TypeProductListSummaryFragment) | null,
    move: vi.fn(),
    toggle: vi.fn(),
    success: vi.fn(),
}));
vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparisonDetails: () => ({
        comparison: mocks.comparison,
        isProductListFetching: false,
        hasProductListError: false,
    }),
    useComparison: (callbacks: typeof mocks.callbacks) => {
        mocks.callbacks = callbacks;
        return {
            comparison: mocks.comparison,
            comparisonSummary: mocks.summary ?? mocks.comparison,
            moveProductInComparison: (productUuid: string, afterProductUuid: string | null, uuid: string) =>
                mocks
                    .move({
                        input: { productListInput: { uuid, type: 'COMPARISON' }, productUuid, afterProductUuid },
                    })
                    .then(
                        (result: { error?: unknown; data?: { MoveProductInList?: TypeProductListFragment } }) =>
                            !result.error && !!result.data?.MoveProductInList,
                    ),
            toggleProductInComparison: mocks.toggle,
            isProductInComparison: (uuid: string) => (mocks.summary ?? mocks.comparison)?.productUuids.includes(uuid),
        };
    },
}));
vi.mock('utils/toasts/showSuccessMessage', () => ({ showSuccessMessage: mocks.success }));
vi.mock('utils/toasts/showErrorMessage', () => ({ showErrorMessage: vi.fn() }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent', () => ({
    useGtmSliderProductListViewEvent: vi.fn(),
}));

const list = (uuids: string[], uuid = 'list') =>
    ({
        __typename: 'ProductList',
        uuid,
        itemsCount: uuids.length,
        productUuids: uuids,
        products: uuids.map((uuid) => ({ uuid, fullName: uuid })),
    }) as TypeProductListFragment & TypeProductListSummaryFragment;

const deferAdd = () => {
    let finish!: (list: TypeProductListSummaryFragment | undefined) => void;
    mocks.toggle.mockReturnValueOnce(
        new Promise<TypeProductListSummaryFragment | undefined>((resolve) => {
            finish = resolve;
        }),
    );
    return (list: TypeProductListSummaryFragment | undefined) => finish(list);
};

beforeEach(() => {
    vi.clearAllMocks();
    mocks.summary = undefined;
    mocks.comparison = list(['a', 'b', 'c']);
    mocks.move.mockResolvedValue({ data: { MoveProductInList: list(['a', 'b', 'c']) } });
});

test.each([
    ['list', ['a', 'b', 'c'], 'b', ['b', 'a', 'c'], 'a'],
    ['new-list', ['b'], 'b', ['b'], null],
])('restores the removed position using the returned list %s', async (uuid, original, removed, added, afterUuid) => {
    const finishAdd = deferAdd();
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
        finishAdd(list(added, uuid));
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
    const finishAdd = deferAdd();
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
        finishAdd(mocks.comparison);
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

test('uses the displayed position when a product is removed before the reordered list reaches the cache', async () => {
    mocks.comparison = list(['b', 'a']);
    const { result } = renderHook(() => useComparisonPage());

    act(() =>
        result.current.handleRemove({ uuid: 'b', fullName: 'b' } as TypeProductListFragment['products'][number], 1),
    );
    act(() => mocks.callbacks.onProductRemoved('b'));
    mocks.comparison = list(['a']);
    const finishAdd = deferAdd();

    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;
    let pending!: Promise<boolean>;
    act(() => {
        pending = action.props.onUndo();
    });
    await act(async () => {
        finishAdd(list(['b', 'a']));
        await pending;
    });

    expect(mocks.move).toHaveBeenCalledWith({
        input: { productListInput: { uuid: 'list', type: 'COMPARISON' }, productUuid: 'b', afterProductUuid: 'a' },
    });
});

test('Undo uses current membership order before detail cards finish refreshing', async () => {
    const hook = renderHook(() => useComparisonPage());
    act(() => mocks.callbacks.onProductRemoved('b'));
    mocks.comparison = list(['c', 'a']);
    mocks.summary = { __typename: 'ProductList', uuid: 'list', itemsCount: 3, productUuids: ['b', 'a', 'c'] };
    hook.rerender();
    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;

    await act(async () => {
        expect(await action.props.onUndo()).toBe(true);
    });

    expect(mocks.toggle).not.toHaveBeenCalled();
    expect(mocks.move).toHaveBeenCalledWith({
        input: { productListInput: { uuid: 'list', type: 'COMPARISON' }, productUuid: 'b', afterProductUuid: 'a' },
    });
});

test.each(['rejected', 'cancelled'])('settles Undo when adding is %s', async (outcome) => {
    const { result, rerender } = renderHook(() => useComparisonPage());
    act(() => mocks.callbacks.onProductRemoved('b'));
    mocks.comparison = list(['a', 'c']);
    rerender();
    const action = mocks.success.mock.calls[0][1].action as ReactElement<{ onUndo: () => Promise<boolean> }>;
    const finishAdd = outcome === 'cancelled' ? deferAdd() : undefined;
    if (!finishAdd) mocks.toggle.mockReturnValueOnce(null);
    let pending!: Promise<boolean>;
    act(() => {
        pending = action.props.onUndo();
    });
    await act(async () => {
        finishAdd?.(undefined);
        expect(await pending).toBe(false);
    });
    expect(mocks.move).not.toHaveBeenCalled();
    expect(result.current.products.map((product) => product.uuid)).toEqual(['a', 'c']);
});
