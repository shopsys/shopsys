import { renderHook } from '@testing-library/react';
import { useChangePaymentInOrder } from 'components/PaymentsInOrderSelect/paymentInOrderSelectUtils';
import { getValidOrderConfirmationContext } from 'utils/order/orderConfirmationContextStorage';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { changePaymentMock, domainMock, loggedInMock, paymentEventMock, routerMock, errorMock, successMock } =
    vi.hoisted(() => ({
        changePaymentMock: vi.fn(),
        domainMock: vi.fn(),
        loggedInMock: vi.fn(),
        paymentEventMock: vi.fn(),
        routerMock: { asPath: '', push: vi.fn(), reload: vi.fn() },
        errorMock: vi.fn(),
        successMock: vi.fn(),
    }));

vi.mock('config/staticRewritePaths', () => ({
    STATIC_REWRITE_PATHS: {
        'https://test1.example.com/': {
            '/customer/order-detail': '/customer/order-detail',
            '/order-confirmation': '/order-confirmation',
            '/order-detail/:urlHash': '/order-detail/:urlHash',
        },
        'https://test2.example.com/': {
            '/customer/order-detail': '/zakaznik/detail-objednavky',
            '/order-confirmation': '/potvrzeni-objednavky',
            '/order-detail/:urlHash': '/detail-objednavky/:urlHash',
        },
        'https://test3.example.com/': {
            '/customer/order-detail': '/zakaznik/detail-objednavky',
            '/order-confirmation': '/potvrdenie-objednavky',
            '/order-detail/:urlHash': '/detail-objednavky/:urlHash',
        },
    },
}));
vi.mock('components/providers/DomainConfigProvider', () => ({ useDomainConfig: domainMock }));
vi.mock('graphql/requests/orders/mutations/ChangePaymentInOrderMutation.generated', () => ({
    useChangePaymentInOrderMutation: () => [{ fetching: false }, changePaymentMock],
}));
vi.mock('gtm/handlers/onGtmPaymentEventHandler', () => ({ onGtmPaymentTryEventHandler: paymentEventMock }));
vi.mock('next/router', () => ({ useRouter: () => routerMock }));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: loggedInMock }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/toasts/showErrorMessage', () => ({ showErrorMessage: errorMock }));
vi.mock('utils/toasts/showSuccessMessage', () => ({ showSuccessMessage: successMock }));

const domains = [
    { url: 'https://test1.example.com/', confirmationPath: '/order-confirmation' },
    { url: 'https://test2.example.com/', confirmationPath: '/potvrzeni-objednavky' },
    { url: 'https://test3.example.com/', confirmationPath: '/potvrdenie-objednavky' },
];

describe('useChangePaymentInOrder', () => {
    beforeEach(() => {
        vi.resetAllMocks();
        sessionStorage.clear();
        domainMock.mockReturnValue({ url: domains[0].url });
        loggedInMock.mockReturnValue(false);
        routerMock.asPath = '/order-detail/order-hash';
        routerMock.push.mockResolvedValue(true);
        changePaymentMock.mockResolvedValue({
            data: {
                ChangePaymentInOrder: {
                    __typename: 'Order',
                    urlHash: 'order-hash',
                    number: '123456',
                    paymentTransactionsCount: 1,
                },
            },
        });
    });

    test.each(domains)('saves context before redirecting to $confirmationPath', async ({ url, confirmationPath }) => {
        domainMock.mockReturnValue({ url });
        routerMock.push.mockImplementation(async () => {
            expect(getValidOrderConfirmationContext(url)?.orderUrlHash).toBe('order-hash');
            return true;
        });
        const { result } = renderHook(() => useChangePaymentInOrder());

        await result.current.changePaymentInOrderHandler(
            'order-uuid',
            'order-hash',
            'bank-uuid',
            'Bank transfer',
            undefined,
            true,
            true,
        );

        expect(routerMock.push).toHaveBeenCalledExactlyOnceWith(confirmationPath);
        expect(changePaymentMock).toHaveBeenCalledExactlyOnceWith({
            input: {
                orderUuid: 'order-uuid',
                orderUrlHash: 'order-hash',
                paymentUuid: 'bank-uuid',
                paymentGoPayBankSwift: null,
            },
        });
        expect(paymentEventMock).toHaveBeenCalledExactlyOnceWith('123456', 'Bank transfer', true, undefined, 1);
    });

    test.each(domains)(
        'stays on $confirmationPath with query and hash without reloading',
        async ({ url, confirmationPath }) => {
            domainMock.mockReturnValue({ url });
            routerMock.asPath = `${confirmationPath}?source=payment#instructions`;
            const { result } = renderHook(() => useChangePaymentInOrder());

            await result.current.changePaymentInOrderHandler(
                'order-uuid',
                'order-hash',
                'bank-uuid',
                'Bank transfer',
                undefined,
                true,
                true,
            );

            expect(routerMock.push).not.toHaveBeenCalled();
            expect(routerMock.reload).not.toHaveBeenCalled();
            expect(getValidOrderConfirmationContext(url)?.orderUrlHash).toBe('order-hash');
            expect(paymentEventMock).toHaveBeenCalledExactlyOnceWith('123456', 'Bank transfer', true, undefined, 1);
        },
    );

    test('redirects a logged-in customer to confirmation for bank transfer', async () => {
        loggedInMock.mockReturnValue(true);
        const { result } = renderHook(() => useChangePaymentInOrder());

        await result.current.changePaymentInOrderHandler(
            'order-uuid',
            'order-hash',
            'bank-uuid',
            'Bank transfer',
            undefined,
            true,
            true,
        );

        expect(routerMock.push).toHaveBeenCalledExactlyOnceWith('/order-confirmation');
        expect(getValidOrderConfirmationContext(domains[0].url)?.orderUrlHash).toBe('order-hash');
    });

    test('does not save context or navigate when changing payment fails', async () => {
        changePaymentMock.mockResolvedValue({ data: undefined });
        const { result } = renderHook(() => useChangePaymentInOrder());

        await result.current.changePaymentInOrderHandler(
            'order-uuid',
            'order-hash',
            'bank-uuid',
            'Bank transfer',
            undefined,
            true,
            true,
        );

        expect(getValidOrderConfirmationContext(domains[0].url)).toBeNull();
        expect(routerMock.push).not.toHaveBeenCalled();
        expect(errorMock).toHaveBeenCalledExactlyOnceWith('An error occurred while changing the payment');
        expect(successMock).not.toHaveBeenCalled();
        expect(paymentEventMock).not.toHaveBeenCalled();
    });

    test.each([false, true])(
        'keeps the order detail destination for other payments (logged in: %s)',
        async (isLoggedIn) => {
            loggedInMock.mockReturnValue(isLoggedIn);
            const { result } = renderHook(() => useChangePaymentInOrder());

            await result.current.changePaymentInOrderHandler('order-uuid', 'order-hash', 'cash-uuid', 'Cash');

            expect(routerMock.push).toHaveBeenCalledExactlyOnceWith(
                isLoggedIn
                    ? { pathname: '/customer/order-detail', query: { orderNumber: '123456' } }
                    : '/order-detail/order-hash',
            );
            expect(getValidOrderConfirmationContext(domains[0].url)).toBeNull();
        },
    );

    test('respects disabled navigation', async () => {
        const { result } = renderHook(() => useChangePaymentInOrder());

        await result.current.changePaymentInOrderHandler(
            'order-uuid',
            'order-hash',
            'bank-uuid',
            'Bank transfer',
            undefined,
            false,
            true,
        );

        expect(routerMock.push).not.toHaveBeenCalled();
        expect(routerMock.reload).not.toHaveBeenCalled();
        expect(getValidOrderConfirmationContext(domains[0].url)).toBeNull();
    });
});
