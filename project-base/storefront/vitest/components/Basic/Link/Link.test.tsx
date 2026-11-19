import { fireEvent, render } from '@testing-library/react';
import { Link } from 'components/Basic/Link/Link';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import type { MouseEvent } from 'react';
import { useSessionStore } from 'store/useSessionStore';
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
    ).getByRole('link');

describe('Link', () => {
    test.each([false, true])('preserves native link attributes and events (external: %s)', (isExternal) => {
        const onClick = vi.fn((event: MouseEvent<HTMLAnchorElement>) => event.preventDefault());
        const link = renderLink({
            href: isExternal ? 'https://example.com' : '/test-href',
            isExternal,
            children: 'link text',
            'aria-label': 'Read the privacy policy',
            'aria-describedby': 'privacy-description',
            title: 'Privacy policy',
            onClick,
        });

        expect(link).toHaveAccessibleName('Read the privacy policy');
        expect(link).toHaveAttribute('aria-describedby', 'privacy-description');
        expect(link).toHaveAttribute('title', 'Privacy policy');

        fireEvent.click(link);

        expect(onClick).toHaveBeenCalledOnce();
    });

    test('internal link cancelled by its click handler does not start page loading', () => {
        useSessionStore.setState({ isPageLoading: false });
        const link = renderLink({
            href: '/test-href',
            children: 'link text',
            onClick: (event) => event.preventDefault(),
        });

        fireEvent.click(link);

        expect(useSessionStore.getState().isPageLoading).toBe(false);
    });

    test('internal download link does not start page loading', () => {
        useSessionStore.setState({ isPageLoading: false });
        const link = renderLink({ href: '/files/manual.pdf', children: 'Download manual', download: true });

        fireEvent.click(link);

        expect(useSessionStore.getState().isPageLoading).toBe(false);
    });

    test.each([
        ['Shift', { shiftKey: true }],
        ['Alt', { altKey: true }],
        ['Ctrl', { ctrlKey: true }],
        ['Meta', { metaKey: true }],
    ])('internal link clicked with %s opens elsewhere and does not start page loading', (_, modifierKey) => {
        useSessionStore.setState({ isPageLoading: false });
        const onClick = vi.fn();
        const link = renderLink({ href: '/test-href', children: 'link text', onClick });

        fireEvent.click(link, modifierKey);

        expect(onClick).toHaveBeenCalledOnce();
        expect(useSessionStore.getState().isPageLoading).toBe(false);
    });

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
