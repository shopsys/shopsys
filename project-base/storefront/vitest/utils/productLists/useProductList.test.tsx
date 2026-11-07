import { BroadcastChannel } from 'node:worker_threads';
import { act, renderHook, waitFor } from '@testing-library/react';
import { TertiaryLoaders } from 'components/Pages/App/TertiaryLoaders';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { ProductListsProvider } from 'components/providers/ProductListsProvider';
import { ProductListSummaryQueryDocument } from 'graphql/requests/productLists/queries/ProductListSummaryQuery.generated';
import {
    TypeWishlistPageQuery,
    TypeWishlistPageQueryVariables,
    WishlistPageQueryDocument,
} from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { createUserSlice, defaultUserState, UserSlice } from 'store/slices/createUserSlice';
import { useSessionStore } from 'store/useSessionStore';
// biome-ignore lint/style/noRestrictedImports: Exercise real URQL queries and cache with a controlled GraphQL transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';
import { useComparisonDetails } from 'utils/productLists/comparison/useComparison';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListCount } from 'utils/productLists/useProductListCount';
import { useWishlistPage } from 'utils/productLists/wishlist/useWishlist';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { create, useStore } from 'zustand';
import { defaultTestDomainConfig } from '../../helpers/mockPublicConfig';
import { createProduct } from '../../helpers/productFixture';

const userStore = create<UserSlice>()(createUserSlice);
const auth = vi.hoisted(() => ({ isLoggedIn: false }));
const reloadPage = vi.hoisted(() => vi.fn());
vi.mock('utils/auth/performAuthHardNavigation', () => ({ performAuthHardNavigation: vi.fn() }));
vi.mock('next/router', () => ({ useRouter: () => ({ reload: reloadPage }) }));
vi.mock('store/usePersistStore', () => ({
    usePersistStoreApi: () => userStore,
    usePersistStore: (selector: (state: UserSlice) => unknown) => useStore(userStore, selector),
}));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => auth.isLoggedIn }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const setup = (
    productListType: TypeProductListTypeEnum,
    withSessionLoader = false,
    extraConsumers = 0,
    initialQueryError = false,
    strictMode = false,
    mode: 'summary' | 'full' | 'page' = 'summary',
    initialPage = 1,
) => {
    let queryError = initialQueryError;
    const pageInputs = { page: initialPage, loadMore: 0 };
    let products = ['first-product', 'second-product', 'third-product'];
    let mutationError: string | null = null;
    let countOffset = 0;
    let listUuid = 'list-uuid';
    let mutationGate: Promise<void> | null = null;
    let queryGate: Promise<void> | null = null;
    const list = () =>
        products.length
            ? {
                  __typename: 'ProductList',
                  uuid: listUuid,
                  itemsCount: products.length + countOffset,
                  productUuids: products,
                  products: products.map((id) => ({ ...createProduct(id), imagesCount: 0, parameters: [] })),
              }
            : null;
    const fetch = vi.fn(async (_input: unknown, options: RequestInit | undefined) => {
        const { query, variables } = JSON.parse(options?.body as string);
        if (query.includes('mutation')) {
            const field = query.includes('RemoveProductListMutation') ? 'RemoveProductList' : 'RemoveProductFromList';
            if (mutationGate) await mutationGate;
            if (mutationError) {
                return Response.json({
                    data: { [field]: null },
                    errors: [
                        {
                            message: 'Product not in list',
                            path: [field],
                            extensions: { userCode: mutationError },
                        },
                    ],
                });
            }
            if (query.includes('MoveProductInListMutation')) {
                products = products.filter((id) => id !== variables.input.productUuid);
                products.splice(
                    variables.input.afterProductUuid ? products.indexOf(variables.input.afterProductUuid) + 1 : 0,
                    0,
                    variables.input.productUuid,
                );
                return Response.json({ data: { MoveProductInList: list() } });
            }
            if (query.includes('RemoveProductListMutation')) {
                products = [];
                return Response.json({ data: { RemoveProductList: null } });
            }
            if (query.includes('RemoveProductFromListMutation')) {
                products = products.filter((id) => id !== variables.input.productUuid);
                return Response.json({ data: { RemoveProductFromList: list() } });
            }
            products = [...products, variables.input.productUuid];
            return Response.json({ data: { AddProductToList: list() } });
        }
        if (queryError) throw new TypeError('Network unavailable');
        const current = variables.input.type === productListType ? list() : null;
        const value =
            current && query.includes('query WishlistPageQuery')
                ? { ...current, products: current.products.slice(variables.offset, variables.offset + variables.limit) }
                : current;
        if (queryGate) {
            const gate = queryGate;
            queryGate = null;
            await gate;
        }
        return Response.json({ data: { productList: value } });
    });
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, fetchExchange],
        fetch,
        preferGetMethod: false,
    });
    const callbacks = {
        removeSuccess: vi.fn(),
        removeError: vi.fn(),
        addProductSuccess: vi.fn(),
        addProductError: vi.fn(),
        removeProductSuccess: vi.fn(),
        removeProductError: vi.fn(),
    };
    const AdditionalConsumer = () => {
        useProductList(productListType, callbacks);
        useProductListCount(productListType);
        return null;
    };
    const wrapper = ({ children }: { children: ReactNode }) => (
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <Provider value={client}>
                <ProductListsProvider>
                    {withSessionLoader && <TertiaryLoaders />}
                    {Array.from({ length: extraConsumers }, (_, index) => (
                        <AdditionalConsumer key={index} />
                    ))}
                    {children}
                </ProductListsProvider>
            </Provider>
        </DomainConfigProvider>
    );
    const useLists = () => ({
        list: useProductList(productListType, callbacks),
        count: useProductListCount(productListType),
        login: useHandleActionsAfterLogin(),
        page: undefined as ReturnType<typeof useWishlistPage> | undefined,
        comparison: undefined as ReturnType<typeof useComparisonDetails> | undefined,
    });
    const usePagedLists = () => ({ ...useLists(), page: useWishlistPage(pageInputs.page, pageInputs.loadMore) });
    const useDetailedLists = () => ({ ...useLists(), comparison: useComparisonDetails() });
    const hook = renderHook(mode === 'page' ? usePagedLists : mode === 'full' ? useDetailedLists : useLists, {
        wrapper,
        reactStrictMode: strictMode,
    });
    return {
        ...hook,
        callbacks,
        client,
        fetch,
        setPage: (page: number, loadMore = 0) => {
            pageInputs.page = page;
            pageInputs.loadMore = loadMore;
            hook.rerender();
        },
        setQueryError: (error: boolean) => {
            queryError = error;
        },
        setListUuid: (uuid: string) => {
            listUuid = uuid;
        },
        deferMutation: (gate: Promise<void>) => {
            mutationGate = gate;
        },
        deferNextQuery: (gate: Promise<void>) => {
            queryGate = gate;
        },
        setProducts: (ids: string[]) => {
            products = ids;
        },
        setCountOffset: (offset: number) => {
            countOffset = offset;
        },
        setMutationError: (error: string) => {
            mutationError = error;
        },
    };
};
const notifyOtherWindow = (productListType: TypeProductListTypeEnum) => {
    const channel = new BroadcastChannel('refetchProductList_1');
    channel.postMessage({ domainId: 1, tabId: 'other-window', productListType });
    channel.close();
};

describe.each([TypeProductListTypeEnum.Wishlist, TypeProductListTypeEnum.Comparison])('useProductList %s', (type) => {
    beforeEach(() => {
        vi.stubGlobal('BroadcastChannel', BroadcastChannel);
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        auth.isLoggedIn = false;
        userStore.setState({ ...defaultUserState, productListUuids: { [type]: 'list-uuid' } });
        useSessionStore.setState({ isProductListHydrated: true });
    });
    afterEach(() => vi.unstubAllGlobals());

    test('settles a failed initial query without clearing its UUID and recovers when shown', async () => {
        const { result, fetch, setQueryError } = setup(type, false, 0, true);
        await waitFor(() => expect(fetch).toHaveBeenCalledOnce());
        await waitFor(() => expect(result.current.list.isProductListFetching).toBe(false));
        expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
        expect(result.current.list.productListSummary).toBeUndefined();
        expect(result.current.list.hasProductListError).toBe(true);
        setQueryError(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.count).toBe(3));
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    test('preserves confirmed content after a refresh query fails', async () => {
        const { result, fetch, setQueryError } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setQueryError(true);
        notifyOtherWindow(type);
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(2));
        await waitFor(() => expect(result.current.list.hasProductListError).toBe(true));
        expect(result.current.list.isProductListFetching).toBe(false);
        expect(result.current.count).toBe(3);
        expect(result.current.list.isProductInList('first-product')).toBe(true);
        expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
    });

    test('suspends the originating page before login resets list identity', async () => {
        auth.isLoggedIn = true;
        const { result, fetch } = setup(type, true);
        await waitFor(() => expect(result.current.count).toBe(3));
        const before = fetch.mock.calls.length;
        act(() => result.current.login(false, '/'));
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(userStore.getState().productListUuids).toEqual({});
        expect(fetch).toHaveBeenCalledTimes(before);
    });

    test('keeps one shared read and handles updates under StrictMode', async () => {
        const { result, fetch, setProducts } = setup(type, false, 3, false, true);
        await waitFor(() => expect(result.current.count).toBe(3));
        expect(fetch).toHaveBeenCalledOnce();
        setProducts(['third-product']);
        notifyOtherWindow(type);
        await waitFor(() => expect(result.current.count).toBe(1));
        expect(result.current.list.isProductInList('third-product')).toBe(true);
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    test('membership and mutation recovery never request full product cards', async () => {
        const { result, fetch, setMutationError } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setMutationError(`${type}-product-not-in-list`);
        act(() => {
            result.current.list.toggleProductInList('first-product');
        });
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(3));
        for (const [, options] of fetch.mock.calls) {
            const { query } = JSON.parse(options?.body as string);
            expect(query).not.toMatch(/products\s*[{(]/);
            expect(query).not.toContain('ListedProductFragment');
            expect(query).not.toContain('ParameterFragment');
        }
    });

    test('does not load list or count when initially hidden', async () => {
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        const { fetch } = setup(type);
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).not.toHaveBeenCalled();
    });

    test('defers hidden changes and validates once when visible', async () => {
        const { result, fetch, setProducts } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        const before = fetch.mock.calls.length;
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        setProducts(['second-product', 'third-product']);
        notifyOtherWindow(type);
        notifyOtherWindow(type);
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).toHaveBeenCalledTimes(before);
        expect(result.current.count).toBe(3);

        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.count).toBe(2));
        await waitFor(() => expect(result.current.list.isProductListFetching).toBe(false));
        expect(fetch).toHaveBeenCalledTimes(before + 1);
    });

    test('uses the authoritative count rather than calculating it from cached cards', async () => {
        const { result, setCountOffset } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setCountOffset(4);
        act(() => {
            result.current.list.toggleProductInList('fourth-product');
        });
        await waitFor(() => expect(result.current.count).toBe(8));
        expect(result.current.list.isProductInList('fourth-product')).toBe(true);
    });

    test('does not fetch after a stored UUID changes in a hidden page', async () => {
        const { result, fetch, setProducts, setListUuid } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        const before = fetch.mock.calls.length;
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        setProducts(['second-product', 'third-product']);
        setListUuid('replacement-list');
        act(() => userStore.setState({ productListUuids: { [type]: 'replacement-list' } }));
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).toHaveBeenCalledTimes(before);
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.count).toBe(2));
        expect(userStore.getState().productListUuids[type]).toBe('replacement-list');
        expect(fetch).toHaveBeenCalledTimes(before + 1);
    });

    test('preserves confirmed data after remove all fails', async () => {
        const { result, callbacks, setMutationError } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setMutationError('unexpected-error');
        await act(async () => {
            await result.current.list.removeList();
        });
        expect(callbacks.removeError).toHaveBeenCalledOnce();
        expect(result.current.count).toBe(3);
        expect(result.current.list.isProductInList('first-product')).toBe(true);
        expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
    });

    test('clears cached account membership when the list was already deleted elsewhere', async () => {
        auth.isLoggedIn = true;
        userStore.setState({ productListUuids: {} });
        const { result, callbacks, setProducts, setMutationError } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        await waitFor(() => expect(userStore.getState().productListUuids[type]).toBe('list-uuid'));
        setProducts([]);
        setMutationError(`${type}-product-list-not-found`);

        await act(async () => {
            await result.current.list.removeList();
        });

        await waitFor(() => expect(result.current.count).toBe(0));
        expect(result.current.list.isProductInList('first-product')).toBe(false);
        expect(callbacks.removeSuccess).toHaveBeenCalledOnce();
        expect(callbacks.removeError).not.toHaveBeenCalled();
        expect(userStore.getState().productListUuids[type]).toBeUndefined();
    });

    test('waits for visibility before recovering a mutation completed in the background', async () => {
        const { result, fetch, callbacks, setProducts, setMutationError, deferMutation } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        let finish!: () => void;
        deferMutation(
            new Promise<void>((resolve) => {
                finish = resolve;
            }),
        );
        setProducts(['second-product', 'third-product']);
        setMutationError(`${type}-product-not-in-list`);
        act(() => {
            result.current.list.toggleProductInList('first-product');
        });
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(2));
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await act(async () => {
            finish();
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).toHaveBeenCalledTimes(2);
        expect(callbacks.removeProductSuccess).not.toHaveBeenCalled();
        expect(result.current.count).toBe(3);
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.count).toBe(2));
        await waitFor(() => expect(callbacks.removeProductSuccess).toHaveBeenCalledOnce());
        expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
    });

    test('does not restore a deleted UUID from a late response', async () => {
        auth.isLoggedIn = true;
        const { result, fetch, deferNextQuery } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        const before = fetch.mock.calls.length;
        let finish!: () => void;
        deferNextQuery(
            new Promise<void>((resolve) => {
                finish = resolve;
            }),
        );
        notifyOtherWindow(type);
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(before + 1));
        await act(async () => {
            await result.current.list.removeList();
        });
        expect(result.current.count).toBe(0);
        await act(async () => {
            finish();
        });
        expect(userStore.getState().productListUuids[type]).toBeUndefined();
        expect(result.current.count).toBe(0);
        expect(result.current.list.isProductInList('first-product')).toBe(false);
    });

    test('waits for the server before adopting an authenticated UUID from an old cache entry', async () => {
        auth.isLoggedIn = true;
        const { result, client, fetch, setListUuid, deferNextQuery } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setListUuid('old-cached-list');
        await act(async () => {
            await client
                .query(
                    ProductListSummaryQueryDocument,
                    { input: { type, uuid: null } },
                    { requestPolicy: 'network-only' },
                )
                .toPromise();
        });
        setListUuid('list-uuid');
        let release!: () => void;
        deferNextQuery(
            new Promise<void>((resolve) => {
                release = resolve;
            }),
        );
        const before = fetch.mock.calls.length;
        act(() => userStore.getState().updateProductListUuids({}));
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(before + 1));
        expect(userStore.getState().productListUuids[type]).toBeUndefined();
        await act(async () => {
            release();
        });
        await waitFor(() => expect(userStore.getState().productListUuids[type]).toBe('list-uuid'));
        expect(result.current.count).toBe(3);
        expect(fetch.mock.calls.length).toBeLessThanOrEqual(before + 2);
    });

    test('notifies other windows after reordering without changing membership or count', async () => {
        const { result } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        const channel = new BroadcastChannel('refetchProductList_1');
        const onMessage = vi.fn();
        channel.onmessage = onMessage;
        try {
            await act(async () => {
                expect(await result.current.list.moveProduct('third-product', null)).toBe(true);
            });
            await waitFor(() => expect(onMessage).toHaveBeenCalled());
            expect(result.current.list.productListSummary?.productUuids).toEqual([
                'third-product',
                'first-product',
                'second-product',
            ]);
            expect(result.current.count).toBe(3);
        } finally {
            channel.close();
        }
    });

    test('invalidates the old session immediately but reloads hidden pages only when shown', async () => {
        const { result, fetch } = setup(type, true);
        await waitFor(() => expect(result.current.count).toBe(3));
        const before = fetch.mock.calls.length;
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        const channel = new BroadcastChannel('reloadPage_1');
        channel.postMessage({ domainId: 1, tabId: 'other-window' });
        channel.close();
        await waitFor(() => expect(result.current.count).toBe(0));
        expect(reloadPage).not.toHaveBeenCalled();
        act(() => userStore.getState().updateProductListUuids({}));
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(reloadPage).toHaveBeenCalledOnce());
        expect(fetch).toHaveBeenCalledTimes(before);
    });

    test('coalesces invalidations received during a request and keeps the latest response', async () => {
        const { result, fetch, setProducts, deferNextQuery } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        let finish!: () => void;
        setProducts(['second-product', 'third-product']);
        deferNextQuery(
            new Promise<void>((resolve) => {
                finish = resolve;
            }),
        );
        notifyOtherWindow(type);
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(2));
        setProducts(['third-product']);
        notifyOtherWindow(type);
        notifyOtherWindow(type);
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
            finish();
        });
        await waitFor(() => expect(result.current.count).toBe(1));
        expect(result.current.list.isProductInList('second-product')).toBe(false);
        expect(fetch).toHaveBeenCalledTimes(3);
    });

    test('accepts two product additions while preserving their order and final state', async () => {
        const { result, fetch, deferMutation } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        let finish!: () => void;
        deferMutation(
            new Promise<void>((resolve) => {
                finish = resolve;
            }),
        );
        act(() => {
            expect(result.current.list.toggleProductInList('fourth-product')).toBeInstanceOf(Promise);
            expect(result.current.list.toggleProductInList('fifth-product')).toBeInstanceOf(Promise);
        });
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).toHaveBeenCalledTimes(2);
        await act(async () => {
            finish();
        });
        await waitFor(() => expect(result.current.count).toBe(5));
        expect(result.current.list.isProductInList('fourth-product')).toBe(true);
        expect(result.current.list.isProductInList('fifth-product')).toBe(true);
    });

    test('rejects a product change while clearing instead of leaving an unfinished operation', async () => {
        const { result, fetch, deferMutation } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        let release!: () => void;
        deferMutation(
            new Promise<void>((resolve) => {
                release = resolve;
            }),
        );
        let clearing!: Promise<void>;
        act(() => {
            clearing = result.current.list.removeList();
        });
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(2));
        act(() => {
            expect(result.current.list.toggleProductInList('fourth-product')).toBeNull();
        });
        await act(async () => {
            release();
            await clearing;
        });
        expect(result.current.count).toBe(0);
    });

    test('settles in-flight and queued additions after another list is selected', async () => {
        const { result, fetch, callbacks, deferMutation, setListUuid } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        let release!: () => void;
        deferMutation(
            new Promise<void>((resolve) => {
                release = resolve;
            }),
        );
        let first!: ReturnType<typeof result.current.list.toggleProductInList>;
        let queued!: ReturnType<typeof result.current.list.toggleProductInList>;
        act(() => {
            first = result.current.list.toggleProductInList('fourth-product');
            queued = result.current.list.toggleProductInList('fifth-product');
        });
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(2));
        setListUuid('another-list');
        act(() => userStore.getState().updateProductListUuids({ [type]: 'another-list' }));
        await act(async () => {
            release();
            expect(await first).toBeUndefined();
            expect(await queued).toBeUndefined();
        });
        expect(callbacks.addProductSuccess).not.toHaveBeenCalled();
        expect(callbacks.addProductError).not.toHaveBeenCalled();
        expect(userStore.getState().productListUuids[type]).toBe('another-list');
        const mutations = fetch.mock.calls.filter(([, options]) =>
            JSON.parse(options?.body as string).query.includes('mutation'),
        );
        expect(mutations).toHaveLength(1);
    });

    test('shares one metadata query across header counts and many product controls', async () => {
        const { result, fetch } = setup(type, false, 20);
        await waitFor(() => expect(result.current.count).toBe(3));
        expect(fetch).toHaveBeenCalledOnce();
        act(() => {
            result.current.list.toggleProductInList('fourth-product');
        });
        await waitFor(() => expect(result.current.count).toBe(4));
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    test('creates one guest list for two queued additions', async () => {
        userStore.setState({ productListUuids: {} });
        const { result, fetch, setProducts } = setup(type);
        setProducts([]);
        act(() => {
            expect(result.current.list.toggleProductInList('first-new-product')).toBeInstanceOf(Promise);
            expect(result.current.list.toggleProductInList('second-new-product')).toBeInstanceOf(Promise);
        });
        await waitFor(() => expect(result.current.count).toBe(2));
        expect(result.current.list.isProductInList('first-new-product')).toBe(true);
        expect(result.current.list.isProductInList('second-new-product')).toBe(true);
        expect(fetch).toHaveBeenCalledTimes(2);
        const secondInput = JSON.parse(fetch.mock.calls[1][1]?.body as string).variables.input.productListInput;
        expect(secondInput.uuid).toBe('list-uuid');
    });

    test('refreshes count, products and membership in a visible window', async () => {
        const { result, setProducts } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(['second-product', 'third-product']);
        notifyOtherWindow(type);
        await waitFor(() => expect(result.current.count).toBe(2));
        expect(result.current.list.productListSummary?.productUuids).toEqual(['second-product', 'third-product']);
        expect(result.current.list.isProductInList('first-product')).toBe(false);
        expect(result.current.list.isProductInList('second-product')).toBe(true);
    });

    test.each([false, true])('clears a visible window after remove all, logged in: %s', async (loggedIn) => {
        auth.isLoggedIn = loggedIn;
        if (loggedIn) {
            userStore.setState({ productListUuids: {} });
        }
        const { result, setProducts } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        await waitFor(() => expect(userStore.getState().productListUuids[type]).toBe('list-uuid'));
        setProducts([]);
        notifyOtherWindow(type);
        await waitFor(() => expect(result.current.count).toBe(0));
        await waitFor(() => expect(result.current.list.productListSummary).toBeFalsy());
        expect(result.current.list.isProductInList('first-product')).toBe(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.list.isProductListFetching).toBe(false));
        expect(userStore.getState().productListUuids[type]).toBeUndefined();
        expect(result.current.count).toBe(0);
    });

    test.each(['add', 'remove', 'remove all'])('notifies other windows after %s', async (action) => {
        const { result } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        const channel = new BroadcastChannel('refetchProductList_1');
        const onMessage = vi.fn();
        channel.onmessage = onMessage;
        try {
            await act(async () => {
                if (action === 'remove all') {
                    await result.current.list.removeList();
                } else {
                    result.current.list.toggleProductInList(action === 'add' ? 'fourth-product' : 'first-product');
                }
            });
            await waitFor(() => expect(onMessage).toHaveBeenCalled());
            expect(onMessage.mock.calls[0][0].data).toMatchObject({ domainId: 1, productListType: type });
            await waitFor(() => expect(result.current.count).toBe(action === 'add' ? 4 : action === 'remove' ? 2 : 0));
        } finally {
            channel.close();
        }
    });

    test('keeps remaining products when removing an already removed product in a stale window', async () => {
        const { result, setProducts, setMutationError, callbacks } = setup(type);
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(['second-product', 'third-product']);
        setMutationError(`${type}-product-not-in-list`);
        act(() => {
            result.current.list.toggleProductInList('first-product');
        });
        await waitFor(() => expect(callbacks.removeProductSuccess).toHaveBeenCalledWith('first-product'));
        expect(result.current.list.productListSummary).toEqual(
            expect.objectContaining({
                uuid: 'list-uuid',
                itemsCount: 2,
                productUuids: ['second-product', 'third-product'],
            }),
        );
        expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
        expect(result.current.list.productListSummary?.productUuids).toEqual(['second-product', 'third-product']);
        expect(result.current.count).toBe(2);
    });
});

describe('wishlist server ranges', () => {
    beforeEach(() => {
        vi.stubGlobal('BroadcastChannel', BroadcastChannel);
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        auth.isLoggedIn = false;
        userStore.setState({ ...defaultUserState, productListUuids: { WISHLIST: 'list-uuid' } });
        useSessionStore.setState({ isProductListHydrated: true });
    });
    afterEach(() => vi.unstubAllGlobals());

    test.each([
        [TypeProductListTypeEnum.Wishlist, 'page'],
        [TypeProductListTypeEnum.Comparison, 'full'],
    ] as const)('recovers %s metadata and cards once and notifies other windows', async (type, mode) => {
        userStore.setState({ productListUuids: { [type]: 'list-uuid' } });
        const { result, fetch, callbacks, setProducts, setMutationError } = setup(type, false, 0, false, false, mode);
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(['second-product', 'third-product']);
        setMutationError(`${type}-product-not-in-list`);
        const channel = new BroadcastChannel('refetchProductList_1');
        const onMessage = vi.fn();
        channel.onmessage = onMessage;

        try {
            act(() => {
                result.current.list.toggleProductInList('first-product');
            });
            await waitFor(() => expect(callbacks.removeProductSuccess).toHaveBeenCalledOnce());
            await waitFor(() => expect(result.current.list.isProductListFetching).toBe(false));
            await waitFor(() => expect(onMessage).toHaveBeenCalledOnce());
            const list = mode === 'page' ? result.current.page?.wishlist : result.current.comparison?.comparison;

            expect(list?.products.map((product) => product.uuid)).toEqual(['second-product', 'third-product']);
            expect(result.current.count).toBe(2);
            expect(userStore.getState().productListUuids[type]).toBe('list-uuid');
            expect(fetch).toHaveBeenCalledTimes(5);
            const operations = fetch.mock.calls.map(([, options]) => JSON.parse(options?.body as string).query);
            expect(operations.filter((query: string) => query.includes('query ProductListSummaryQuery'))).toHaveLength(
                2,
            );
            expect(
                operations.filter((query: string) =>
                    query.includes(mode === 'page' ? 'query WishlistPageQuery' : 'query ProductListQuery('),
                ),
            ).toHaveLength(2);
            expect(onMessage.mock.calls[0][0].data).toMatchObject({ productListType: type, uuid: 'list-uuid' });
        } finally {
            channel.close();
        }
    });

    test('requests only the selected range while keeping complete membership and total', async () => {
        const { result, fetch, setProducts, setPage } = setup(
            TypeProductListTypeEnum.Wishlist,
            false,
            0,
            false,
            false,
            'page',
        );
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 65 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(65));
        expect(result.current.page?.wishlist?.products).toHaveLength(30);
        expect(result.current.list.isProductInList('product-64')).toBe(true);
        act(() => setPage(3));
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-60'));
        expect(result.current.page?.wishlist?.products).toHaveLength(5);
        const last = JSON.parse(fetch.mock.calls.at(-1)?.[1]?.body as string);
        expect(last.variables).toEqual({ input: { type: 'WISHLIST', uuid: 'list-uuid' }, offset: 60, limit: 30 });
        expect(last.query).not.toContain('ParameterFragment');
    });

    test('summary mutations preserve two independently cached product ranges', async () => {
        const { result, client, setProducts } = setup(TypeProductListTypeEnum.Wishlist, false, 0, false, false, 'page');
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 65 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(65));
        const variables = {
            input: { type: TypeProductListTypeEnum.Wishlist, uuid: 'list-uuid' },
            offset: 30,
            limit: 30,
        };
        await act(async () => {
            await client
                .query<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>(WishlistPageQueryDocument, variables, {
                    requestPolicy: 'network-only',
                })
                .toPromise();
        });
        act(() => {
            result.current.list.toggleProductInList('product-64');
        });
        await waitFor(() => expect(result.current.count).toBe(64));
        const cached = await client
            .query<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>(WishlistPageQueryDocument, variables, {
                requestPolicy: 'cache-only',
            })
            .toPromise();
        expect(cached.data?.productList?.products.map((p) => p.uuid)).toEqual(
            Array.from({ length: 30 }, (_, i) => `product-${30 + i}`),
        );
        expect(result.current.count).toBe(64);
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-0'));
        expect(result.current.page?.wishlist?.products).toHaveLength(30);
    });

    test('clamps after the last item on page three is removed and supports ranges beyond 100', async () => {
        const { result, setProducts, setPage } = setup(
            TypeProductListTypeEnum.Wishlist,
            false,
            0,
            false,
            false,
            'page',
        );
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 61 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(61));
        act(() => setPage(3));
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-60'));
        act(() => {
            result.current.list.toggleProductInList('product-60');
        });
        await waitFor(() => expect(result.current.page?.page).toBe(2));
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-30'));
        expect(result.current.count).toBe(60);
        setProducts(Array.from({ length: 150 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(150));
        act(() => setPage(1, 3));
        await waitFor(() => expect(result.current.page?.wishlist?.products).toHaveLength(120));
        expect(result.current.count).toBe(150);
    });

    test('defers a range change and remote changes while the wishlist page is hidden', async () => {
        const { result, fetch, setProducts, setPage } = setup(
            TypeProductListTypeEnum.Wishlist,
            false,
            0,
            false,
            false,
            'page',
        );
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 65 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(65));
        const before = fetch.mock.calls.length;
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(true);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        act(() => setPage(2));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 20));
        });
        expect(fetch).toHaveBeenCalledTimes(before);
        vi.spyOn(document, 'hidden', 'get').mockReturnValue(false);
        act(() => document.dispatchEvent(new Event('visibilitychange')));
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-30'));
        expect(fetch).toHaveBeenCalledTimes(before + 2);
    });

    test('a delayed previous range cannot replace the selected page', async () => {
        const { result, fetch, setProducts, setPage, deferNextQuery } = setup(
            TypeProductListTypeEnum.Wishlist,
            false,
            0,
            false,
            false,
            'page',
        );
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 65 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.count).toBe(65));
        let release!: () => void;
        deferNextQuery(
            new Promise<void>((resolve) => {
                release = resolve;
            }),
        );
        const before = fetch.mock.calls.length;
        act(() => setPage(2));
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(before + 1));
        act(() => setPage(3));
        await act(async () => {
            release();
        });
        await waitFor(() => expect(result.current.page?.wishlist?.products[0]?.uuid).toBe('product-60'));
        expect(result.current.page?.wishlist?.products).toHaveLength(5);
    });

    test('a delayed card response cannot restore a product removed in the meantime', async () => {
        const { result, fetch, setProducts, setPage, deferNextQuery } = setup(
            TypeProductListTypeEnum.Wishlist,
            false,
            0,
            false,
            false,
            'page',
        );
        await waitFor(() => expect(result.current.count).toBe(3));
        setProducts(Array.from({ length: 65 }, (_, i) => `product-${i}`));
        notifyOtherWindow(TypeProductListTypeEnum.Wishlist);
        await waitFor(() => expect(result.current.page?.wishlist?.products).toHaveLength(30));
        let release!: () => void;
        deferNextQuery(
            new Promise<void>((resolve) => {
                release = resolve;
            }),
        );
        const before = fetch.mock.calls.length;
        act(() => setPage(2));
        await waitFor(() => expect(fetch).toHaveBeenCalledTimes(before + 1));
        act(() => {
            result.current.list.toggleProductInList('product-40');
        });
        await waitFor(() => expect(result.current.count).toBe(64));
        await waitFor(() => expect(result.current.page?.wishlist?.products.at(-1)?.uuid).toBe('product-60'));
        await act(async () => {
            release();
        });
        expect(result.current.page?.wishlist?.products.map((product) => product.uuid)).not.toContain('product-40');
        expect(result.current.page?.wishlist?.products.at(-1)?.uuid).toBe('product-60');
        expect(result.current.list.isProductInList('product-40')).toBe(false);
        expect(result.current.count).toBe(64);
    });
});
