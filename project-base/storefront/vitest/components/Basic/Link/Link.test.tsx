import { render } from '@testing-library/react';
import { Link } from 'components/Basic/Link/Link';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('next/router', () => ({
    useRouter: () => ({
        push: vi.fn(),
        prefetch: vi.fn().mockResolvedValue(undefined),
        pathname: '/',
        query: {},
        asPath: '/',
    }),
}));

const renderLink = (props: Parameters<typeof Link>[0]) =>
    render(
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <Link {...props} />
        </DomainConfigProvider>,
    ).container.querySelector('a');

describe('Link', () => {
    test.each([
        [undefined, 'noopener'],
        ['nofollow', 'nofollow noopener'],
        ['noopener noreferrer', 'noopener noreferrer'],
    ])('link opening in a new tab with rel="%s" gets noopener', (rel, expectedRel) => {
        const link = renderLink({ href: '/test-href', rel, target: '_blank', children: 'link text' });

        expect(link).toHaveAttribute('rel', expectedRel);
    });

    test('link without a target keeps rel untouched', () => {
        const link = renderLink({ href: '/test-href', rel: 'nofollow', children: 'link text' });

        expect(link).toHaveAttribute('rel', 'nofollow');
    });

    test.each([
        [undefined, 'noopener'],
        ['nofollow', 'nofollow noopener'],
    ])('external link opening in a new tab with rel="%s" gets noopener', (rel, expectedRel) => {
        const link = renderLink({
            href: 'https://example.com',
            isExternal: true,
            rel,
            target: '_blank',
            children: 'link text',
        });

        expect(link).toHaveAttribute('rel', expectedRel);
    });
});
