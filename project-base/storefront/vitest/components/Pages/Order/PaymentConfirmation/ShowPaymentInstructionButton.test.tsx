import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import { ShowPaymentInstructionButton } from 'components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton';
import { useEffect } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const mockSetOrderPaymentStatusPageValidityHashMutation = vi.fn();
const mockSaveGoPayPaymentSession = vi.fn();
const mockShouldOpenGoPayAsRedirectOnly = vi.fn();
const mockBuildPaymentConfirmationUrlFromSession = vi.fn();
const mockMarkGoPayPaymentSessionForRedirectOnly = vi.fn();

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (text: string) => text }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://test.example.com', defaultLocale: 'cs' }),
}));

vi.mock('graphql/requests/orders/mutations/SetOrderPaymentStatusPageValidityHashMutation.generated', () => ({
    useSetOrderPaymentStatusPageValidityHashMutation: () => [
        {},
        (...args: unknown[]) => mockSetOrderPaymentStatusPageValidityHashMutation(...args),
    ],
}));

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    buildPaymentConfirmationUrlFromSession: (...args: unknown[]) => mockBuildPaymentConfirmationUrlFromSession(...args),
    markGoPayPaymentSessionForRedirectOnly: (...args: unknown[]) => mockMarkGoPayPaymentSessionForRedirectOnly(...args),
    removeGoPayPaymentSession: vi.fn(),
    saveGoPayPaymentSession: (...args: unknown[]) => mockSaveGoPayPaymentSession(...args),
    shouldOpenGoPayAsRedirectOnly: (...args: unknown[]) => mockShouldOpenGoPayAsRedirectOnly(...args),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: vi.fn(),
}));

vi.mock('next/script', () => ({
    default: function MockScript({ onLoad }: { onLoad?: () => void }) {
        useEffect(() => {
            onLoad?.();
        }, [onLoad]);

        return null;
    },
}));

const SUCCESSFUL_INSTRUCTION_RESPONSE = {
    data: {
        SetOrderPaymentStatusPageValidityHashMutation: {
            goPayEmbedJs: 'https://gopay.example.com/embed.js',
            orderPaymentStatusPageValidityHash: 'validity-hash',
        },
    },
};

describe('ShowPaymentInstructionButton', () => {
    const mockLocationAssign = vi.fn();
    const mockLocationReplace = vi.fn();
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        mockShouldOpenGoPayAsRedirectOnly.mockReturnValue(false);
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
        mockSetOrderPaymentStatusPageValidityHashMutation.mockResolvedValue(SUCCESSFUL_INSTRUCTION_RESPONSE);
        delete (window as any)._gopay;
        Object.defineProperty(window, 'location', {
            value: { ...originalLocation, assign: mockLocationAssign, replace: mockLocationReplace },
            writable: true,
        });
    });

    afterEach(() => {
        Object.defineProperty(window, 'location', {
            value: originalLocation,
            writable: true,
        });
    });

    test('opens GoPay inline on the first payment-instruction attempt', async () => {
        const mockCheckout = vi.fn();
        (window as any)._gopay = { checkout: mockCheckout };

        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));

        await waitFor(() => {
            expect(mockSaveGoPayPaymentSession).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                orderUrlHash: 'order-hash',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'https://test.example.com',
                forceRedirectAfterInlineReturn: false,
            });
            expect(mockCheckout).toHaveBeenCalledWith(
                {
                    gatewayUrl: 'https://gopay.example.com/gateway',
                    inline: true,
                },
                expect.any(Function),
            );
        });
    });

    test('switches to full-page redirect after inline fallback was already activated', async () => {
        mockShouldOpenGoPayAsRedirectOnly.mockReturnValue(true);

        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));

        await waitFor(() => {
            expect(mockSaveGoPayPaymentSession).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                orderUrlHash: 'order-hash',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'https://test.example.com',
                forceRedirectAfterInlineReturn: true,
            });
            expect(mockLocationAssign).toHaveBeenCalledWith('https://gopay.example.com/gateway');
        });
    });

    test('marks session as redirect-only and returns to payment confirmation on browser back from inline GoPay', async () => {
        const mockCheckout = vi.fn();
        (window as any)._gopay = { checkout: mockCheckout };

        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalled();
        });

        fireEvent.popState(window);

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledWith(
            'https://test.example.com',
            'order-uuid',
        );
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
    });

    test('navigates parent to fresh session URL when GoPay callback fires with internal return URL', async () => {
        // Regression for SSP-3808 scenario #3: payment completed via Show payment instruction
        // must reliably bring the user back to /order-payment-confirmation with the FRESH
        // validity hash so useUpdatePaymentStatus → tryEmitPaymentEvent fires ec.payment.
        // GoPay's own navigation uses the stale return_url baked at PayOrder time and may
        // not navigate at all, leaving the user stuck on the InProcess page.
        let callback: ((result: { url?: string }) => void) | undefined;
        const mockCheckout = vi.fn((_: unknown, passedCallback: (result: { url?: string }) => void) => {
            callback = passedCallback;
        });
        (window as any)._gopay = { checkout: mockCheckout };

        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalled();
        });

        // Simulate GoPay returning to the storefront after the user completed payment.
        callback?.({
            url: '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=stale-hash',
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledWith(
            'https://test.example.com',
            'order-uuid',
        );
        // Navigation must use the FRESH validity hash from session, not the stale hash
        // from GoPay's callback URL.
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
    });

    test('does not exit inline flow on browser back before inline checkout becomes active', async () => {
        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        await act(async () => {
            fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));
            fireEvent.popState(window);
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).not.toHaveBeenCalled();
        expect(mockLocationReplace).not.toHaveBeenCalled();
    });

    test('dedupes repeated terminal exits from callback and browser back', async () => {
        let callback: ((result: { url?: string }) => void) | undefined;
        const mockCheckout = vi.fn((_: unknown, passedCallback: (result: { url?: string }) => void) => {
            callback = passedCallback;
        });
        (window as any)._gopay = { checkout: mockCheckout };

        render(
            <ShowPaymentInstructionButton
                href="https://gopay.example.com/gateway"
                orderUrlHash="order-hash"
                orderUuid="order-uuid"
            />,
        );

        fireEvent.click(screen.getByRole('button', { name: 'Show payment instruction' }));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalled();
        });

        callback?.({ url: '/order-payment-confirmation?orderIdentifier=order-uuid' });
        fireEvent.popState(window);

        // First terminal exit (callback with internal URL) marks session and navigates to
        // the FRESH session URL so the page re-mounts and emits ec.payment;
        // the subsequent popstate is a no-op because isPaymentActiveRef was already cleared.
        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledTimes(1);
        expect(mockLocationReplace).toHaveBeenCalledTimes(1);
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
    });
});
