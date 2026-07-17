import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MobileBottomNavigation } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigation';
import type { ReactNode } from 'react';
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

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: { items: [] } }),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => false,
}));

vi.mock('components/Basic/Drawer/Drawer', () => ({
    Drawer: ({ children, isActive, title }: { children: ReactNode; isActive: boolean; title?: string }) =>
        isActive ? (
            <div aria-label={title} role="dialog">
                {children}
            </div>
        ) : null,
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href, skeletonType: _skeletonType, ...props }: any) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
}));

vi.mock('components/Layout/Header/MobileMenu/MobileMenu', () => ({
    MobileMenu: () => null,
}));

vi.mock('components/Layout/Header/Cart/CartCount', () => ({
    CartCount: ({ children }: { children: ReactNode }) => <span>{children}</span>,
}));

vi.mock('components/Layout/Header/Cart/CartInHeaderList', () => ({
    CartInHeaderList: () => <div>Cart panel</div>,
}));

vi.mock('components/Blocks/UserMenu/UserMenu', () => ({
    UserMenu: () => <div>User menu</div>,
}));

vi.mock('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent', () => ({
    MenuIconicItemUserUnauthenticatedContent: () => <div>Login links</div>,
}));

vi.mock('components/Layout/Header/AutocompleteSearch/AutocompleteSearch', () => ({
    AutocompleteSearch: ({ inputRef, onClearEmpty }: any) => (
        <div>
            <input aria-label="Search input" ref={inputRef} />
            <button type="button" onClick={onClearEmpty}>
                Close mocked search
            </button>
        </div>
    ),
}));

describe('MobileBottomNavigation', () => {
    test('closes search overlay with Escape key', async () => {
        const user = userEvent.setup();

        render(<MobileBottomNavigation />);

        const searchButton = screen.getByRole('button', { name: 'Search' });
        await user.click(searchButton);

        expect(screen.getByLabelText('Search input')).toHaveFocus();
        expect(screen.getByRole('button', { name: 'Close search overlay' })).toBeInTheDocument();

        await user.keyboard('{Escape}');

        await waitFor(() => expect(screen.queryByLabelText('Search input')).not.toBeInTheDocument());
        expect(searchButton).toHaveAttribute('aria-expanded', 'false');
    });
});
