import { act, renderHook } from '@testing-library/react';
import { useChangePaymentInOrder } from 'components/PaymentsInOrderSelect/paymentInOrderSelectUtils';
import { TypePaymentTypeEnum } from 'graphql/types';
import { SkeletonEnum } from 'types/skeletons';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const mockChangePaymentInOrderMutation = vi.fn();
const mockOnGtmPaymentTryEventHandler = vi.fn();
const mockShowErrorMessage = vi.fn();
const mockShowSuccessMessage = vi.fn();
const mockGetIsPaymentWithPaymentGate = vi.fn();
const mockUpdatePageLoadingState = vi.fn();

let mockIsUserLoggedIn = false;
let mockDomainUrl = 'http://127.0.0.1:8000';
let mockDomainDefaultLocale = 'cs';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (text: string) => text,
    }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({}),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => mockIsUserLoggedIn,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({
        url: mockDomainUrl,
        defaultLocale: mockDomainDefaultLocale,
    }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (store: { updatePageLoadingState: typeof mockUpdatePageLoadingState }) => unknown) =>
        selector({ updatePageLoadingState: mockUpdatePageLoadingState }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/order-detail/', '/customer/order-detail'],
}));

vi.mock('gtm/handlers/onGtmPaymentEventHandler', () => ({
    onGtmPaymentTryEventHandler: (...args: unknown[]) => mockOnGtmPaymentTryEventHandler(...args),
}));

vi.mock('utils/mappers/payment', () => ({
    getIsPaymentWithPaymentGate: (...args: unknown[]) => mockGetIsPaymentWithPaymentGate(...args),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: (...args: unknown[]) => mockShowErrorMessage(...args),
}));

vi.mock('utils/toasts/showSuccessMessage', () => ({
    showSuccessMessage: (...args: unknown[]) => mockShowSuccessMessage(...args),
}));

vi.mock('graphql/requests/orders/mutations/ChangePaymentInOrderMutation.generated', () => ({
    useChangePaymentInOrderMutation: () => [
        { fetching: false },
        (...args: unknown[]) => mockChangePaymentInOrderMutation(...args),
    ],
}));

const createMutationData = (overrides: Partial<Record<string, unknown>> = {}) => ({
    ChangePaymentInOrder: {
        number: '1234567890',
        urlHash: 'order-hash',
        paymentTransactionsCount: 3,
        ...overrides,
    },
});

describe('useChangePaymentInOrder', () => {
    const mockLocationAssign = vi.fn();
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        mockIsUserLoggedIn = false;
        mockDomainUrl = 'http://127.0.0.1:8000';
        mockDomainDefaultLocale = 'cs';
        Object.defineProperty(window, 'location', {
            value: { ...originalLocation, assign: mockLocationAssign },
            writable: true,
        });
    });

    afterEach(() => {
        Object.defineProperty(window, 'location', {
            value: originalLocation,
            writable: true,
        });
    });

    test('uses customer order detail redirect and non-gateway retry count for logged in user', async () => {
        mockIsUserLoggedIn = true;
        mockGetIsPaymentWithPaymentGate.mockReturnValue(false);
        mockChangePaymentInOrderMutation.mockResolvedValue({
            data: createMutationData(),
        });

        const { result } = renderHook(() => useChangePaymentInOrder());

        await act(async () => {
            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'payment-uuid',
                'Bank Transfer',
                TypePaymentTypeEnum.Basic,
            );
        });

        expect(mockShowSuccessMessage).toHaveBeenCalledWith('Your payment has been successfully changed');
        expect(mockOnGtmPaymentTryEventHandler).toHaveBeenCalledWith('1234567890', 'Bank Transfer', true, undefined, 3);
        expect(mockUpdatePageLoadingState).toHaveBeenCalledWith({
            isPageLoading: true,
            redirectPageType: SkeletonEnum.OrderDetail,
        });
        expect(mockLocationAssign).toHaveBeenCalledWith('/customer/order-detail?orderNumber=1234567890');
    });

    test('keeps locale prefix in redirect URL for locale-path domain', async () => {
        mockIsUserLoggedIn = true;
        mockDomainUrl = 'http://127.0.0.1:8000/sk';
        mockDomainDefaultLocale = 'sk';
        mockGetIsPaymentWithPaymentGate.mockReturnValue(false);
        mockChangePaymentInOrderMutation.mockResolvedValue({
            data: createMutationData(),
        });

        const { result } = renderHook(() => useChangePaymentInOrder());

        await act(async () => {
            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'payment-uuid',
                'Bank Transfer',
                TypePaymentTypeEnum.Basic,
            );
        });

        expect(mockLocationAssign).toHaveBeenCalledWith('/sk/customer/order-detail?orderNumber=1234567890');
    });

    test('uses public order detail redirect and gateway retry count for anonymous user', async () => {
        mockIsUserLoggedIn = false;
        mockGetIsPaymentWithPaymentGate.mockReturnValue(true);
        mockChangePaymentInOrderMutation.mockResolvedValue({
            data: createMutationData({
                paymentTransactionsCount: 3,
            }),
        });

        const { result } = renderHook(() => useChangePaymentInOrder());

        await act(async () => {
            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'payment-uuid',
                'GoPay',
                TypePaymentTypeEnum.GoPay,
            );
        });

        expect(mockOnGtmPaymentTryEventHandler).toHaveBeenCalledWith('1234567890', 'GoPay', true, undefined, 2);
        expect(mockUpdatePageLoadingState).toHaveBeenCalledWith({
            isPageLoading: true,
            redirectPageType: SkeletonEnum.OrderDetailPublic,
        });
        expect(mockLocationAssign).toHaveBeenCalledWith('/order-detail/order-hash');
    });

    test('does not redirect or emit GTM payment try event when redirect is disabled', async () => {
        mockGetIsPaymentWithPaymentGate.mockReturnValue(false);
        mockChangePaymentInOrderMutation.mockResolvedValue({
            data: createMutationData(),
        });

        const { result } = renderHook(() => useChangePaymentInOrder());

        await act(async () => {
            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'payment-uuid',
                'Bank Transfer',
                TypePaymentTypeEnum.Basic,
                null,
                false,
            );
        });

        expect(mockLocationAssign).not.toHaveBeenCalled();
        expect(mockOnGtmPaymentTryEventHandler).not.toHaveBeenCalled();
        expect(mockUpdatePageLoadingState).not.toHaveBeenCalled();
    });

    test('shows error message and exits when mutation returns no edited order', async () => {
        mockChangePaymentInOrderMutation.mockResolvedValue({
            data: {
                ChangePaymentInOrder: null,
            },
        });

        const { result } = renderHook(() => useChangePaymentInOrder());

        await act(async () => {
            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'payment-uuid',
                'Any payment',
                TypePaymentTypeEnum.Basic,
            );
        });

        expect(mockShowErrorMessage).toHaveBeenCalledWith('An error occurred while changing the payment');
        expect(mockShowSuccessMessage).not.toHaveBeenCalled();
        expect(mockLocationAssign).not.toHaveBeenCalled();
        expect(mockOnGtmPaymentTryEventHandler).not.toHaveBeenCalled();
        expect(mockUpdatePageLoadingState).not.toHaveBeenCalled();
    });
});
