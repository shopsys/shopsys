import { act, render, screen, waitFor } from '@testing-library/react';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { TypeRecommendationType } from 'graphql/types';
import { renderToString } from 'react-dom/server';
// biome-ignore lint/style/noRestrictedImports: A test client exercises the real query lifecycle with a controlled HTTP response.
import { Client, createClient, fetchExchange, Provider, ssrExchange } from 'urql';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Skeleton/SkeletonModuleProductSlider', () => ({
    SkeletonModuleProductSlider: () => <div role="status">Loading recommendations</div>,
}));

vi.mock('components/Blocks/Product/ProductsSliderPlaceholder', () => ({
    ProductsSliderPlaceholder: ({ products }: { products: { slug: string; fullName: string }[] }) => (
        <>
            {products.map((product) => (
                <a key={product.slug} href={product.slug}>
                    {product.fullName}
                </a>
            ))}
        </>
    ),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ isLuigisBoxActive: true }),
}));

const mockedRouter = vi.hoisted(() => ({ pathname: '/' }));
vi.mock('next/router', () => ({ useRouter: () => mockedRouter }));

vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (state: { userIdentifier: string }) => string) =>
        selector({ userIdentifier: 'test-visitor' }),
}));

vi.mock('utils/useDeferredRender', () => ({ useDeferredRender: () => false }));

type RecommendationResult = {
    data?: { recommendedProducts: { slug: string; fullName: string }[] };
    errors?: { message: string }[];
};

const createTestClient = (isClient: boolean) => {
    let resolveResponse!: (response: Response) => void;
    const fetch = vi.fn<typeof globalThis.fetch>(
        () =>
            new Promise<Response>((resolve) => {
                resolveResponse = resolve;
            }),
    );
    const client = createClient({
        url: 'http://localhost/graphql',
        preferGetMethod: false,
        exchanges: [ssrExchange({ isClient }), fetchExchange],
        fetch,
    });
    const respond = (result: RecommendationResult) =>
        resolveResponse(
            new Response(JSON.stringify(result), {
                headers: { 'Content-Type': 'application/json' },
            }),
        );

    return { client, fetch, respond };
};

const recommendations = (client: Client, recommendationType: TypeRecommendationType, itemUuids: string[]) => (
    <Provider value={client}>
        <DeferredRecommendedProducts
            itemUuids={itemUuids}
            recommendationType={recommendationType}
            render={(content) => (
                <section>
                    <h2>Recommended for you</h2>
                    {content}
                </section>
            )}
        />
    </Provider>
);

describe.each([
    { recommendationType: TypeRecommendationType.Personalized, pathname: '/', identifier: 'homepage', itemUuids: [] },
    {
        recommendationType: TypeRecommendationType.ItemDetail,
        pathname: '/products/[productSlug]',
        identifier: 'product-detail',
        itemUuids: ['test-product'],
    },
    {
        recommendationType: TypeRecommendationType.Basket,
        pathname: '/cart',
        identifier: 'cart',
        itemUuids: ['cart-product-one', 'cart-product-two'],
    },
    {
        recommendationType: TypeRecommendationType.BasketPopup,
        pathname: '/categories/[categorySlug]',
        identifier: 'category-detail',
        itemUuids: ['added-product'],
    },
])('client-only $recommendationType recommendations', ({ recommendationType, pathname, identifier, itemUuids }) => {
    test('reserves the section on the server without starting a recommendation request', () => {
        const server = createTestClient(false);

        mockedRouter.pathname = pathname;
        const html = renderToString(recommendations(server.client, recommendationType, itemUuids));

        expect(html).toContain('Recommended for you');
        expect(html).toContain('Loading recommendations');
        expect(server.fetch).not.toHaveBeenCalled();
    });

    test.each([
        'products',
        'empty',
        'error',
    ] as const)('hydrates and requests recommendations automatically, then handles %s', async (result) => {
        const server = createTestClient(false);
        mockedRouter.pathname = pathname;
        const container = document.createElement('div');
        container.innerHTML = renderToString(recommendations(server.client, recommendationType, itemUuids));
        document.body.appendChild(container);
        const browser = createTestClient(true);
        const onRecoverableError = vi.fn();

        render(recommendations(browser.client, recommendationType, itemUuids), {
            container,
            hydrate: true,
            onRecoverableError,
        });

        expect(server.fetch).not.toHaveBeenCalled();
        await waitFor(() => expect(browser.fetch).toHaveBeenCalledTimes(1));
        expect(JSON.parse(browser.fetch.mock.calls[0][1]?.body as string).variables).toEqual({
            itemUuids,
            userIdentifier: 'test-visitor',
            recommendationType,
            recommenderClientIdentifier: identifier,
            limit: 10,
        });
        expect(screen.getByRole('status')).toBeInTheDocument();
        expect(onRecoverableError).not.toHaveBeenCalled();

        await act(async () => {
            browser.respond(
                result === 'error'
                    ? { errors: [{ message: 'Recommendations unavailable' }] }
                    : {
                          data: {
                              recommendedProducts:
                                  result === 'products'
                                      ? [{ slug: '/recommended-product', fullName: 'Recommended product' }]
                                      : [],
                          },
                      },
            );
        });

        await waitFor(() => expect(screen.queryByRole('status')).not.toBeInTheDocument());
        if (result === 'products') {
            expect(screen.getByRole('link', { name: 'Recommended product' })).toHaveAttribute(
                'href',
                '/recommended-product',
            );
        } else {
            expect(screen.queryByRole('heading', { name: 'Recommended for you' })).not.toBeInTheDocument();
        }
        expect(browser.fetch).toHaveBeenCalledTimes(1);
        expect(onRecoverableError).not.toHaveBeenCalled();
    });
});
