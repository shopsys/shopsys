import { SeoPageQueryDocument, TypeSeoPageQuery } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { Client, fetchExchange } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { expect, test, vi } from 'vitest';

test('keeps SEO attributes embedded in each page without missing-key warnings', async () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => undefined);
    const pages: Record<string, TypeSeoPageQuery> = {
        '/': {
            seoPage: {
                __typename: 'SeoPage',
                seo: {
                    __typename: 'SeoAttributes',
                    title: 'Homepage title',
                    metaDescription: 'Homepage description',
                    h1: null,
                    metaRobots: null,
                    canonicalUrl: 'https://example.com/',
                },
                ogTitle: null,
                ogDescription: null,
                ogImage: null,
                hreflangLinks: [],
            },
        },
        '/contact': {
            seoPage: {
                __typename: 'SeoPage',
                seo: {
                    __typename: 'SeoAttributes',
                    title: 'Contact title',
                    metaDescription: 'Contact description',
                    h1: null,
                    metaRobots: 'noindex',
                    canonicalUrl: 'https://example.com/contact',
                },
                ogTitle: null,
                ogDescription: null,
                ogImage: null,
                hreflangLinks: [],
            },
        },
    };
    const fetchMock = vi.fn<typeof fetch>(async (_url, options) => {
        const { variables } = JSON.parse(String(options?.body));
        return Response.json({ data: pages[variables.pageSlug] });
    });
    const client = new Client({
        url: 'https://example.com/graphql/',
        exchanges: [cache, fetchExchange],
        preferGetMethod: false,
        fetch: fetchMock,
    });

    for (const pageSlug of ['/', '/contact']) {
        expect((await client.query(SeoPageQueryDocument, { pageSlug }).toPromise()).data).toEqual(pages[pageSlug]);
    }

    for (const pageSlug of ['/', '/contact']) {
        expect(client.readQuery(SeoPageQueryDocument, { pageSlug })?.data).toEqual(pages[pageSlug]);
    }
    expect(fetchMock).toHaveBeenCalledTimes(2);
    expect(warn).not.toHaveBeenCalled();
});
