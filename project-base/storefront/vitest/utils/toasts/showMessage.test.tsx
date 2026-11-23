import { act, fireEvent, render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { toast } from 'react-toastify';
import { showMessage } from 'utils/toasts/showMessage';
import { afterEach, describe, expect, test } from 'vitest';

const renderWithPageButton = () =>
    render(
        <>
            <button type="button">Continue shopping</button>
            <ToastContainerWrapper />
        </>,
    );

// react-toastify starts the timer after the enter animation, which never runs in jsdom
const finishEnterAnimations = () => {
    document.querySelectorAll('.Toastify__toast').forEach((message) => fireEvent.animationEnd(message));
};

const getTimerPlayState = (message: string) =>
    screen.getByText(message).closest('.Toastify__toast')?.querySelector<HTMLElement>('.Toastify__progress-bar')?.style
        .animationPlayState;

describe('showMessage', () => {
    afterEach(() => {
        act(() => {
            toast.dismiss();
        });
    });

    test('renders an HTML-like message as text', async () => {
        const maliciousMessage = '<img src=x onerror="alert(1)">';
        render(<ToastContainerWrapper />);

        act(() => {
            showMessage(maliciousMessage, 'info');
        });

        expect(await screen.findByText(maliciousMessage)).toBeInTheDocument();
        expect(document.querySelector('img')).not.toBeInTheDocument();
    });

    test.each(['success', 'info', 'error'] as const)(
        'renders a %s message inside the single notifications live region',
        async (type) => {
            render(<ToastContainerWrapper />);

            act(() => {
                showMessage('Saved', type);
            });

            const notifications = await screen.findByRole('region', { name: 'Notifications (Alt+T)' });
            expect(notifications).toHaveAttribute('aria-live', 'polite');
            expect(within(notifications).getByText('Saved')).toBeInTheDocument();
            expect(notifications.querySelector('[role="status"], [role="alert"], [aria-live]')).toBeNull();
            expect(screen.queryByRole('progressbar')).not.toBeInTheDocument();
            expect(document.querySelector('.Toastify__progress-bar')).toHaveAttribute('aria-hidden', 'true');
        },
    );

    test('closes a message from the keyboard and returns the focus to the page', async () => {
        const user = userEvent.setup();
        renderWithPageButton();

        act(() => {
            showMessage('Saved', 'success');
        });

        const pageButton = screen.getByRole('button', { name: 'Continue shopping' });
        const closeButton = await screen.findByRole('button', { name: 'Close' });
        pageButton.focus();
        await user.keyboard('{Alt>}[KeyT]{/Alt}');
        await user.tab();
        expect(closeButton).toHaveFocus();

        await user.keyboard('{Enter}');

        expect(toast.isActive('Saved')).toBe(false);
        expect(pageButton).toHaveFocus();
    });

    test('returns the focus to the page when a focused message is dismissed by code', async () => {
        const user = userEvent.setup();
        renderWithPageButton();

        act(() => {
            showMessage('Saved', 'success');
        });

        await screen.findByText('Saved');
        const pageButton = screen.getByRole('button', { name: 'Continue shopping' });
        pageButton.focus();
        await user.keyboard('{Alt>}[KeyT]{/Alt}');
        expect(document.activeElement?.closest('.Toastify__toast')).toHaveTextContent('Saved');

        act(() => {
            toast.dismiss('Saved');
        });

        expect(pageButton).toHaveFocus();
    });

    test('resumes the other messages paused by Alt+T after a message is closed', async () => {
        const user = userEvent.setup();
        renderWithPageButton();

        act(() => {
            showMessage('Saved', 'success');
            showMessage('Added to comparison', 'info');
        });

        await screen.findByText('Added to comparison');
        finishEnterAnimations();
        screen.getByRole('button', { name: 'Continue shopping' }).focus();
        await user.keyboard('{Alt>}[KeyT]{/Alt}');
        expect(getTimerPlayState('Added to comparison')).toBe('paused');

        await user.tab();
        await user.keyboard('{Enter}');

        expect(toast.isActive('Saved')).toBe(false);
        expect(getTimerPlayState('Added to comparison')).toBe('running');
    });

    test('keeps a message open when its text is clicked', async () => {
        const user = userEvent.setup();
        render(<ToastContainerWrapper />);

        act(() => {
            showMessage('Saved', 'info');
        });

        await user.click(await screen.findByText('Saved'));

        expect(toast.isActive('Saved')).toBe(true);
    });
});
