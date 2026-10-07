import { renderHook } from '@testing-library/react';
import { useSeo } from 'utils/seo/useSeo';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { mockSeoPageQuery, organization } = vi.hoisted(() => ({
    mockSeoPageQuery: vi.fn(),
    organization: { name: null, logo: null as { __typename: 'Image'; name: string | null; url: string } | null },
}));

vi.mock('graphql/requests/seoPage/queries/SeoPageQuery.generated', () => ({
    useSeoPageQuery: mockSeoPageQuery,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { seo: { titleAddOn: null, organization } } } }],
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ asPath: '/', pathname: '/', query: {} }),
}));

describe('SEO image ALT', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        organization.logo = null;
    });

    test.each([
        [' Custom ALT ', 'Social title', 'Page title', 'Default title', 'Custom ALT'],
        [null, ' Social title ', 'Page title', 'Default title', 'Social title'],
        [' \t ', ' ', 'Page title', 'Default title', 'Page title'],
        [null, null, 'Page title', 'Default title', 'Page title'],
        [null, null, null, 'Default title', 'Default title'],
        [null, null, null, undefined, ''],
    ])('resolves ALT from public values: %j, %j, %j, %j', (name, ogTitle, title, defaultTitle, expected) => {
        mockSeoPageQuery.mockReturnValue([
            { data: { seoPage: { seo: { title }, ogTitle, ogImage: { name, url: '/image.jpg' } } } },
        ]);

        const { result } = renderHook(() => useSeo({ defaultTitle }));

        expect(result.current.ogImageAlt).toBe(expected);
    });

    test('does not provide an ALT when there is no SEO image', () => {
        mockSeoPageQuery.mockReturnValue([{ data: { seoPage: { seo: { title: 'Page title' }, ogImage: null } } }]);

        const { result } = renderHook(() => useSeo({}));

        expect(result.current.ogImageAlt).toBeUndefined();
    });
});

describe('SEO image source', () => {
    const entityImage = { __typename: 'Image', name: 'Entity ALT', url: 'https://example.com/entity.jpg' } as const;

    beforeEach(() => {
        vi.clearAllMocks();
        organization.logo = { __typename: 'Image', name: 'Logo ALT', url: 'https://example.com/logo.png' };
        mockSeoPageQuery.mockReturnValue([{ data: { seoPage: { seo: { title: 'Page title' }, ogImage: null } } }]);
    });

    test('prefers the SEO page image to the entity image and the organization logo', () => {
        mockSeoPageQuery.mockReturnValue([
            {
                data: {
                    seoPage: {
                        seo: { title: 'Page title' },
                        ogImage: { name: 'SEO page ALT', url: 'https://example.com/seo-page.png' },
                    },
                },
            },
        ]);

        const { result } = renderHook(() => useSeo({ ogImage: entityImage }));

        expect(result.current.ogImageUrl).toBe('https://example.com/seo-page.png?preset=og');
        expect(result.current.ogImageAlt).toBe('SEO page ALT');
    });

    test('prefers the entity image to the organization logo', () => {
        const { result } = renderHook(() => useSeo({ ogImage: entityImage }));

        expect(result.current.ogImageUrl).toBe('https://example.com/entity.jpg?preset=og');
        expect(result.current.ogImageAlt).toBe('Entity ALT');
    });

    test('falls back to the organization logo', () => {
        const { result } = renderHook(() => useSeo({ ogImage: null }));

        expect(result.current.ogImageUrl).toBe('https://example.com/logo.png?preset=og');
        expect(result.current.ogImageAlt).toBe('Logo ALT');
    });

    test.each([
        [{ ...entityImage, name: null }, 'Page title'],
        [null, 'Page title'],
    ])('resolves ALT of an image without a name from the title: %j', (ogImage, expected) => {
        organization.logo = { __typename: 'Image', name: null, url: 'https://example.com/logo.png' };

        const { result } = renderHook(() => useSeo({ ogImage }));

        expect(result.current.ogImageAlt).toBe(expected);
    });

    test('skips an image in a format the image resizer cannot process', () => {
        const ogImage = { ...entityImage, url: 'https://example.com/entity.svg' };

        const { result } = renderHook(() => useSeo({ ogImage }));

        expect(result.current.ogImageUrl).toBe('https://example.com/logo.png?preset=og');
        expect(result.current.ogImageAlt).toBe('Logo ALT');
    });

    test('does not provide an image when no source has a supported format', () => {
        organization.logo = { __typename: 'Image', name: 'Logo ALT', url: 'https://example.com/logo.svg' };
        const ogImage = { ...entityImage, url: 'https://example.com/entity.webp' };

        const { result } = renderHook(() => useSeo({ ogImage }));

        expect(result.current.ogImageUrl).toBeUndefined();
        expect(result.current.ogImageAlt).toBeUndefined();
    });

    test('does not provide an image when there is none', () => {
        organization.logo = null;

        const { result } = renderHook(() => useSeo({}));

        expect(result.current.ogImageUrl).toBeUndefined();
    });
});
