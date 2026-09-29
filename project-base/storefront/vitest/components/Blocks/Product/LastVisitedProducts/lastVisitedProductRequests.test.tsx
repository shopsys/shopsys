import { act, render, screen, waitFor } from '@testing-library/react';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { LastVisitedProductsContent } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProductsContent';
import { getOperationAST, parse } from 'graphql';
import type { TypeProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
// biome-ignore lint/style/noRestrictedImports: Verify production hooks and Graphcache with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { describe, expect, test, vi } from 'vitest';
import { createProduct } from 'vitest/helpers/productFixture';

vi.mock('components/Basic/UserText/UserText', () => ({ UserText: () => null }));
vi.mock('components/Basic/UserText/GrapesJsProducts', () => ({
    GrapesJsProducts: ({ allFetchedProducts }: { allFetchedProducts?: TypeProductsByCatnums }) => (
        <div data-testid="article-products">
            {allFetchedProducts?.productsByCatnums.map((product) => product.fullName).join(',')}
        </div>
    ),
}));
vi.mock('components/Blocks/Product/ProductsSlider', () => ({
    VISIBLE_SLIDER_ITEMS_LAST_VISITED: 5,
    ProductsSlider: ({
        products,
        cardVariant,
    }: {
        products: TypeProductsByCatnums['productsByCatnums'];
        cardVariant: string;
    }) => (
        <div data-testid="visited-products" data-card-variant={cardVariant}>
            {products.map((product) => product.fullName).join(',')}
        </div>
    ),
}));
vi.mock('components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts', () => ({
    SkeletonModuleLastVisitedProducts: () => <div>Loading visited products</div>,
}));

const catnums = ['first-product', 'second-product'];

const createPage = () => {
    const operations: string[] = [];
    let releaseResponse: () => void = () => {};
    const responseReady = new Promise<void>((resolve) => {
        releaseResponse = resolve;
    });
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, fetchExchange],
        preferGetMethod: false,
        fetch: async (_url, options) => {
            const request = JSON.parse(String(options?.body));
            operations.push(getOperationAST(parse(request.query))!.name!.value);
            await responseReady;

            return new Response(JSON.stringify({ data: { productsByCatnums: catnums.map(createProduct) } }), {
                headers: { 'Content-Type': 'application/json' },
            });
        },
    });
    const page = (article: boolean, visited: boolean) => (
        <Provider value={client}>
            {article && <GrapesJsParser text={`|||[gjc-comp-ProductList&#61;${catnums.join(',')}]|||`} />}
            {visited && <LastVisitedProductsContent productsCatnums={catnums} />}
        </Provider>
    );

    return { page, operations, releaseResponse };
};

describe('shared article and last-visited product requests', () => {
    test('deduplicates overlapping requests for identical products and keeps compact visited cards', async () => {
        const { page, operations, releaseResponse } = createPage();

        render(page(true, true));
        await waitFor(() => expect(operations.length).toBeGreaterThan(0));
        expect(operations).toEqual(['ProductsByCatnums']);
        act(releaseResponse);

        await waitFor(() => expect(screen.getByTestId('article-products')).toHaveTextContent(catnums.join(',')));
        expect(await screen.findByTestId('visited-products')).toHaveTextContent(catnums.join(','));
        expect(screen.getByTestId('visited-products')).toHaveAttribute('data-card-variant', 'compact');
        expect(operations).toEqual(['ProductsByCatnums']);
    });

    test.each([true, false])('reuses completed data when the article mounts first: %s', async (articleFirst) => {
        const { page, operations, releaseResponse } = createPage();
        const { rerender } = render(page(articleFirst, !articleFirst));
        act(releaseResponse);
        await waitFor(() =>
            expect(screen.getByTestId(articleFirst ? 'article-products' : 'visited-products')).toHaveTextContent(
                catnums.join(','),
            ),
        );

        rerender(page(true, true));

        await waitFor(() => expect(screen.getByTestId('article-products')).toHaveTextContent(catnums.join(',')));
        expect(await screen.findByTestId('visited-products')).toHaveTextContent(catnums.join(','));
        expect(operations).toEqual(['ProductsByCatnums']);
    });
});
