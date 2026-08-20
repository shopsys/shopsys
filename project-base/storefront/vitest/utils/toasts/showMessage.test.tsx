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
});
