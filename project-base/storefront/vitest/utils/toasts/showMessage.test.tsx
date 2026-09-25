import { act, render, screen } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { toast } from 'react-toastify';
import { showMessage } from 'utils/toasts/showMessage';
import { afterEach, describe, expect, test } from 'vitest';

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

    test('announces only the message and hides technical controls', async () => {
        render(<ToastContainerWrapper />);

        act(() => {
            showMessage('Saved', 'success');
        });

        expect(await screen.findByRole('region', { name: 'Notifications' })).toBeInTheDocument();
        expect(screen.getByRole('alert')).toHaveTextContent('Saved');
        expect(screen.queryByRole('button', { name: 'Close' })).not.toBeInTheDocument();
        expect(screen.queryByRole('progressbar')).not.toBeInTheDocument();
        expect(document.querySelector('button[aria-hidden="true"]')).toHaveAttribute('tabindex', '-1');
        expect(document.querySelector('.Toastify__progress-bar')).toHaveAttribute('aria-hidden', 'true');
    });
});
