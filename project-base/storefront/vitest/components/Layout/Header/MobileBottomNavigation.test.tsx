import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MobileBottomNavigation } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigation';
import { type ReactNode } from 'react';
import { afterEach, describe, expect, test, vi } from 'vitest';

afterEach(() => {
    vi.unstubAllGlobals();
});

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

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: { items: [] } }),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => false,
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href, skeletonType: _skeletonType, ...props }: any) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
}));

vi.mock('components/Layout/Header/MobileMenu/MobileMenu', () => ({
    MobileMenu: ({ isMenuOpened }: { isMenuOpened: boolean }) => (
        <div data-active={isMenuOpened} data-testid="mobile-menu" />
    ),
}));

vi.mock('components/Layout/Header/Cart/CartCount', () => ({
    CartCount: ({ children }: { children: ReactNode }) => <span>{children}</span>,
}));

vi.mock('components/Layout/Header/MobileBottomNavigation/MobileBottomCartDrawer', () => ({
    MobileBottomCartDrawer: ({ isActive }: { isActive: boolean }) => (
        <div data-active={isActive} data-testid="cart-drawer" />
    ),
}));

vi.mock('components/Layout/Header/MobileBottomNavigation/MobileBottomAccountDrawer', () => ({
    MobileBottomAccountDrawer: ({ isActive }: { isActive: boolean }) => (
        <div data-active={isActive} data-testid="account-drawer" />
    ),
}));

vi.mock('components/Layout/Header/AutocompleteSearch/AutocompleteSearch', () => ({
    AutocompleteSearch: ({ inputRef, onClearEmpty, onSearchSubmit }: any) => (
        <div>
            <input aria-label="Search input" ref={inputRef} />
            <button type="button" onClick={onClearEmpty}>
                Close mocked search
            </button>
            <button type="button" onClick={onSearchSubmit}>
                Submit mocked search
            </button>
        </div>
    ),
}));

describe('MobileBottomNavigation', () => {
    test('mounts panel implementations only after their actions are used', async () => {
        const user = userEvent.setup();

        render(<MobileBottomNavigation />);

        expect(screen.getByRole('navigation', { name: 'Mobile bottom navigation' })).not.toHaveClass('fixed');
        expect(screen.queryByTestId('cart-drawer')).not.toBeInTheDocument();
        expect(screen.queryByTestId('account-drawer')).not.toBeInTheDocument();
        expect(screen.queryByTestId('mobile-menu')).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: /Cart$/ }));

        expect(await screen.findByTestId('cart-drawer')).toHaveAttribute('data-active', 'true');

        await user.click(screen.getByRole('button', { name: 'Menu' }));

        expect(await screen.findByTestId('mobile-menu')).toHaveAttribute('data-active', 'true');
        expect(screen.getByTestId('cart-drawer')).toHaveAttribute('data-active', 'false');

        await user.click(screen.getByRole('button', { name: 'Account' }));

        expect(await screen.findByTestId('account-drawer')).toHaveAttribute('data-active', 'true');
        expect(screen.getByTestId('mobile-menu')).toHaveAttribute('data-active', 'false');
    });

    test('focuses search input while opening and closes overlay with Escape key', async () => {
        const user = userEvent.setup();

        render(<MobileBottomNavigation />);

        const searchButton = screen.getByRole('button', { name: 'Search' });

        expect(screen.queryByLabelText('Search input')).not.toBeInTheDocument();

        fireEvent.click(searchButton);

        expect(screen.getByLabelText('Search input')).toHaveFocus();
        expect(screen.getByRole('button', { name: 'Close search overlay' })).toBeInTheDocument();

        await user.keyboard('{Escape}');

        await waitFor(() => expect(screen.queryByLabelText('Search input')).not.toBeInTheDocument());
        expect(searchButton).toHaveAttribute('aria-expanded', 'false');
    });

    test('closes search overlay after submitting a search', async () => {
        const user = userEvent.setup();

        render(<MobileBottomNavigation />);

        const searchButton = screen.getByRole('button', { name: 'Search' });
        await user.click(searchButton);
        await user.click(screen.getByRole('button', { name: 'Submit mocked search' }));

        expect(screen.queryByLabelText('Search input')).not.toBeInTheDocument();
        expect(searchButton).toHaveAttribute('aria-expanded', 'false');
    });

    test('keeps search input aligned with the visual viewport', async () => {
        const visualViewport = new EventTarget() as EventTarget & { offsetTop: number };
        visualViewport.offsetTop = 40;
        vi.stubGlobal('visualViewport', visualViewport);

        render(<MobileBottomNavigation />);

        fireEvent.click(screen.getByRole('button', { name: 'Search' }));
        const searchContainer = screen.getByLabelText('Search input').closest('.fixed');

        await waitFor(() => expect(searchContainer).toHaveStyle({ transform: 'translateY(40px)' }));

        act(() => {
            visualViewport.offsetTop = 120;
            visualViewport.dispatchEvent(new Event('scroll'));
        });

        await waitFor(() => expect(searchContainer).toHaveStyle({ transform: 'translateY(120px)' }));

        act(() => {
            visualViewport.offsetTop = 0;
            visualViewport.dispatchEvent(new Event('resize'));
        });

        await waitFor(() => expect(searchContainer).toHaveStyle({ transform: 'translateY(0px)' }));
    });
});
