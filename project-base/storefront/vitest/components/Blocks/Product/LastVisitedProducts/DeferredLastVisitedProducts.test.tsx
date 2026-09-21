import { act, render, screen } from '@testing-library/react';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { CookiesStoreContext } from 'components/providers/CookiesStoreProvider';
import { createCookiesStore, getDefaultCookiesStoreState } from 'utils/cookies/cookiesStore';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { deferredReady } = vi.hoisted(() => ({ deferredReady: vi.fn(() => true) }));

vi.mock('utils/useDeferredRender', () => ({ useDeferredRender: deferredReady }));
vi.mock('components/Blocks/Product/LastVisitedProducts/LastVisitedProducts', () => ({
    LastVisitedProducts: ({ currentProductCatnum }: { currentProductCatnum?: string }) => (
        <div data-current-product={currentProductCatnum}>Last visited products</div>
    ),
}));

const createHistoryStore = (lastVisitedProductsCatnums: string[] | null) =>
    createCookiesStore({ ...getDefaultCookiesStoreState(), lastVisitedProductsCatnums });

describe('DeferredLastVisitedProducts', () => {
    beforeEach(() => {
        deferredReady.mockReturnValue(true);
    });

    test.each([
        { history: null },
        { history: [] },
        { history: ['current-product'] },
    ])('keeps the component unmounted with history $history', ({ history }) => {
        const store = createHistoryStore(history);

        render(
            <CookiesStoreContext.Provider value={store}>
                <DeferredLastVisitedProducts currentProductCatnum="current-product" />
            </CookiesStoreContext.Provider>,
        );

        expect(screen.queryByText('Last visited products')).not.toBeInTheDocument();
    });

    test('waits for the deferred wave even when history contains another product', async () => {
        const store = createHistoryStore(['previous-product']);
        deferredReady.mockReturnValue(false);
        const { rerender } = render(
            <CookiesStoreContext.Provider value={store}>
                <DeferredLastVisitedProducts currentProductCatnum="current-product" />
            </CookiesStoreContext.Provider>,
        );

        expect(screen.queryByText('Last visited products')).not.toBeInTheDocument();

        deferredReady.mockReturnValue(true);
        rerender(
            <CookiesStoreContext.Provider value={store}>
                <DeferredLastVisitedProducts currentProductCatnum="current-product" />
            </CookiesStoreContext.Provider>,
        );

        expect(await screen.findByText('Last visited products')).toHaveAttribute(
            'data-current-product',
            'current-product',
        );
    });

    test('responds to history changes without reloading the page', async () => {
        const store = createHistoryStore(null);

        render(
            <CookiesStoreContext.Provider value={store}>
                <DeferredLastVisitedProducts />
            </CookiesStoreContext.Provider>,
        );

        expect(screen.queryByText('Last visited products')).not.toBeInTheDocument();

        act(() => store.getState().setCookiesStoreState({ lastVisitedProductsCatnums: ['previous-product'] }));

        expect(await screen.findByText('Last visited products')).toBeInTheDocument();

        act(() => store.getState().setCookiesStoreState({ lastVisitedProductsCatnums: [] }));

        expect(screen.queryByText('Last visited products')).not.toBeInTheDocument();
    });
});
