import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useEffect } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const mockPayOrder = vi.fn();
const mockRouterReplace = vi.fn();
const mockSaveGtmPendingPayment = vi.fn();
const mockRemoveGtmPendingPayment = vi.fn();
const mockSaveGoPayPaymentSession = vi.fn();
const mockBuildPaymentConfirmationUrlFromSession = vi.fn();
const mockMarkGoPayPaymentSessionForRedirectOnly = vi.fn();
const mockShouldOpenGoPayAsRedirectOnly = vi.fn();
const mockShowErrorMessage = vi.fn();
let shouldTriggerScriptLoad = false;
let shouldTriggerScriptError = false;

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (text: string) => text }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://test.example.com', defaultLocale: 'cs' }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({
        query: {},
        pathname: '/order-confirmation',
        replace: (...args: unknown[]) => mockRouterReplace(...args),
    }),
}));

vi.mock('next/script', () => ({
    default: function MockScript({ onError, onLoad }: { onError?: () => void; onLoad?: () => void }) {
        useEffect(() => {
            if (shouldTriggerScriptError) {
                onError?.();
            }
            if (shouldTriggerScriptLoad) {
                onLoad?.();
            }
        }, [onError, onLoad]);

        return null;
    },
}));

vi.mock('graphql/requests/orders/mutations/PayOrderMutation.generated', () => ({
    usePayOrderMutation: () => [{}, (...args: unknown[]) => mockPayOrder(...args)],
}));

vi.mock('utils/errors/friendlyErrorMessageParser', () => ({
    getUserFriendlyErrors: (error: unknown) => ({
        applicationError: (error as any)?.__testApplicationError as { type: string; message: string } | undefined,
    }),
}));

vi.mock('gtm/utils/gtmPaymentEventLocalStorage', () => ({
    saveGtmPendingPaymentInLocalStorage: (...args: unknown[]) => mockSaveGtmPendingPayment(...args),
    removeGtmPendingPaymentFromLocalStorage: () => mockRemoveGtmPendingPayment(),
}));

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    saveGoPayPaymentSession: (...args: unknown[]) => mockSaveGoPayPaymentSession(...args),
    buildPaymentConfirmationUrlFromSession: (...args: unknown[]) => mockBuildPaymentConfirmationUrlFromSession(...args),
    markGoPayPaymentSessionForRedirectOnly: (...args: unknown[]) => mockMarkGoPayPaymentSessionForRedirectOnly(...args),
    removeGoPayPaymentSession: vi.fn(),
    shouldOpenGoPayAsRedirectOnly: (...args: unknown[]) => mockShouldOpenGoPayAsRedirectOnly(...args),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: (...args: unknown[]) => mockShowErrorMessage(...args),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: unknown[]) =>
        urls.map((url) => (typeof url === 'string' ? url : '/order-detail/mock')),
}));

const SUCCESSFUL_PAY_ORDER_RESPONSE = {
    data: {
        PayOrder: {
            goPayCreatePaymentSetup: {
                embedJs: 'https://gopay.example.com/embed.js',
                gatewayUrl: 'https://gopay.example.com/gateway',
                goPayId: '123456',
            },
            orderPaymentStatusPageValidityHash: 'validity-hash',
        },
    },
};

class ResizeObserverMock {
    // eslint-disable-next-line no-empty-function
    observe() {}

    // eslint-disable-next-line no-empty-function
    unobserve(): void {}

    // eslint-disable-next-line no-empty-function
    disconnect() {}
}

vi.stubGlobal('ResizeObserver', ResizeObserverMock);

describe('GoPayGateway', () => {
    const mockLocationAssign = vi.fn();
    const mockLocationReplace = vi.fn();
    const mockLocationReload = vi.fn();
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        mockRouterReplace.mockResolvedValue(true);
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
        mockShouldOpenGoPayAsRedirectOnly.mockReturnValue(false);
        shouldTriggerScriptLoad = false;
        shouldTriggerScriptError = false;
        delete (window as any)._gopay;
        Object.defineProperty(window, 'location', {
            value: {
                ...originalLocation,
                assign: mockLocationAssign,
                reload: mockLocationReload,
                replace: mockLocationReplace,
            },
            writable: true,
        });
    });

    afterEach(() => {
        window.history.replaceState({}, '', '/');
        Object.defineProperty(window, 'location', {
            value: originalLocation,
            writable: true,
        });
    });

    test('saves session and pending payment data after successful PayOrder', async () => {
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(
            <GoPayGateway
                requiresAction
                initialButtonText="Pay"
                orderNumber="12345"
                orderUrlHash="url-hash"
                orderUuid="order-uuid"
                paymentName="GoPay"
                paymentTransactionsCount={0}
            />,
        );

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockSaveGoPayPaymentSession).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                orderUrlHash: 'url-hash',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'https://test.example.com',
                forceRedirectAfterInlineReturn: false,
            });
        });

        expect(mockSaveGtmPendingPayment).toHaveBeenCalledWith({
            orderUuid: 'order-uuid',
            orderNumber: '12345',
            paymentName: 'GoPay',
            paymentTransactionsCount: 1,
            domainUrl: 'https://test.example.com',
        });
    });

    test('resets loading state and notifies parent on max transaction count', async () => {
        const mockOnMaxReached = vi.fn();
        mockPayOrder.mockResolvedValue({
            error: { __testApplicationError: { type: 'max-transaction-count-reached', message: 'Max reached' } },
        });

        render(
            <GoPayGateway
                requiresAction
                initialButtonText="Pay"
                orderUuid="order-uuid"
                onMaxTransactionCountReached={mockOnMaxReached}
            />,
        );

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockOnMaxReached).toHaveBeenCalled();
        });

        expect(mockShowErrorMessage).not.toHaveBeenCalled();
        expect(screen.getByText('Pay')).not.toBeDisabled();
    });

    test('shows error toast on generic PayOrder error', async () => {
        mockPayOrder.mockResolvedValue({
            error: { __testApplicationError: { type: 'generic', message: 'Something went wrong' } },
        });

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockShowErrorMessage).toHaveBeenCalledWith('Something went wrong');
        });
    });

    test('shows error toast and cleans up pending payment when embed.js fails to load', async () => {
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);
        shouldTriggerScriptError = true;

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockRemoveGtmPendingPayment).toHaveBeenCalled();
            expect(mockShowErrorMessage).toHaveBeenCalled();
        });
    });

    test('shows preparation overlay during loading to prevent double-click', async () => {
        let resolvePayOrder: (value: unknown) => void;
        mockPayOrder.mockReturnValue(new Promise((resolve) => (resolvePayOrder = resolve)));

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(screen.getByText('Preparing your payment...')).toBeInTheDocument();
        });

        await act(async () => {
            resolvePayOrder!(SUCCESSFUL_PAY_ORDER_RESPONSE);
        });
    });

    test('auto-triggers PayOrder when requiresAction is not set', async () => {
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway orderUuid="order-uuid" />);

        await waitFor(() => {
            expect(mockPayOrder).toHaveBeenCalledWith({ orderUuid: 'order-uuid' });
        });
    });

    test('does not auto-trigger when requiresAction is set', () => {
        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        expect(mockPayOrder).not.toHaveBeenCalled();
    });

    test('calls GoPay checkout with inline:true when _gopay is already available', async () => {
        const mockCheckout = vi.fn();
        (window as any)._gopay = { checkout: mockCheckout };
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalledWith(
                {
                    gatewayUrl: 'https://gopay.example.com/gateway',
                    inline: true,
                },
                expect.any(Function),
            );
        });
    });

    test('redirects to full-page GoPay after inline return fallback is activated', async () => {
        mockShouldOpenGoPayAsRedirectOnly.mockReturnValue(true);
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockSaveGoPayPaymentSession).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                orderUrlHash: undefined,
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
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalled();
        });

        act(() => {
            window.dispatchEvent(new PopStateEvent('popstate'));
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledWith(
            'https://test.example.com',
            'order-uuid',
        );
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
    });

    test('navigates parent to fresh session URL when GoPay callback fires with internal return URL', async () => {
        const mockCheckout = vi.fn((_: unknown, callback: (result: { url?: string }) => void) => {
            callback({ url: '/order-payment-confirmation?orderIdentifier=order-uuid' });
        });
        (window as any)._gopay = { checkout: mockCheckout };
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledWith(
                'https://test.example.com',
                'order-uuid',
            );
        });

        // We always navigate ourselves to the FRESH session URL — GoPay's own navigation
        // (when it happens) uses the stale return_url baked at PayOrder time and skips
        // the validity window refresh that triggers ec.payment emission.
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
    });

    test('does not exit inline flow on browser back before inline checkout becomes active', async () => {
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        await act(async () => {
            fireEvent.click(screen.getByText('Pay'));
            window.dispatchEvent(new PopStateEvent('popstate'));
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
        mockPayOrder.mockResolvedValue(SUCCESSFUL_PAY_ORDER_RESPONSE);

        render(<GoPayGateway requiresAction initialButtonText="Pay" orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Pay'));

        await waitFor(() => {
            expect(mockCheckout).toHaveBeenCalled();
        });

        act(() => {
            callback?.({ url: '/order-payment-confirmation?orderIdentifier=order-uuid' });
            window.dispatchEvent(new PopStateEvent('popstate'));
        });

        // First terminal exit (callback with internal URL) marks session and navigates;
        // popstate after that is a no-op because isPaymentActiveRef was already cleared.
        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledTimes(1);
        expect(mockLocationReplace).toHaveBeenCalledTimes(1);
    });
});
