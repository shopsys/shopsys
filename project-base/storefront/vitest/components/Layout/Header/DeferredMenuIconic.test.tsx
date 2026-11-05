import { render, screen } from '@testing-library/react';
import { DeferredMenuIconic } from 'components/Layout/Header/MenuIconic/DeferredMenuIconic';
import { createFocusManagementSlice } from 'store/slices/createFocusManagementSlice';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { create } from 'zustand';

const { deferred } = vi.hoisted(() => ({ deferred: { shouldRender: true, isUserLoggedIn: false } }));

vi.mock('utils/useDeferredRender', () => ({ useDeferredRender: () => deferred.shouldRender }));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => deferred.isUserLoggedIn }));
vi.mock('next/dynamic', () => ({
    default: () => () => <button type="button">Account</button>,
}));
vi.mock('components/Layout/Header/MenuIconic/MenuIconicPlaceholder', () => ({
    MenuIconicPlaceholder: () => <span>Loading account menu</span>,
}));

describe('Deferred header account menu', () => {
    beforeEach(() => {
        deferred.shouldRender = true;
        deferred.isUserLoggedIn = false;
    });

    test('restores stored focus after a mobile and desktop resize', () => {
        const focusStore = create(createFocusManagementSlice);
        const { rerender } = render(<DeferredMenuIconic isDesktop />);
        screen.getByRole('button', { name: 'Account' }).focus();
        focusStore.getState().storeCurrentFocus();

        rerender(<DeferredMenuIconic isDesktop={false} />);
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }
        rerender(<DeferredMenuIconic isDesktop />);
        focusStore.getState().restoreStoredFocus();

        expect(screen.getByRole('button', { name: 'Account' })).toHaveFocus();
    });

    test('defers the menu until both rendering and the desktop viewport are ready', () => {
        deferred.shouldRender = false;
        const { rerender } = render(<DeferredMenuIconic isDesktop />);
        expect(screen.queryByRole('button', { name: 'Account' })).not.toBeInTheDocument();

        deferred.shouldRender = true;
        rerender(<DeferredMenuIconic isDesktop={false} />);
        expect(screen.queryByRole('button', { name: 'Account' })).not.toBeInTheDocument();

        rerender(<DeferredMenuIconic isDesktop />);
        expect(screen.getByRole('button', { name: 'Account' })).toBeInTheDocument();
    });

    test('unmounts the authenticated desktop menu on mobile', () => {
        deferred.isUserLoggedIn = true;
        const { rerender } = render(<DeferredMenuIconic isDesktop />);
        expect(screen.getByRole('button', { name: 'Account' })).toBeInTheDocument();

        rerender(<DeferredMenuIconic isDesktop={false} />);

        expect(screen.queryByRole('button', { name: 'Account' })).not.toBeInTheDocument();
    });
});
