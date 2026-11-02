import { act, renderHook } from '@testing-library/react';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useProductList } from 'utils/productLists/useProductList';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';

const state = vi.hoisted(() => ({
    uuid: null as string | null,
    add: vi.fn(),
    remove: vi.fn(),
}));
vi.mock('graphql/requests/productLists/mutations/AddProductToListMutation.generated', () => ({
    useAddProductToListMutation: () => [null, state.add],
}));
vi.mock('graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated', () => ({
    useRemoveProductFromListMutation: () => [null, state.remove],
}));
vi.mock('graphql/requests/productLists/mutations/RemoveProductListMutation.generated', () => ({
    useRemoveProductListMutation: () => [null, vi.fn()],
}));
vi.mock('graphql/requests/productLists/queries/ProductListQuery.generated', () => ({
    ProductListQueryDocument: {},
    useProductListQuery: () => [{ data: undefined, fetching: false }, vi.fn()],
}));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (s: unknown) => unknown) => selector({ productListUuids: { COMPARISON: state.uuid } }),
}));
vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (s: unknown) => unknown) =>
        selector({ isProductListHydrated: true, updatePageLoadingState: vi.fn() }),
}));
vi.mock('utils/productLists/useUpdateProductListUuid', () => ({ useUpdateProductListUuid: () => vi.fn() }));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => false }));
vi.mock('urql', () => ({ useClient: () => ({ query: vi.fn() }) }));
vi.mock('utils/useRefetchOnTabFocus', () => ({ useRefetchOnTabFocus: () => undefined }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const pending: (() => void)[] = [];
const callbacks = {
    removeSuccess: vi.fn(),
    removeError: vi.fn(),
    addProductSuccess: vi.fn(),
    addProductError: vi.fn(),
    removeProductSuccess: vi.fn(),
    removeProductError: vi.fn(),
};

beforeEach(() => {
    vi.clearAllMocks();
    state.uuid = null;
    state.add.mockImplementation(
        () => new Promise((resolve) => pending.push(() => resolve({ data: { AddProductToList: null } }))),
    );
});
afterEach(async () => {
    await act(async () => {
        pending.splice(0).forEach((finish) => finish());
    });
});

test('keeps one creation in flight across hook instances and selector remounts', async () => {
    const first = renderHook(() => useProductList(TypeProductListTypeEnum.Comparison, callbacks));
    act(() => {
        first.result.current.toggleProductInList('first-variant');
    });
    first.unmount();
    const second = renderHook(() => useProductList(TypeProductListTypeEnum.Comparison, callbacks));
    let accepted: boolean | undefined;

    act(() => {
        accepted = second.result.current.toggleProductInList('second-variant');
    });

    expect(accepted).toBe(false);
    expect(state.add).toHaveBeenCalledTimes(1);

    await act(async () => {
        pending.splice(0).forEach((finish) => finish());
    });
    act(() => {
        expect(second.result.current.toggleProductInList('second-variant')).toBe(true);
    });
    expect(state.add).toHaveBeenCalledTimes(2);
});

test('shares duplicate-product locks for an existing list but allows independent products', () => {
    state.uuid = 'existing-list';
    const { result } = renderHook(() => [
        useProductList(TypeProductListTypeEnum.Comparison, callbacks),
        useProductList(TypeProductListTypeEnum.Comparison, callbacks),
    ]);
    act(() => {
        result.current[0].toggleProductInList('first-variant');
    });

    act(() => {
        expect(result.current[1].toggleProductInList('first-variant')).toBe(false);
        expect(result.current[1].toggleProductInList('second-variant')).toBe(true);
    });

    expect(state.add).toHaveBeenCalledTimes(2);
});
