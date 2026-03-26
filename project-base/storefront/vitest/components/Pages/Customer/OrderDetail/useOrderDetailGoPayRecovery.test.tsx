import { renderHook, waitFor } from '@testing-library/react';
import { useOrderDetailGoPayRecovery } from 'components/Pages/Customer/OrderDetail/useOrderDetailGoPayRecovery';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockBuildPaymentConfirmationUrlFromSession = vi.fn();
const domainConfig = { url: 'https://test.example.com', defaultLocale: 'cs' } as DomainConfigType;

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    buildPaymentConfirmationUrlFromSession: (...args: unknown[]) => mockBuildPaymentConfirmationUrlFromSession(...args),
}));

describe('useOrderDetailGoPayRecovery', () => {
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        Object.defineProperty(window, 'location', {
            value: {
                ...originalLocation,
                href: 'https://test.example.com/customer/order-detail?orderNumber=1234567890',
            },
            writable: true,
        });
    });

    test('starts recovery and redirects to payment confirmation on back_forward navigation with matching session', async () => {
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
        vi.spyOn(window.performance, 'getEntriesByType').mockReturnValue([
            { type: 'back_forward' } as PerformanceNavigationTiming,
        ]);

        const { result } = renderHook(() => useOrderDetailGoPayRecovery(domainConfig, 'order-uuid'));

        await waitFor(() => {
            expect(result.current).toBe(true);
            expect(window.location.href).toBe(
                '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
            );
        });
    });

    test('does not recover after explicit navigation to order detail', async () => {
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
        vi.spyOn(window.performance, 'getEntriesByType').mockReturnValue([
            { type: 'navigate' } as PerformanceNavigationTiming,
        ]);

        const { result } = renderHook(() => useOrderDetailGoPayRecovery(domainConfig, 'order-uuid'));

        await waitFor(() => {
            expect(result.current).toBe(false);
        });

        expect(window.location.href).toBe('https://test.example.com/customer/order-detail?orderNumber=1234567890');
    });
});
