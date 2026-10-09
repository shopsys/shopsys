import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum, TypeProductReviewOrderingModeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/head', () => ({ default: ({ children }: { children: ReactNode }) => <>{children}</> }));
vi.mock('next/router', () => ({ useRouter: () => ({ asPath: '/product' }) }));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ currencyCode: 'CZK', url: 'https://example.com/' }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('graphql/requests/productReviews/queries/ProductReviewsQuery.generated', () => ({
    useProductReviewsQuery: () => {
        throw new Error('Structured data must reuse the product detail reviews.');
    },
}));

const reviews = {
    totalCount: 1,
    orderingMode: TypeProductReviewOrderingModeEnum.Newest,
    summary: { __typename: 'ProductReviewsSummary', totalCount: 1, averageRating: 5, ratingCounts: [] },
    pageInfo: { __typename: 'PageInfo', endCursor: null, hasNextPage: false, hasPreviousPage: false },
    edges: [
        {
            cursor: '1',
            node: {
                __typename: 'ProductReview',
                uuid: 'review',
                productName: 'Product',
                reviewerName: null,
                rating: 5,
                text: 'Great </script> product',
                createdAt: '2026-09-01T12:00:00+00:00',
                isVerifiedPurchase: true,
                responseText: null,
                responseCreatedAt: null,
                images: [],
            },
        },
    ],
} satisfies NonNullable<TypeProductDetailFragment['reviews']>;

type MetadataProduct = Pick<
    TypeProductDetailFragment,
    | '__typename'
    | 'fullName'
    | 'isInquiryType'
    | 'isSellingDenied'
    | 'images'
    | 'description'
    | 'catalogNumber'
    | 'ean'
    | 'brand'
    | 'price'
    | 'availability'
    | 'reviewsSummary'
    | 'reviews'
>;

const metadataProduct: MetadataProduct = {
    __typename: 'RegularProduct',
    fullName: 'Product',
    isInquiryType: false,
    isSellingDenied: false,
    images: [],
    description: 'Description',
    catalogNumber: '123',
    ean: null,
    brand: null,
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121',
        priceWithoutVat: '100',
        vatAmount: '21',
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        basicPrice: { priceWithVat: '121', priceWithoutVat: '100', vatAmount: '21' },
    },
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    reviewsSummary: reviews.summary,
    reviews,
};

const renderMetadata = (product: MetadataProduct) => {
    // The fixture intentionally excludes detail fields unrelated to structured data.
    const html = renderToStaticMarkup(<ProductMetadata product={product as TypeProductDetailFragment} />);
    const document = new DOMParser().parseFromString(html, 'text/html');
    return { document, metadata: JSON.parse(document.querySelector('script')?.textContent ?? '{}') };
};

describe('ProductMetadata', () => {
    test('renders initial product reviews into SSR structured data without another query', () => {
        const { document, metadata } = renderMetadata(metadataProduct);

        expect(document.querySelectorAll('script')).toHaveLength(1);
        expect(metadata.review).toEqual([
            expect.objectContaining({
                author: { '@type': 'Person', name: 'Anonymous customer' },
                datePublished: '2026-09-01',
                reviewBody: 'Great </script> product',
                reviewRating: expect.objectContaining({ ratingValue: 5 }),
            }),
        ]);
        expect(metadata.aggregateRating).toMatchObject({ ratingValue: 5, reviewCount: 1 });
    });

    test('omits reviews and aggregate rating when the product has no review data', () => {
        const { metadata } = renderMetadata({ ...metadataProduct, reviews: null, reviewsSummary: null });

        expect(metadata).not.toHaveProperty('review');
        expect(metadata).not.toHaveProperty('aggregateRating');
        expect(metadata.offers.availability).toBe('https://schema.org/InStock');
    });
});
