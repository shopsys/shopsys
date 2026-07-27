import { act, fireEvent, render, screen, within } from '@testing-library/react';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const productImagesQueryMocks = vi.hoisted(() => ({
    data: undefined as
        | {
              product: {
                  images: Array<{ __typename: 'Image'; name: string | null; url: string }>;
              };
          }
        | undefined,
    fetchGallery: vi.fn(),
    useProductListItemImagesQuery: vi.fn(),
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt, src }: { alt: string; src?: string }) => <span aria-label={alt} data-src={src} role="img" />,
}));

vi.mock('components/Blocks/Product/ProductFlags', () => ({
    ProductFlags: () => null,
}));

vi.mock('graphql/requests/products/queries/ProductListItemImagesQuery.generated', () => ({
    useProductListItemImagesQuery: (...args: unknown[]) =>
        productImagesQueryMocks.useProductListItemImagesQuery(...args),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const product = {
    __typename: 'RegularProduct',
    id: 42,
    uuid: 'f7888ef5-ae16-4f5c-b98d-4a6c947a9f71',
    slug: '/test-product',
    fullName: 'Test product',
    stockQuantity: 10,
    isAllowedNegativeStock: false,
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    flags: [],
    mainImage: { __typename: 'Image', url: '/main.jpg' },
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121',
        priceWithoutVat: '100',
        vatAmount: '21',
        isPriceFrom: false,
        percentageDiscount: null,
        basicPrice: {
            __typename: 'Price',
            priceWithVat: '121',
        },
    },
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    availableStoresCount: 1,
    catalogNumber: 'TEST-42',
    brand: null,
    categories: [{ __typename: 'Category', name: 'Test category' }],
    isMainVariant: false,
    isInquiryType: false,
    unit: { __typename: 'Unit', name: 'pcs' },
} satisfies TypeListedProductFragment;

const galleryImages = [
    { __typename: 'Image' as const, name: 'Main view', url: '/main.jpg' },
    { __typename: 'Image' as const, name: 'Side view', url: '/side.jpg' },
    { __typename: 'Image' as const, name: 'Back view', url: '/back.jpg' },
];

const renderProductImage = (isWithImageGallery = true) =>
    render(
        <a href="/test-product">
            <ProductListItemImage
                imageCount={3}
                isWithImageGallery={isWithImageGallery}
                product={product}
                size="large"
                visibleItemsConfig={{ flags: true }}
            />
        </a>,
    );

describe('ProductListItemImage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        productImagesQueryMocks.data = undefined;
        productImagesQueryMocks.useProductListItemImagesQuery.mockImplementation(() => [
            { data: productImagesQueryMocks.data },
            productImagesQueryMocks.fetchGallery,
        ]);
    });

    test('does not mount the gallery query outside enabled product grids', () => {
        renderProductImage(false);

        expect(screen.getByRole('img')).toHaveAttribute('data-src', '/main.jpg');
        expect(screen.queryByRole('list', { name: 'Product media gallery' })).not.toBeInTheDocument();
        expect(productImagesQueryMocks.useProductListItemImagesQuery).not.toHaveBeenCalled();
    });

    test('keeps the image container within the available product card width', () => {
        renderProductImage(false);

        const imageContainer = screen.getByRole('img').parentElement?.parentElement;

        expect(imageContainer).toHaveClass('w-full');
        expect(imageContainer).toHaveStyle({ maxWidth: '180px' });
        expect(imageContainer).not.toHaveStyle({ width: '180px' });
    });

    test('keeps the product card background in the same compositing layer as gallery images', () => {
        renderProductImage();

        const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });
        const gallerySlide = galleryTrack.querySelector('li');

        expect(gallerySlide).toHaveClass('bg-background-more');
        expect(gallerySlide).toHaveClass('vl:group-hover:bg-background-default');
    });

    test('fetches metadata after deliberate hover and loads another image only after scrolling to it', () => {
        vi.useFakeTimers();

        try {
            const { rerender } = renderProductImage();
            const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });
            const gallery = galleryTrack.parentElement as HTMLDivElement;
            Object.defineProperty(galleryTrack, 'clientWidth', { configurable: true, value: 180 });

            expect(galleryTrack).toHaveAttribute('tabindex', '-1');
            expect(within(galleryTrack).getByRole('img')).toHaveAttribute('data-src', '/main.jpg');
            expect(galleryTrack.querySelector('[data-src="/side.jpg"]')).not.toBeInTheDocument();

            fireEvent.pointerEnter(gallery, { pointerType: 'mouse' });
            act(() => {
                vi.advanceTimersByTime(249);
            });
            expect(productImagesQueryMocks.fetchGallery).not.toHaveBeenCalled();

            act(() => {
                vi.advanceTimersByTime(1);
            });
            expect(productImagesQueryMocks.fetchGallery).toHaveBeenCalledTimes(1);

            productImagesQueryMocks.data = { product: { images: galleryImages } };
            rerender(
                <a href="/test-product">
                    <ProductListItemImage
                        imageCount={3}
                        isWithImageGallery
                        product={product}
                        size="large"
                        visibleItemsConfig={{ flags: true }}
                    />
                </a>,
            );

            expect(galleryTrack.querySelector('[data-src="/side.jpg"]')).not.toBeInTheDocument();

            galleryTrack.scrollLeft = 180;
            fireEvent.scroll(galleryTrack);

            expect(galleryTrack.querySelector('[data-src="/side.jpg"]')).toBeInTheDocument();
            expect(galleryTrack.querySelector('[data-src="/back.jpg"]')).not.toBeInTheDocument();
            expect(productImagesQueryMocks.fetchGallery).toHaveBeenCalledTimes(1);
        } finally {
            vi.useRealTimers();
        }
    });

    test('prevents product navigation after a swipe gesture', () => {
        const onProductClick = vi.fn();
        render(
            <a href="/test-product" onClick={onProductClick}>
                <ProductListItemImage
                    imageCount={3}
                    isWithImageGallery
                    product={product}
                    size="large"
                    visibleItemsConfig={{ flags: true }}
                />
            </a>,
        );
        const galleryTrack = screen.getByRole('list', { name: 'Product media gallery' });

        fireEvent.pointerDown(galleryTrack, { clientX: 0, clientY: 0 });
        fireEvent.pointerMove(galleryTrack, { clientX: 25, clientY: 0 });
        fireEvent.click(galleryTrack, { detail: 1 });

        expect(onProductClick).not.toHaveBeenCalled();
    });
});
