import { cleanup, fireEvent, render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { MenuIconicItemUserUnauthenticated } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticated';
import { MobileBottomNavigationButton } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigationItem';
import type { ComponentProps, ReactNode } from 'react';
import { createFocusManagementSlice, type FocusManagementSlice } from 'store/slices/createFocusManagementSlice';
import { createPortalSlice, type PortalSlice } from 'store/slices/createPortalSlice';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { create } from 'zustand';

const useTestStore = create<PortalSlice & FocusManagementSlice>()((...args) => ({
    ...createPortalSlice(...args),
    ...createFocusManagementSlice(...args),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: PortalSlice & FocusManagementSlice) => unknown) => useTestStore(selector),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/useWindowDimensions', () => {
    const dimensions = { width: 1600, height: 900 };
    return { default: () => dimensions };
});
vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href, onClick }: ComponentProps<'a'>) => (
        <a href={href} onClick={onClick}>
            {children}
        </a>
    ),
}));
vi.mock('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent', () => ({
    MenuIconicItemUserUnauthenticatedContent: ({ onMenuClose }: { onMenuClose: () => void }) => (
        <form aria-label="Account login">
            <label>
                Email
                <input autoComplete="email" />
            </label>
            <label>
                Password
                <input type="password" autoComplete="current-password" />
            </label>
            <button type="button">Login</button>
            <button type="button" onClick={onMenuClose}>
                Register
            </button>
        </form>
    ),
}));
vi.mock('next/dynamic', () => ({
    default:
        () =>
        ({ onClick }: { onClick: () => void }) => (
            <button aria-label="Backdrop" tabIndex={-1} type="button" onClick={onClick} />
        ),
}));
vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: { children: ReactNode }) => children,
    m: {
        div: ({ animate, initial, exit, transition, ...props }: ComponentProps<'div'> & Record<string, unknown>) => (
            <div {...props} />
        ),
    },
}));

const TestPage = ({ isDesktop = true }: { isDesktop?: boolean }) => {
    const portalContent = useTestStore((state) => state.portalContent);
    return (
        <>
            {isDesktop ? (
                <>
                    <MenuIconicItemUserUnauthenticated loginFormName="header-login-form" />
                    <MenuIconicItemUserUnauthenticated loginFormName="fixed-header-login-form" />
                </>
            ) : (
                <ul>
                    <MobileBottomNavigationButton
                        icon={UserIcon}
                        isExpanded={!!portalContent}
                        label="Mobile account"
                        name="account-login"
                        onClick={() => {}}
                    />
                </ul>
            )}
            {portalContent}
        </>
    );
};

describe('Header account dialog', () => {
    beforeEach(() => {
        vi.stubGlobal(
            'ResizeObserver',
            class {
                observe() {}
                disconnect() {}
            },
        );
        useTestStore.setState({ portalContent: null, storedFocusElement: null, storedFocusFallbackSelector: null });
    });
    afterEach(() => {
        cleanup();
        vi.unstubAllGlobals();
    });

    test('opens one dialog on click, ignores hover and keeps it open when the pointer leaves', async () => {
        const user = userEvent.setup();
        render(<TestPage />);
        const trigger = screen.getAllByRole('button', { name: 'Show registration and login popup' })[0];
        await user.hover(trigger);
        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        await user.click(trigger);
        expect(screen.getAllByRole('dialog')).toHaveLength(1);
        expect(trigger).toHaveAttribute('aria-haspopup', 'dialog');
        expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await user.unhover(trigger);
        await user.type(screen.getByLabelText('Email'), 'customer@example.com');
        fireEvent.mouseLeave(screen.getByRole('dialog'));
        expect(screen.getByLabelText('Email')).toHaveValue('customer@example.com');
        expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    test.each(['Close popup', 'Backdrop', 'Escape'])(
        'closes with %s and restores focus to its trigger',
        async (action) => {
            const user = userEvent.setup();
            render(<TestPage />);
            const trigger = screen.getAllByRole('button', { name: 'Show registration and login popup' })[1];
            await user.click(trigger);
            if (action === 'Escape') await user.keyboard('{Escape}');
            else await user.click(screen.getByRole('button', { name: action }));
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
            await waitFor(() => expect(trigger).toHaveFocus());
            expect(trigger).toHaveAttribute('aria-expanded', 'false');
        },
    );

    test('returns focus to the mobile account button when its desktop trigger has unmounted', async () => {
        const user = userEvent.setup();
        const { rerender } = render(<TestPage />);
        await user.click(screen.getAllByRole('button', { name: 'Show registration and login popup' })[1]);

        rerender(<TestPage isDesktop={false} />);
        await user.keyboard('{Escape}');

        await waitFor(() => expect(screen.getByRole('button', { name: 'Mobile account' })).toHaveFocus());
    });

    test.each(['{Enter}', ' '])('opens with %s, moves focus inside and traps both tab directions', async (key) => {
        const user = userEvent.setup();
        render(<TestPage />);
        const trigger = screen.getAllByRole('button', { name: 'Show registration and login popup' })[0];
        trigger.focus();
        await user.keyboard(key);
        const dialog = screen.getByRole('dialog');
        expect(dialog).toContainElement(document.activeElement as HTMLElement);
        const close = within(dialog).getByRole('button', { name: 'Close popup' });
        const register = within(dialog).getByRole('button', { name: 'Register' });
        register.focus();
        await user.tab();
        expect(close).toHaveFocus();
        await user.tab({ shift: true });
        expect(register).toHaveFocus();
    });
});
