import { render, screen } from '@testing-library/react';
import { GrapesJsProducts } from 'components/Basic/UserText/GrapesJsProducts';
import { TypeProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Product/ProductsSlider', () => ({
    ProductsSlider: ({
        products,
        tid,
        variant,
    }: {
        products: Array<{ catalogNumber: string }>;
        tid?: string;
        variant: string;
    }) => (
        <div
            data-product-catnums={products.map((product) => product.catalogNumber).join(',')}
            data-testid="products-slider"
            data-tid={tid}
            data-variant={variant}
        />
    ),
    VISIBLE_SLIDER_ITEMS_ARTICLE: 3,
}));

vi.mock('components/Blocks/Product/ArticleProductHero', () => ({
    ArticleProductHero: ({ product }: { product: { catalogNumber: string } }) => (
        <div data-product-catnum={product.catalogNumber} data-testid="article-product-hero" />
    ),
}));

vi.mock('components/Blocks/Skeleton/SkeletonModuleProductListItem', () => ({
    SkeletonModuleProductListItem: () => <div data-testid="product-skeleton" />,
}));

const createProduct = (catalogNumber: string) => ({ catalogNumber });

const createProductsResponse = (catalogNumbers: string[]) =>
    ({
        productsByCatnums: catalogNumbers.map(createProduct),
    }) as TypeProductsByCatnums;

const createRawProductPart = (catalogNumbers: string[]) =>
    `|||[gjc-comp-ProductList&#61;${catalogNumbers.join(',')}]|||`;

const renderGrapesJsProducts = (
    catalogNumbers: string[],
    fetchedCatalogNumbers: string[] = catalogNumbers,
    areProductsFetching = false,
) =>
    render(
        <GrapesJsProducts
            allFetchedProducts={createProductsResponse(fetchedCatalogNumbers)}
            areProductsFetching={areProductsFetching}
            rawProductPart={createRawProductPart(catalogNumbers)}
            visibleSliderItems={3}
        />,
    );

describe('GrapesJsProducts', () => {
    test('renders product placeholders while products are loading', () => {
        renderGrapesJsProducts(['product-1'], [], true);

        expect(screen.getAllByTestId('product-skeleton')).toHaveLength(4);
        expect(screen.queryByTestId('article-product-hero')).not.toBeInTheDocument();
        expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
    });

    test('renders no product block when none of the configured products are visible', () => {
        renderGrapesJsProducts(['product-1'], []);

        expect(screen.queryByTestId('article-product-hero')).not.toBeInTheDocument();
        expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
    });

    test('renders one visible product as an article hero', () => {
        renderGrapesJsProducts(['product-1']);

        expect(screen.getByTestId('article-product-hero')).toHaveAttribute('data-product-catnum', 'product-1');
        expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
    });

    test('keeps multiple visible products in the article slider', () => {
        renderGrapesJsProducts(['product-1', 'product-2']);

        expect(screen.getByTestId('products-slider')).toHaveAttribute('data-variant', 'article');
        expect(screen.getByTestId('products-slider')).not.toHaveAttribute('data-tid');
    });

    test('renders a hero when only one of multiple configured products is visible', () => {
        renderGrapesJsProducts(['product-1', 'hidden-product'], ['product-1']);

        expect(screen.getByTestId('article-product-hero')).toHaveAttribute('data-product-catnum', 'product-1');
        expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
    });

    test('renders products in the order configured in the concrete product block', () => {
        renderGrapesJsProducts(['product-2', 'product-1'], ['product-1', 'product-2']);

        expect(screen.getByTestId('products-slider')).toHaveAttribute('data-product-catnums', 'product-2,product-1');
    });
});
