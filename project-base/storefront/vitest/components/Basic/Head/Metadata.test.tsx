import { ArticleMetadata } from 'components/Basic/Head/ArticleMetadata';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { ReactElement, ReactNode } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';
import { describe, expect, test, vi } from 'vitest';

vi.mock('graphql/requests/productReviews/queries/ProductReviewsQuery.generated', () => ({
    useProductReviewsQuery: () => [{ data: undefined }],
}));

vi.mock('next/head', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('next/router', () => ({ useRouter: () => ({ asPath: '/product/' }) }));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://shop.example/', currencyCode: 'CZK' }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/hledat'],
}));
vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    seo: {
                        organization: {
                            name: 'Example',
                            vatId: 'CZ123',
                            companyNumber: '123',
                            description: 'Company',
                            streetAddress: 'Main 1',
                            addressLocality: 'Prague',
                            postalCode: '11000',
                            addressCountry: 'CZ',
                            logo: 'https://shop.example/logo.png',
                            sameAs: ['https://social.example/company'],
                        },
                    },
                },
            },
        },
    ],
}));

const readMetadata = (element: ReactElement) => {
    const container = document.createElement('div');
    container.innerHTML = renderToStaticMarkup(element);
    return JSON.parse(container.querySelector('script')!.textContent!);
};

const product = {
    __typename: 'RegularProduct',
    fullName: 'Product',
    images: [{ url: 'https://shop.example/1.jpg' }, { url: 'https://shop.example/2.jpg' }],
    description: '<p>First</p><p><strong>Second</strong></p>',
    catalogNumber: 'SKU',
    ean: '1234567890123',
    price: { priceWithVat: '120' },
    availability: { status: TypeAvailabilityStatusEnum.InStock },
    isInquiryType: false,
    isSellingDenied: false,
} as TypeProductDetailFragment;

describe('server-rendered JSON-LD', () => {
    test('uses the current domain and localized search path without escaping the template variable', () => {
        expect(readMetadata(<SearchMetadata />).potentialAction.target.urlTemplate).toBe(
            'https://shop.example/hledat?q={q}',
        );
    });

    test('prepends the homepage and numbers the complete breadcrumb hierarchy', () => {
        const metadata = readMetadata(
            <BreadcrumbsMetadata
                breadcrumbs={[
                    { __typename: 'Link', name: 'Category', slug: '/category/' },
                    { __typename: 'Link', name: 'Product', slug: '/product/' },
                ]}
            />,
        );
        expect(metadata.itemListElement).toEqual([
            { '@type': 'ListItem', position: 1, name: 'Home page', item: 'https://shop.example/' },
            { '@type': 'ListItem', position: 2, name: 'Category', item: 'https://shop.example/category/' },
            { '@type': 'ListItem', position: 3, name: 'Product' },
        ]);
    });

    test('outputs all product images, plain text, EAN and an absolute offer URL', () => {
        const metadata = readMetadata(<ProductMetadata product={product} />);
        expect(metadata.image).toEqual(['https://shop.example/1.jpg', 'https://shop.example/2.jpg']);
        expect(metadata.description).toBe('First Second');
        expect(metadata.gtin13).toBe('1234567890123');
        expect(metadata).not.toHaveProperty('mpn');
        expect(metadata.offers).toMatchObject({
            '@type': 'Offer',
            url: 'https://shop.example/product/',
            price: '120',
            itemCondition: 'https://schema.org/NewCondition',
            availability: 'https://schema.org/InStock',
        });
    });

    test.each([{ isInquiryType: true }, { isSellingDenied: true }])('omits non-purchasable offers: %o', (flags) => {
        expect(readMetadata(<ProductMetadata product={{ ...product, ...flags }} />)).not.toHaveProperty('offers');
    });

    test('aggregates only purchasable variant prices', () => {
        const mainVariant = {
            ...product,
            __typename: 'MainVariant',
            variants: [
                { ...product, price: { priceWithVat: '150' } },
                { ...product, price: { priceWithVat: '90' } },
                { ...product, isInquiryType: true, price: { priceWithVat: '0' } },
                { ...product, isSellingDenied: true, price: { priceWithVat: '999' } },
            ],
        } as unknown as TypeMainVariantDetailFragment;
        const offer = readMetadata(<ProductMetadata product={mainVariant} />).offers;
        expect(offer).toMatchObject({ '@type': 'AggregateOffer', lowPrice: 90, highPrice: 150 });
        expect(offer).not.toHaveProperty('price');
        expect(readMetadata(<ProductMetadata product={{ ...mainVariant, variants: [] }} />)).not.toHaveProperty(
            'offers',
        );
    });

    test('shares organization details with the article publisher and enriches blog authors', () => {
        const organization = readMetadata(<LogoMetadata />);
        const article = readMetadata(
            <ArticleMetadata
                headline="Article"
                type="BlogPosting"
                datePublished="2026-08-01T10:00:00Z"
                dateModified="2026-09-01T10:00:00Z"
                authorName="Author"
                authorJobTitle="Editor"
                authorImage="https://shop.example/author.jpg"
                imageUrl="https://shop.example/article.jpg"
            />,
        );
        expect(article).toMatchObject({
            '@type': 'BlogPosting',
            datePublished: '2026-08-01T10:00:00Z',
            dateModified: '2026-09-01T10:00:00Z',
            image: 'https://shop.example/article.jpg',
            author: { '@type': 'Person', name: 'Author', jobTitle: 'Editor', image: 'https://shop.example/author.jpg' },
        });
        const { '@context': context, ...publisher } = organization;
        expect(context).toBe('https://schema.org');
        expect(article.publisher).toEqual(publisher);
        expect(organization.identifier).toEqual({ '@type': 'PropertyValue', propertyID: 'IČO', value: '123' });
        expect(organization.address['@type']).toBe('PostalAddress');
        expect(organization.logo).toBe('https://shop.example/logo.png');
        expect(organization.sameAs).toEqual(['https://social.example/company']);
    });

    test('keeps regular articles and unknown historical dates unmodified', () => {
        const metadata = readMetadata(<ArticleMetadata headline="Article" />);
        expect(metadata['@type']).toBe('Article');
        expect(metadata).not.toHaveProperty('datePublished');
        expect(metadata).not.toHaveProperty('dateModified');
    });

    test('preserves malicious text as data without creating a second script', () => {
        const text = '</script><script>alert(1)</script>';
        const encoded = serializeJsonForScriptTag({ text });
        expect(encoded).not.toContain('<');
        expect(JSON.parse(encoded)).toEqual({ text });
        const container = document.createElement('div');
        container.innerHTML = renderToStaticMarkup(<ArticleMetadata headline={text} />);
        expect(container.querySelectorAll('script')).toHaveLength(1);
        expect(JSON.parse(container.querySelector('script')!.textContent!).headline).toBe(text);
    });
});
