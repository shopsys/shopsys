import { act, renderHook } from '@testing-library/react';
import { useInlinePaymentBackGuard } from 'components/Pages/Order/PaymentConfirmation/useInlinePaymentBackGuard';
import { useRef } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const mockBuildPaymentConfirmationUrlFromSession = vi.fn();
const mockMarkGoPayPaymentSessionForRedirectOnly = vi.fn();
const domainConfig = { url: 'https://test.example.com', defaultLocale: 'cs' } as DomainConfigType;

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    buildPaymentConfirmationUrlFromSession: (...args: unknown[]) => mockBuildPaymentConfirmationUrlFromSession(...args),
    markGoPayPaymentSessionForRedirectOnly: (...args: unknown[]) => mockMarkGoPayPaymentSessionForRedirectOnly(...args),
}));

describe('useInlinePaymentBackGuard', () => {
    const mockLocationReplace = vi.fn();
    const mockLocationReload = vi.fn();
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );
        Object.defineProperty(window, 'location', {
            value: { ...originalLocation, reload: mockLocationReload, replace: mockLocationReplace },
            writable: true,
        });
    });

    afterEach(() => {
        Object.defineProperty(window, 'location', {
            value: originalLocation,
            writable: true,
        });
    });

    test('pageshow recovery exits only for persisted pageshow with matching session', () => {
        renderHook(() => {
            const isPaymentActiveRef = useRef(true);

            return useInlinePaymentBackGuard({
                domainConfig,
                orderUuid: 'order-uuid',
                isPaymentActiveRef,
            });
        });

        act(() => {
            const event = new Event('pageshow');
            Object.defineProperty(event, 'persisted', { value: true });
            window.dispatchEvent(event);
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledWith(
            'https://test.example.com',
            'order-uuid',
        );
        expect(mockLocationReplace).toHaveBeenCalledWith(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );

        act(() => {
            const event = new Event('pageshow');
            Object.defineProperty(event, 'persisted', { value: false });
            window.dispatchEvent(event);
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledTimes(1);
    });

    test('pageshow recovery does not exit when session for current order is missing', () => {
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(null);

        renderHook(() => {
            const isPaymentActiveRef = useRef(true);

            return useInlinePaymentBackGuard({
                domainConfig,
                orderUuid: 'order-uuid',
                isPaymentActiveRef,
            });
        });

        act(() => {
            const event = new Event('pageshow');
            Object.defineProperty(event, 'persisted', { value: true });
            window.dispatchEvent(event);
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).not.toHaveBeenCalled();
        expect(mockLocationReplace).not.toHaveBeenCalled();
        expect(mockLocationReload).not.toHaveBeenCalled();
    });

    test('dedupes repeated popstate and manual exit handling', () => {
        const { result } = renderHook(() => {
            const isPaymentActiveRef = useRef(true);

            return useInlinePaymentBackGuard({
                domainConfig,
                orderUuid: 'order-uuid',
                isPaymentActiveRef,
            });
        });

        act(() => {
            window.dispatchEvent(new PopStateEvent('popstate'));
            result.current.exitInlineFlow();
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).toHaveBeenCalledTimes(1);
        expect(mockLocationReplace).toHaveBeenCalledTimes(1);
    });

    test('popstate does not exit when payment is not active', () => {
        renderHook(() => {
            const isPaymentActiveRef = useRef(false);

            return useInlinePaymentBackGuard({
                domainConfig,
                orderUuid: 'order-uuid',
                isPaymentActiveRef,
            });
        });

        act(() => {
            window.dispatchEvent(new PopStateEvent('popstate'));
        });

        expect(mockMarkGoPayPaymentSessionForRedirectOnly).not.toHaveBeenCalled();
        expect(mockLocationReplace).not.toHaveBeenCalled();
    });
});
