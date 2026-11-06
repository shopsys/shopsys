import { BroadcastChannel } from 'node:worker_threads';
import { act, renderHook, waitFor } from '@testing-library/react';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { ProductListsProvider } from 'components/providers/ProductListsProvider';
import { TypeProductListTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { createUserSlice, defaultUserState, UserSlice } from 'store/slices/createUserSlice';
import { useSessionStore } from 'store/useSessionStore';
// biome-ignore lint/style/noRestrictedImports: Exercise the real query lifecycle with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { useProductListCount } from 'utils/productLists/useProductListCount';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { create, useStore } from 'zustand';
import { defaultTestDomainConfig } from '../../helpers/mockPublicConfig';

const userStore = create<UserSlice>()(createUserSlice);
const auth = vi.hoisted(() => ({ isLoggedIn: false }));

vi.mock('store/usePersistStore', () => ({
    usePersistStoreApi: () => userStore,
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
                productList:
                    itemsCount === null
                        ? null
                        : {
                              __typename: 'ProductList',
                              uuid: 'list-uuid',
                              itemsCount,
                              productUuids: Array.from({ length: itemsCount }, (_, i) => `product-${i}`),
                          },
            },
        }),
    );
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, fetchExchange],
        fetch,
        preferGetMethod: false,
    });
    const wrapper = ({ children }: { children: ReactNode }) => (
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <Provider value={client}>
                <ProductListsProvider>{children}</ProductListsProvider>
            </Provider>
        </DomainConfigProvider>
    );
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

describe.each([TypeProductListTypeEnum.Wishlist, TypeProductListTypeEnum.Comparison])(
    'useProductListCount %s',
    (productListType) => {
        beforeEach(() => {
            vi.stubGlobal('BroadcastChannel', BroadcastChannel);
            auth.isLoggedIn = false;
            userStore.setState({ ...defaultUserState, productListUuids: { [productListType]: 'list-uuid' } });
            useSessionStore.setState({ isProductListHydrated: true });
        });

        afterEach(() => vi.unstubAllGlobals());

        test('refreshes a visible window after another window changes the list', async () => {
            const { result, setItemsCount } = setup(productListType);
            await waitFor(() => expect(result.current).toBe(1));
            setItemsCount(2);

            const channel = new BroadcastChannel('refetchProductList_1');
            channel.postMessage({ tabId: 'other-window', domainId: 1, productListType });
            channel.close();

            await waitFor(() => expect(result.current).toBe(2));
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
    },
);
