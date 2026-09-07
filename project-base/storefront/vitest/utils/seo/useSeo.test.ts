import { renderHook } from '@testing-library/react';
import { useSeo } from 'utils/seo/useSeo';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { mockSeoPageQuery } = vi.hoisted(() => ({ mockSeoPageQuery: vi.fn() }));

vi.mock('graphql/requests/seoPage/queries/SeoPageQuery.generated', () => ({
    useSeoPageQuery: mockSeoPageQuery,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { seo: { title: 'Site title' } } } }],
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
    });

    test.each([
        [' Custom ALT ', 'Social title', 'Page title', 'Default title', 'Custom ALT'],
        [null, ' Social title ', 'Page title', 'Default title', 'Social title'],
        [' \t ', ' ', 'Page title', 'Default title', 'Page title'],
        [null, null, 'Page title', 'Default title', 'Page title'],
        [null, null, null, 'Default title', 'Default title'],
        [null, null, null, undefined, 'Site title'],
    ])('resolves ALT from public values: %j, %j, %j, %j', (name, ogTitle, title, defaultTitle, expected) => {
        mockSeoPageQuery.mockReturnValue([
            { data: { seoPage: { title, ogTitle, ogImage: { name, url: '/image.jpg' } } } },
        ]);

        const { result } = renderHook(() => useSeo({ defaultTitle }));

        expect(result.current.ogImageAlt).toBe(expected);
    });

    test('does not provide an ALT when there is no SEO image', () => {
        mockSeoPageQuery.mockReturnValue([{ data: { seoPage: { title: 'Page title', ogImage: null } } }]);

        const { result } = renderHook(() => useSeo({}));

        expect(result.current.ogImageAlt).toBeUndefined();
    });
});
