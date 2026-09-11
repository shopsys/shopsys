import { act, fireEvent, render } from '@testing-library/react';
import { ExtendedNextLink, ExtendedNextLinkProps } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { RouterContext } from 'next/dist/shared/lib/router-context.shared-runtime';
import { NextRouter } from 'next/router';
import { renderToString } from 'react-dom/server';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const origin = window.location.origin;
const anotherPortUrl = new URL('/blog/', origin);
anotherPortUrl.port = '8443';
const anotherProtocolUrl = new URL('/blog/', origin);
anotherProtocolUrl.protocol = anotherProtocolUrl.protocol === 'http:' ? 'https:' : 'http:';

const cases: {
    name: string;
    props: ExtendedNextLinkProps;
    expectedHref: string;
    currentLocale?: string;
    expectedNavigation?: { as: string; locale: ExtendedNextLinkProps['locale'] };
}[] = [
    { name: 'absolute blog URL', props: { href: `${origin}/blog/`, type: 'blogCategory' }, expectedHref: '/blog' },
    {
        name: 'blog query and anchor',
        props: { href: `${origin}/blog/?page=2#articles`, type: 'blogCategory' },
        expectedHref: '/blog?page=2#articles',
    },
    { name: 'ordinary absolute URL', props: { href: `${origin}/contact/` }, expectedHref: '/contact' },
    {
        name: 'explicit absolute as',
        props: { href: '/contact', as: `${origin}/contact/` },
        expectedHref: '/contact',
    },
    { name: 'relative URL', props: { href: '/blog/' }, expectedHref: '/blog' },
    { name: 'homepage', props: { href: `${origin}/` }, expectedHref: '/' },
    {
        name: 'URL object',
        props: { href: { pathname: '/blog', query: { page: '2' }, hash: 'articles' } },
        expectedHref: '/blog?page=2#articles',
    },
    {
        name: 'another domain',
        props: { href: 'https://other.example.com/blog/' },
        expectedHref: 'https://other.example.com/blog/',
    },
    {
        name: 'friendly URL on another domain',
        props: { href: 'https://other.example.com/blog/', type: 'blogCategory' },
        expectedHref: 'https://other.example.com/blog/',
    },
    {
        name: 'another port',
        props: { href: anotherPortUrl.href, type: 'blogCategory' },
        expectedHref: anotherPortUrl.href,
    },
    {
        name: 'another protocol',
        props: { href: anotherProtocolUrl.href, type: 'blogCategory' },
        expectedHref: anotherProtocolUrl.href,
    },
    { name: 'email', props: { href: 'mailto:info@example.com' }, expectedHref: 'mailto:info@example.com' },
    { name: 'telephone', props: { href: 'tel:+420123456789' }, expectedHref: 'tel:+420123456789' },
    {
        name: 'absolute link to root domain',
        props: { href: `${origin}/contact` },
        currentLocale: 'sk',
        expectedHref: '/contact',
        expectedNavigation: { as: '/contact', locale: false },
    },
    {
        name: 'absolute link to another language',
        props: { href: `${origin}/en/contact` },
        currentLocale: 'sk',
        expectedHref: '/en/contact',
        expectedNavigation: { as: '/en/contact', locale: false },
    },
    {
        name: 'absolute link within current language',
        props: { href: `${origin}/sk/contact` },
        currentLocale: 'sk',
        expectedHref: '/sk/contact',
        expectedNavigation: { as: '/sk/contact', locale: false },
    },
    {
        name: 'friendly URL to root domain',
        props: { href: `${origin}/blog/?page=2#articles`, type: 'blogCategory' },
        currentLocale: 'sk',
        expectedHref: '/blog?page=2#articles',
        expectedNavigation: { as: '/blog?page=2#articles', locale: false },
    },
    {
        name: 'friendly URL to another language',
        props: { href: `${origin}/en/blog/`, type: 'blogCategory' },
        currentLocale: 'sk',
        expectedHref: '/en/blog',
        expectedNavigation: { as: '/en/blog', locale: false },
    },
    {
        name: 'explicit absolute as to another language',
        props: { href: '/contact', as: `${origin}/en/contact` },
        currentLocale: 'sk',
        expectedHref: '/en/contact',
        expectedNavigation: { as: '/en/contact', locale: false },
    },
    {
        name: 'relative link keeps current language',
        props: { href: '/contact' },
        currentLocale: 'sk',
        expectedHref: '/sk/contact',
        expectedNavigation: { as: '/contact', locale: undefined },
    },
    {
        name: 'relative as keeps current language',
        props: { href: `${origin}/contact`, as: '/contact' },
        currentLocale: 'sk',
        expectedHref: '/sk/contact',
        expectedNavigation: { as: '/contact', locale: undefined },
    },
    {
        name: 'explicit locale overrides converted URL default',
        props: { href: `${origin}/contact`, locale: 'en' },
        currentLocale: 'sk',
        expectedHref: '/en/contact',
        expectedNavigation: { as: '/contact', locale: 'en' },
    },
    {
        name: 'explicit false locale on relative link',
        props: { href: '/contact', locale: false },
        currentLocale: 'sk',
        expectedHref: '/contact',
        expectedNavigation: { as: '/contact', locale: false },
    },
];

describe('ExtendedNextLink SSR and hydration', () => {
    beforeEach(() => {
        vi.stubEnv('__NEXT_I18N_SUPPORT', '1');
    });

    afterEach(() => {
        vi.unstubAllEnvs();
    });

    test.each(cases)('$name produces matching server and client links', async ({
        props,
        expectedHref,
        currentLocale = 'default',
        expectedNavigation,
    }) => {
        const router: NextRouter = {
            basePath: '',
            locale: currentLocale,
            defaultLocale: 'default',
            locales: ['default', 'en', 'cs', 'sk'],
            pathname: '/',
            route: '/',
            query: {},
            asPath: '/',
            isFallback: false,
            isReady: true,
            isPreview: false,
            isLocaleDomain: false,
            push: vi.fn().mockResolvedValue(true),
            replace: vi.fn().mockResolvedValue(true),
            reload: vi.fn(),
            back: vi.fn(),
            forward: vi.fn(),
            prefetch: vi.fn().mockResolvedValue(undefined),
            beforePopState: vi.fn(),
            events: { on: vi.fn(), off: vi.fn(), emit: vi.fn() },
        };
        const element = (
            <RouterContext.Provider value={router}>
                <DomainConfigProvider
                    domainConfig={{
                        ...defaultTestDomainConfig,
                        url: `${origin}/${currentLocale === 'default' ? '' : `${currentLocale}/`}`,
                    }}
                >
                    <ExtendedNextLink {...props}>Link</ExtendedNextLink>
                </DomainConfigProvider>
            </RouterContext.Provider>
        );
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        const onRecoverableError = vi.fn();
        let html: string;

        vi.stubGlobal('window', undefined);
        try {
            html = renderToString(element);
        } finally {
            vi.unstubAllGlobals();
        }

        const container = document.createElement('div');
        container.innerHTML = html;
        document.body.append(container);
        const serverHref = container.querySelector('a')?.getAttribute('href');

        await act(async () => {
            render(element, { container, hydrate: true, onRecoverableError });
        });

        expect(consoleError).not.toHaveBeenCalled();
        expect(onRecoverableError).not.toHaveBeenCalled();
        expect(serverHref).toBe(expectedHref);
        expect(container.querySelector('a')).toHaveAttribute('href', expectedHref);

        if (expectedNavigation) {
            fireEvent.click(container.querySelector('a')!);

            expect(router.push).toHaveBeenCalledWith(
                expect.any(String),
                expectedNavigation.as,
                expect.objectContaining({ locale: expectedNavigation.locale }),
            );
        }
    });
});
