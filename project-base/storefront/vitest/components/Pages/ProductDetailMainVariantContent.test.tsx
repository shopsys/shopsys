import { fireEvent, screen } from '@testing-library/react';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { afterEach, describe, expect, test, vi } from 'vitest';
import { createProduct } from '../../helpers/productFixture';
import { renderWithTooltipProvider } from '../../helpers/renderWithTooltipProvider';

vi.mock('components/Basic/Head/ProductMetadata', () => ({ ProductMetadata: () => null }));
vi.mock('components/Blocks/Popup/DeliveryOptionsPopup/DeliveryOptionsLink', () => ({
    DeliveryOptionsLink: () => <button type="button">Delivery & pickup</button>,
}));
vi.mock('components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts', () => ({
    DeferredLastVisitedProducts: () => null,
}));
vi.mock('components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils', () => ({
    useLastVisitedProductView: () => undefined,
}));
vi.mock('components/Blocks/ProductReviews/useOpenReviewPopupFromUrl', () => ({
    useOpenReviewPopupFromUrl: () => undefined,
}));
vi.mock('components/Pages/ProductDetail/ProductDetailAccessories/DeferredProductDetailAccessories', () => ({
    DeferredProductDetailAccessories: () => null,
}));
vi.mock('components/Pages/ProductDetail/ProductDetailGallery', () => ({
    ProductDetailGallery: ({ images }: { images: TypeImageFragment[] }) => (
        <div role="img" aria-label="Product gallery" data-images={images.map((image) => image.url).join(',')} />
    ),
}));
vi.mock('components/Pages/ProductDetail/ProductDetailSections/ProductDetailSections', () => ({
    ProductDetailSections: () => null,
}));
vi.mock('components/Pages/ProductDetail/ProductDetailVariantsTable', () => ({
    ProductVariantsTable: ({ variants }: { variants: { uuid: string; fullName: string }[] }) =>
        variants.length > 0 ? (
            <ul id={PRODUCT_VARIANTS_ID} tabIndex={-1}>
                {variants.map((variant) => (
                    <li key={variant.uuid}>{variant.fullName}</li>
                ))}
            </ul>
        ) : (
            <p>No variants</p>
        ),
}));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ defaultLocale: 'en', url: 'http://localhost' }),
}));
vi.mock('gtm/factories/useGtmFriendlyPageReadyEvent', () => ({ useGtmFriendlyPageReadyEvent: () => null }));
vi.mock('gtm/utils/pageReadyEvents/useGtmPageReadyEvent', () => ({ useGtmPageReadyEvent: () => undefined }));
vi.mock('gtm/utils/pageReadyEvents/useGtmProductDetailViewEvent', () => ({
    useGtmProductDetailViewEvent: () => undefined,
}));
vi.mock('next/router', () => ({ useRouter: () => ({ asPath: '/main-product' }) }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const product: TypeMainVariantDetailFragment = {
    ...createProduct('main-product'),
    __typename: 'MainVariant',
    name: 'Main product',
    namePrefix: null,
    nameSuffix: null,
    shortDescription: 'A short introduction to this product family.',
    seo: {
        __typename: 'SeoAttributes',
        h1: null,
        title: null,
        metaDescription: null,
        metaRobots: null,
        canonicalUrl: null,
    },
    images: [{ __typename: 'Image', name: 'Main image', url: '/main.jpg' }],
    variants: [
        {
            ...createProduct('concrete-variant'),
            __typename: 'Variant',
            mainVariant: null,
            additionalServices: [],
            mainImage: { __typename: 'Image', name: 'Variant image', url: '/variant.jpg' },
        },
    ],
    ean: null,
    description: null,
    zboziCategory: null,
    promotionBuyQuantity: null,
    promotionFreeQuantity: null,
    isMainVariant: true,
    brand: null,
    reviewsSummary: null,
    breadcrumb: [],
    hreflangLinks: [],
    parameters: [],
    relatedProducts: [],
    files: [],
    gifts: [],
    reviews: null,
    price: {
        ...createProduct('main-product').price,
        nextPriceChange: null,
        basicPrice: { priceWithVat: '121', priceWithoutVat: '100', vatAmount: '21' },
    },
    accessories: [],
    productVideos: [],
};

describe('ProductDetailMainVariantContent', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
        window.history.replaceState(null, '', '/');
    });

    test('shows the product introduction and links to the unique variant list while preserving gallery images', () => {
        renderWithTooltipProvider(
            <ProductDetailMainVariantContent product={product} isProductDetailFetching={false} />,
        );

        expect(screen.getByRole('heading', { name: 'Main product' })).toBeInTheDocument();
        expect(screen.getByText(product.shortDescription!)).toBeInTheDocument();
        expect(screen.getByRole('img', { name: 'Product gallery' })).toHaveAttribute(
            'data-images',
            '/main.jpg,/variant.jpg',
        );
        expect(screen.getByRole('link', { name: 'Choose a variant' })).toHaveAttribute(
            'href',
            `#${PRODUCT_VARIANTS_ID}`,
        );
        expect(document.querySelectorAll(`#${PRODUCT_VARIANTS_ID}`)).toHaveLength(1);
        expect(document.getElementById(PRODUCT_VARIANTS_ID)).toContainElement(screen.getByText('concrete-variant'));
        expect(screen.getByRole('button', { name: 'Delivery & pickup' })).toBeInTheDocument();
    });

    test('does not offer a variant-list link when there are no variants', () => {
        renderWithTooltipProvider(
            <ProductDetailMainVariantContent product={{ ...product, variants: [] }} isProductDetailFetching={false} />,
        );

        expect(screen.queryByRole('link', { name: 'Choose a variant' })).not.toBeInTheDocument();
        expect(screen.getByText('No variants')).toBeInTheDocument();
    });

    test.each([
        { reducedMotion: false, behavior: 'smooth' },
        { reducedMotion: true, behavior: 'instant' },
    ])('moves focus to the variant list with reducedMotion=$reducedMotion', ({ reducedMotion, behavior }) => {
        vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: reducedMotion }));
        renderWithTooltipProvider(
            <ProductDetailMainVariantContent product={product} isProductDetailFetching={false} />,
        );
        const variantList = document.getElementById(PRODUCT_VARIANTS_ID)!;
        const scrollIntoView = vi.fn();
        variantList.scrollIntoView = scrollIntoView;

        fireEvent.click(screen.getByRole('link', { name: 'Choose a variant' }));

        expect(variantList).toHaveFocus();
        expect(window.location.hash).toBe(`#${PRODUCT_VARIANTS_ID}`);
        expect(scrollIntoView).toHaveBeenCalledWith({ behavior, block: 'start' });
    });
});
