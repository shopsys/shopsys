import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { ResetPasswordContent } from 'components/Pages/ResetPassword/ResetPasswordContent';
import { CombinedError } from 'urql';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { onGtmSendFormEventHandlerMock, resetPasswordMock } = vi.hoisted(() => ({
    onGtmSendFormEventHandlerMock: vi.fn(),
    resetPasswordMock: vi.fn(),
}));

vi.mock('graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.generated', () => ({
    usePasswordRecoveryMutation: () => [{}, resetPasswordMock],
}));

vi.mock('gtm/handlers/onGtmSendFormEventHandler', () => ({
    onGtmSendFormEventHandler: onGtmSendFormEventHandlerMock,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: vi.fn(),
}));

describe('ResetPasswordContent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        resetPasswordMock.mockResolvedValue({
            data: { RequestPasswordRecovery: 'Password recovery requested' },
            error: undefined,
        });
    });

    test('updates the existing status region after a successful submission', async () => {
        render(<ResetPasswordContent />);

        const statusRegion = screen.getByRole('status');
        fireEvent.change(screen.getByRole('textbox', { name: 'Your email' }), {
            target: { value: 'customer@example.com' },
        });
        fireEvent.click(screen.getByRole('button', { name: 'Reset password' }));

        await waitFor(() => {
            expect(screen.getByRole('heading', { name: 'Check your email' })).toBeInTheDocument();
        });
        expect(screen.getByRole('status')).toBe(statusRegion);
        expect(statusRegion).toHaveTextContent(
            'We have sent password reset instructions to your email address. Please check your inbox and follow the link to create a new password.',
        );
    });

    test('shows the rate limit message instead of a generic one when too many recovery attempts were made', async () => {
        resetPasswordMock.mockResolvedValue({
            data: undefined,
            error: new CombinedError({
                graphQLErrors: [
                    {
                        message: 'Too many password recovery attempts. Try again later.',
                        extensions: { userCode: 'too-many-password-recovery-attempts' },
                    },
                ],
            }),
        });

        render(<ResetPasswordContent />);

        fireEvent.change(screen.getByRole('textbox', { name: 'Your email' }), {
            target: { value: 'customer@example.com' },
        });
        fireEvent.click(screen.getByRole('button', { name: 'Reset password' }));

        await waitFor(() => {
            expect(showErrorMessage).toHaveBeenCalledWith(
                'Too many password recovery attempts. Try again later.',
                expect.anything(),
                expect.objectContaining({ errorType: 'too-many-password-recovery-attempts' }),
            );
        });
    });
});
