import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SubMenu } from 'components/Layout/Header/MobileMenu/MobileMenuSubItems';
import { type MouseEventHandler, type ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: () => ({ comparison: { products: [{ uuid: 'first' }, { uuid: 'second' }] } }),
}));

vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({ wishlist: { products: [{ uuid: 'first' }] } }),
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        href,
        onClick,
        passHref: _passHref,
        type: _type,
        ...props
    }: {
        children: ReactNode;
        href: string;
        onClick: MouseEventHandler<HTMLAnchorElement>;
        passHref?: boolean;
        type?: string;
    }) => (
        <a href={href} onClick={onClick} {...props}>
            {children}
        </a>
    ),
}));

describe('MobileMenuSubItems', () => {
    test('renders icon quick links with counts and no account action', async () => {
        const user = userEvent.setup();
        const onNavigate = vi.fn();

        render(<SubMenu onNavigate={onNavigate} />);

        const storesLink = screen.getByRole('link', { name: 'Stores' });
        const comparisonLink = screen.getByRole('link', { name: 'Comparison (2)' });
        const wishlistLink = screen.getByRole('link', { name: 'Wishlist (1)' });

        expect(screen.getByText('Quick links')).toBeInTheDocument();
        expect(storesLink).toHaveAttribute('href', '/stores');
        expect(comparisonLink).toHaveAttribute('href', '/product-comparison');
        expect(wishlistLink).toHaveAttribute('href', '/wishlist');
        expect(screen.getByText('2')).toHaveAttribute('aria-hidden', 'true');
        expect(screen.getByText('1')).toHaveAttribute('aria-hidden', 'true');
        expect(storesLink.querySelector('svg')).toBeInTheDocument();
        expect(comparisonLink.querySelector('svg')).toBeInTheDocument();
        expect(wishlistLink.querySelector('svg')).toBeInTheDocument();
        expect(screen.queryByText('Sign in')).not.toBeInTheDocument();
        expect(screen.queryByText('Logout')).not.toBeInTheDocument();

        await user.click(storesLink);

        expect(onNavigate).toHaveBeenCalledOnce();
    });
});
