import { render, screen } from '@testing-library/react';
import { ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeContentBlock } from 'types/contentEditor';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Product/ProductsSlider', () => ({
    ProductsSlider: ({ products, variant }: { products: Array<{ catalogNumber: string }>; variant: string }) => (
        <div
            data-product-catnums={products.map((product) => product.catalogNumber).join(',')}
            data-testid="products-slider"
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

vi.mock('components/Basic/Image/Image', () => ({
    // biome-ignore lint/performance/noImgElement: a stand-in for the next/image based component
    Image: ({ alt, src }: { alt: string; src: string }) => <img alt={alt} data-testid="media-image" src={src} />,
}));

const products = (catnums: string[]) =>
    catnums.map((catalogNumber) => ({ catalogNumber })) as TypeListedProductFragment[];

const block = (type: string, data: Record<string, unknown>, children?: TypeContentBlock[][]): TypeContentBlock => ({
    id: `${type}-${Math.random()}`,
    type,
    data,
    children,
});

const renderBlocks = (blocks: TypeContentBlock[], fetchedCatnums: string[] = [], areProductsFetching = false) =>
    render(
        <ContentRenderer
            areProductsFetching={areProductsFetching}
            document={{ blocks }}
            products={products(fetchedCatnums)}
        />,
    );

describe('ContentRenderer', () => {
    test('renders every block type, including blocks nested in columns', () => {
        const { container } = renderBlocks(
            [
                block('text', { html: '<p>Hello</p>' }),
                block('image', {
                    url: '/image.jpg',
                    width: 10,
                    height: 10,
                    alt: 'An image',
                    position: 'center',
                    link: null,
                }),
                block('button-link', {
                    text: 'Buy',
                    url: '/buy',
                    openInNewTab: true,
                    position: 'left',
                    color: '#00C8B7',
                }),
                block('table', { rows: 1, columns: 2, variant: 'secondary', cells: [['<b>a</b>', 'b']] }),
                block('video', {
                    provider: 'youtube',
                    videoId: 'x',
                    embedUrl: 'https://www.youtube-nocookie.com/embed/x',
                }),
                block('map', { url: 'https://www.google.com/maps/embed?pb=1' }),
                block('iframe', { url: 'https://example.com', height: 300 }),
                block('products', { catnums: ['a'] }),
                block('columns-1-2', { layout: '1/3-2/3' }, [
                    [block('text', { html: '<p>Left</p>' })],
                    [block('text', { html: '<p>Right</p>' })],
                ]),
            ],
            ['a'],
        );

        expect(screen.getAllByText('Hello')).toHaveLength(1);
        expect(screen.getByTestId('media-image')).toHaveAttribute('alt', 'An image');
        expect(screen.getByText('Buy')).toHaveAttribute('target', '_blank');
        expect(container.querySelector('table.secondary b')).toHaveTextContent('a');
        expect(container.querySelector('iframe[src="https://www.youtube-nocookie.com/embed/x"]')).toBeInTheDocument();
        expect(container.querySelector('iframe[src^="https://www.google.com/maps/embed"]')).toBeInTheDocument();
        expect(container.querySelector('iframe[height="300"]')).toBeInTheDocument();
        expect(screen.getByTestId('article-product-hero')).toBeInTheDocument();
        expect(screen.getByText('Left')).toBeInTheDocument();
        expect(screen.getByText('Right')).toBeInTheDocument();
        expect(container.querySelector(`[data-tid="${TIDs.content_block_columns}"]`)).toHaveClass(
            'lg:grid-cols-[1fr_2fr]',
        );
    });

    test('renders nothing for an unknown block type', () => {
        const { container } = renderBlocks([block('does-not-exist', {})]);

        expect(container.firstChild).toBeEmptyDOMElement();
    });

    test('renders nothing without a document', () => {
        const { container } = render(<ContentRenderer areProductsFetching={false} document={null} products={[]} />);

        expect(container).toBeEmptyDOMElement();
    });

    test('renders nothing for an image without url and for a video without embed url', () => {
        const { container } = renderBlocks([
            block('image', { url: null, alt: '', position: 'center', link: null }),
            block('video', { provider: null, videoId: null, embedUrl: null }),
        ]);

        expect(container.firstChild).toBeEmptyDOMElement();
    });

    test('renders a legacy image as a plain img inside its link', () => {
        const { container } = renderBlocks([
            block('image', { url: '/old.jpg', alt: 'Old', position: 'left', link: '/target', legacy: true }),
        ]);

        expect(screen.queryByTestId('media-image')).not.toBeInTheDocument();
        expect(container.querySelector('a[href="/target"] > img[src="/old.jpg"]')).toBeInTheDocument();
    });

    describe('products block', () => {
        test('renders product placeholders while products are loading', () => {
            renderBlocks([block('products', { catnums: ['product-1'] })], [], true);

            expect(screen.getAllByTestId('product-skeleton')).toHaveLength(4);
            expect(screen.queryByTestId('article-product-hero')).not.toBeInTheDocument();
        });

        test('renders no block when none of the configured products are visible', () => {
            renderBlocks([block('products', { catnums: ['product-1'] })], []);

            expect(screen.queryByTestId('article-product-hero')).not.toBeInTheDocument();
            expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
        });

        test('renders one visible product as an article hero', () => {
            renderBlocks([block('products', { catnums: ['product-1'] })], ['product-1']);

            expect(screen.getByTestId('article-product-hero')).toHaveAttribute('data-product-catnum', 'product-1');
            expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
        });

        test('renders a hero when only one of multiple configured products is visible', () => {
            renderBlocks([block('products', { catnums: ['product-1', 'hidden-product'] })], ['product-1']);

            expect(screen.getByTestId('article-product-hero')).toBeInTheDocument();
        });

        test('renders several products in the article slider in the configured order', () => {
            renderBlocks([block('products', { catnums: ['product-2', 'product-1'] })], ['product-1', 'product-2']);

            expect(screen.getByTestId('products-slider')).toHaveAttribute('data-variant', 'article');
            expect(screen.getByTestId('products-slider')).toHaveAttribute(
                'data-product-catnums',
                'product-2,product-1',
            );
        });
    });
});
