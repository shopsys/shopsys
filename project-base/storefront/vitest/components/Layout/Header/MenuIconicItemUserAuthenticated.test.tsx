import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MenuIconicItemUserAuthenticated } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserAuthenticated';
import { MobileBottomAccountDrawer } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomAccountDrawer';
import type { ComponentProps, ReactNode } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { create } from 'zustand';

const { media, userMenuMock } = vi.hoisted(() => ({ media: { isDesktop: true }, userMenuMock: vi.fn() }));
const useTestStore = create<{ isUserMenuOpen: boolean; setIsUserMenuOpen: (value: boolean) => void }>((set) => ({
    isUserMenuOpen: false,
    setIsUserMenuOpen: (isUserMenuOpen) => set({ isUserMenuOpen }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: ReturnType<typeof useTestStore.getState>) => unknown) => useTestStore(selector),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/ui/useMediaMin', () => ({ useMediaMin: () => media.isDesktop }));
vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href, onClick }: ComponentProps<'a'>) => (
        <a href={href} onClick={onClick}>
            {children}
        </a>
    ),
}));
vi.mock('components/Blocks/UserMenu/UserMenu', () => ({
    UserMenu: (props: { onMenuClose?: () => void; hideFocusTrap?: boolean }) => {
        userMenuMock(props);
        return (
            <nav aria-label="Account links">
                <p>Signed in as Customer</p>
                <a
                    href="/customer/orders"
                    onClick={(event) => {
                        event.preventDefault();
                        props.onMenuClose?.();
                    }}
                >
                    My orders
                </a>
                <button type="button" onClick={props.onMenuClose}>
                    Logout
                </button>
            </nav>
        );
    },
}));
vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: { children: ReactNode }) => children,
    m: {
        div: ({
            animate,
            initial,
            exit,
            transition,
            variants,
            ...props
        }: ComponentProps<'div'> & Record<string, unknown>) => <div {...props} />,
    },
}));

const TestPage = ({ withFixedHeader = false }: { withFixedHeader?: boolean }) => (
    <>
        <header tabIndex={-1}>
            <MenuIconicItemUserAuthenticated />
            {withFixedHeader && <MenuIconicItemUserAuthenticated shouldUseLocalUserMenuState />}
        </header>
        <button type="button">Outside</button>
    </>
);

describe('Authenticated account disclosure', () => {
    beforeEach(() => {
        media.isDesktop = true;
        useTestStore.setState({ isUserMenuOpen: false });
    });

    test('opens with an overlay, remains open after pointer leaves, and toggles closed', async () => {
        const user = userEvent.setup();
        render(<TestPage />);
        const trigger = screen.getByRole('button', { name: 'Show logged in user popup' });
        await user.hover(trigger);
        expect(screen.queryByRole('navigation', { name: 'Account links' })).not.toBeInTheDocument();
        await user.click(trigger);
        expect(trigger).toHaveAttribute('aria-expanded', 'true');
        expect(screen.getByRole('link', { name: 'My orders' })).toBeVisible();
        expect(document.querySelector('[data-tid="overlay"]')).toBeVisible();
        await user.unhover(trigger);
        await user.click(screen.getByText('Signed in as Customer'));
        expect(screen.getByRole('link', { name: 'My orders' })).toBeVisible();
        await user.click(trigger);
        expect(screen.queryByRole('navigation', { name: 'Account links' })).not.toBeInTheDocument();
        expect(document.querySelector('[data-tid="overlay"]')).not.toBeInTheDocument();
        await user.click(trigger);
        await user.click(document.querySelector<HTMLElement>('[data-tid="overlay"]')!);
        expect(trigger).toHaveAttribute('aria-expanded', 'false');
        expect(document.querySelector('[data-tid="overlay"]')).not.toBeInTheDocument();
    });

    test.each(['{Enter}', ' '])('opens with %s and Escape restores the trigger', async (key) => {
        const user = userEvent.setup();
        render(<TestPage />);
        const trigger = screen.getByRole('button', { name: 'Show logged in user popup' });
        trigger.focus();
        await user.keyboard(key);
        await user.tab();
        expect(screen.getByRole('link', { name: 'My orders' })).toHaveFocus();
        await user.click(screen.getByText('Signed in as Customer'));
        await user.keyboard('{Escape}');
        expect(screen.queryByRole('navigation', { name: 'Account links' })).not.toBeInTheDocument();
        expect(trigger).toHaveFocus();
    });

    test('closes on outside click, tabbing away, and selecting a destination', async () => {
        const user = userEvent.setup();
        render(<TestPage />);
        const trigger = screen.getByRole('button', { name: 'Show logged in user popup' });
        await user.click(trigger);
        await user.click(screen.getByRole('button', { name: 'Outside' }));
        expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await user.click(trigger);
        await user.tab();
        await user.tab();
        await user.tab();
        expect(screen.getByRole('button', { name: 'Outside' })).toHaveFocus();
        expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await user.click(trigger);
        await user.click(screen.getByRole('link', { name: 'My orders' }));
        expect(trigger).toHaveAttribute('aria-expanded', 'false');
        expect(userMenuMock).toHaveBeenLastCalledWith(expect.objectContaining({ hideFocusTrap: true }));
    });

    test('switches between main and fixed header menus without opening two panels', async () => {
        const user = userEvent.setup();
        render(<TestPage withFixedHeader />);
        const triggers = screen.getAllByRole('button', { name: 'Show logged in user popup' });
        await user.click(triggers[0]);
        await user.click(triggers[1]);
        expect(triggers[0]).toHaveAttribute('aria-expanded', 'false');
        expect(triggers[1]).toHaveAttribute('aria-expanded', 'true');
        expect(screen.getAllByRole('navigation', { name: 'Account links' })).toHaveLength(1);
    });

    test('closes the mobile account drawer when a destination is selected', async () => {
        media.isDesktop = false;
        const setIsActive = vi.fn();
        const user = userEvent.setup();
        render(<MobileBottomAccountDrawer isActive setIsActive={setIsActive} title="Account" />);
        await screen.findByRole('dialog', { name: 'Account' });
        await waitFor(() => expect(screen.getByTitle('Close')).toHaveFocus());
        await user.click(screen.getByRole('link', { name: 'My orders' }));
        expect(setIsActive).toHaveBeenCalledWith(false);
    });
});
