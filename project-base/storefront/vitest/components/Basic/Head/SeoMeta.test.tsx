import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { ReactNode } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    defaultLocale: 'cs',
    organizationName: 'Example s.r.o.' as string | null,
    logo: null as { __typename: 'Image'; name: string | null; url: string } | null,
    router: { asPath: '/', query: {} as Record<string, string> },
    seoPage: null as { seo: { canonicalUrl: string } } | null,
}));

beforeEach(() => {
    mocks.defaultLocale = 'cs';
    mocks.organizationName = 'Example s.r.o.';
    mocks.logo = null;
    mocks.router = { asPath: '/', query: {} };
    mocks.seoPage = null;
});

vi.mock('next/head', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('next/router', () => ({ useRouter: () => mocks.router }));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://shop.example/', defaultLocale: mocks.defaultLocale }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/seo/useSeoPage', () => ({ useSeoPage: () => mocks.seoPage }));
vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    seo: {
                        titleAddOn: null,
                        organization: { name: mocks.organizationName, logo: mocks.logo },
                    },
                },
            },
        },
    ],
}));

const renderTags = (element: ReactNode) => {
    const container = document.createElement('div');
    container.innerHTML = renderToStaticMarkup(element);

    return (selector: string) => container.querySelector(selector)?.getAttribute('content');
};

describe('Open Graph and Twitter tags', () => {
    test('use the organization name as the site name and omit it when empty', () => {
        expect(renderTags(<SeoMeta defaultTitle="Title" />)('meta[property="og:site_name"]')).toBe('Example s.r.o.');

        mocks.organizationName = '';

        expect(renderTags(<SeoMeta defaultTitle="Title" />)('meta[property="og:site_name"]')).toBeUndefined();
    });

    test.each([
        ['cs', 'cs_CZ'],
        ['en', 'en_US'],
        ['sk', 'sk_SK'],
        ['sl', 'sl_SI'],
        ['en-GB', 'en_GB'],
    ])('output the locale of the domain %s as %s', (defaultLocale, expected) => {
        mocks.defaultLocale = defaultLocale;

        expect(renderTags(<SeoMeta defaultTitle="Title" />)('meta[property="og:locale"]')).toBe(expected);
    });

    test('share the URL without filter, sort, page and load-more parameters but with the search query', () => {
        mocks.router = {
            asPath: '/category/?filter=abc&sort=PRICE_ASC&page=2&lm=1&q=shirt',
            query: { filter: 'abc', sort: 'PRICE_ASC', page: '2', lm: '1', q: 'shirt' },
        };
        const tag = renderTags(<SeoMeta defaultTitle="Title" />);

        expect(tag('meta[property="og:url"]')).toBe('https://shop.example/category/?q=shirt');
        expect(tag('meta[name="twitter:url"]')).toBe('https://shop.example/category/?q=shirt');
    });

    test('share the current URL when it has no parameters to remove', () => {
        mocks.router = { asPath: '/category/', query: {} };

        expect(renderTags(<SeoMeta defaultTitle="Title" />)('meta[property="og:url"]')).toBe(
            'https://shop.example/category/',
        );
    });

    test('share the canonical URL set in administration', () => {
        mocks.router = { asPath: '/category/?page=2', query: { page: '2' } };

        expect(
            renderTags(
                <SeoMeta
                    defaultTitle="Title"
                    seo={{
                        __typename: 'SeoAttributes',
                        title: null,
                        metaDescription: null,
                        h1: null,
                        metaRobots: null,
                        canonicalUrl: 'https://shop.example/canonical/',
                    }}
                />,
            )('meta[property="og:url"]'),
        ).toBe('https://shop.example/canonical/');
    });

    test('share the canonical URL of the SEO page in preference to the canonical URL of the entity', () => {
        mocks.seoPage = { seo: { canonicalUrl: 'https://shop.example/seo-page-canonical/' } };

        expect(
            renderTags(
                <SeoMeta
                    defaultTitle="Title"
                    seo={{
                        __typename: 'SeoAttributes',
                        title: null,
                        metaDescription: null,
                        h1: null,
                        metaRobots: null,
                        canonicalUrl: 'https://shop.example/canonical/',
                    }}
                />,
            )('meta[property="og:url"]'),
        ).toBe('https://shop.example/seo-page-canonical/');
    });

    test('share the homepage URL', () => {
        expect(renderTags(<SeoMeta defaultTitle="Title" />)('meta[property="og:url"]')).toBe('https://shop.example/');
    });

    test('output only the hostname as the Twitter domain and a summary card without an image', () => {
        const tag = renderTags(<SeoMeta defaultTitle="Title" />);

        expect(tag('meta[name="twitter:domain"]')).toBe('shop.example');
        expect(tag('meta[name="twitter:card"]')).toBe('summary');
    });

    test('output the resized image of the entity with its ALT and a large Twitter card', () => {
        const tag = renderTags(
            <SeoMeta
                defaultTitle="Title"
                ogImage={{ __typename: 'Image', name: 'Product ALT', url: 'https://shop.example/product.jpg' }}
            />,
        );

        expect(tag('meta[property="og:image"]')).toBe('https://shop.example/product.jpg?preset=og');
        expect(tag('meta[name="twitter:image"]')).toBe('https://shop.example/product.jpg?preset=og');
        expect(tag('meta[property="og:image:alt"]')).toBe('Product ALT');
        expect(tag('meta[name="twitter:image:alt"]')).toBe('Product ALT');
        expect(tag('meta[name="twitter:card"]')).toBe('summary_large_image');
    });

    test('output the resized organization logo when the page has no image', () => {
        mocks.logo = { __typename: 'Image', name: 'Logo ALT', url: 'https://shop.example/logo.png' };
        const tag = renderTags(<SeoMeta defaultTitle="Title" />);

        expect(tag('meta[property="og:image"]')).toBe('https://shop.example/logo.png?preset=og');
        expect(tag('meta[property="og:image:alt"]')).toBe('Logo ALT');
    });

    test('omit an image in a format social networks do not support', () => {
        const tag = renderTags(
            <SeoMeta
                defaultTitle="Title"
                ogImage={{ __typename: 'Image', name: 'Product ALT', url: 'https://shop.example/product.svg' }}
            />,
        );

        expect(tag('meta[property="og:image"]')).toBeUndefined();
        expect(tag('meta[property="og:image:alt"]')).toBeUndefined();
        expect(tag('meta[name="twitter:card"]')).toBe('summary');
    });
});
