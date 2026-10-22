import { act, renderHook, waitFor } from '@testing-library/react';
import { TypeProductListTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { createUserSlice, defaultUserState, UserSlice } from 'store/slices/createUserSlice';
import { useSessionStore } from 'store/useSessionStore';
// biome-ignore lint/style/noRestrictedImports: Exercise the real query lifecycle with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { useProductListCount } from 'utils/productLists/useProductListCount';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { create, useStore } from 'zustand';

const userStore = create<UserSlice>()(createUserSlice);
const auth = vi.hoisted(() => ({ isLoggedIn: false }));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: UserSlice) => unknown) => useStore(userStore, selector),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => auth.isLoggedIn,
}));

const setup = (productListType: TypeProductListTypeEnum) => {
    let itemsCount: number | null = 1;
    const fetch = vi.fn(async () =>
        Response.json({
            data: {
                productList: itemsCount === null ? null : { uuid: 'list-uuid', itemsCount },
            },
        }),
    );
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [fetchExchange],
        fetch,
        preferGetMethod: false,
    });
    const wrapper = ({ children }: { children: ReactNode }) => <Provider value={client}>{children}</Provider>;
    const hook = renderHook(() => useProductListCount(productListType), { wrapper });

    return {
        ...hook,
        fetch,
        setItemsCount: (count: number | null) => {
            itemsCount = count;
        },
    };
};

const focusTab = () => {
    act(() => {
        document.dispatchEvent(new Event('visibilitychange'));
    });
};

describe.each([
    TypeProductListTypeEnum.Wishlist,
    TypeProductListTypeEnum.Comparison,
])('useProductListCount %s', (productListType) => {
    beforeEach(() => {
        auth.isLoggedIn = false;
        userStore.setState({ ...defaultUserState, productListUuids: { [productListType]: 'list-uuid' } });
        useSessionStore.setState({ isProductListHydrated: true });
    });

    test('clears the count when another tab removes the last product', async () => {
        const { result, fetch } = setup(productListType);
        await waitFor(() => expect(result.current).toBe(1));

        act(() => userStore.setState({ productListUuids: {} }));
        focusTab();

        expect(result.current).toBe(0);
        expect(fetch).toHaveBeenCalledTimes(1);
    });

    test('refreshes the count on tab focus when the list still exists', async () => {
        const { result, setItemsCount } = setup(productListType);
        await waitFor(() => expect(result.current).toBe(1));

        setItemsCount(2);
        focusTab();
        await waitFor(() => expect(result.current).toBe(2));

        setItemsCount(1);
        focusTab();
        await waitFor(() => expect(result.current).toBe(1));
    });

    test('loads the count when another tab creates a list', async () => {
        userStore.setState({ productListUuids: {} });
        const { result, fetch } = setup(productListType);
        expect(result.current).toBe(0);
        expect(fetch).not.toHaveBeenCalled();

        act(() => userStore.setState({ productListUuids: { [productListType]: 'list-uuid' } }));

        await waitFor(() => expect(result.current).toBe(1));
    });

    test('loads and refreshes the logged-in user list without a stored UUID', async () => {
        auth.isLoggedIn = true;
        userStore.setState({ productListUuids: {} });
        const { result, setItemsCount } = setup(productListType);
        await waitFor(() => expect(result.current).toBe(1));

        setItemsCount(null);
        focusTab();

        await waitFor(() => expect(result.current).toBe(0));
    });
});
