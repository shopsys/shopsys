import { render, screen, waitFor } from '@testing-library/react';
import { TypeOrderItemTypeEnum, TypePaymentTypeEnum } from 'graphql/types';
import OrderConfirmationPage from 'pages/order-confirmation';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockFetchCart = vi.fn();
const mockTryEmitPaymentEvent = vi.fn();
const mockBuildPaymentConfirmationUrlFromSession = vi.fn();
const mockRouterReplace = vi.fn();
const mockReexecuteOrderDetailQuery = vi.fn();

let mockRouterQuery: Record<string, unknown> = {};
let mockOrderHasPaymentInProcess = false;
let mockOrderData: any;

const renderOrderConfirmationPage = () => render(<OrderConfirmationPage {...({} as any)} />);

const createMockOrder = () =>
    ({
        uuid: 'order-uuid',
        urlHash: 'order-hash',
        email: 'customer@example.com',
        number: '1234567890',
        payment: {
            name: 'GoPay - Payment by Card',
            price: { priceWithVat: '10.00' },
        },
        paymentTransactionsCount: 3,
        isPaid: false,
        hasPaymentInProcess: mockOrderHasPaymentInProcess,
        hasExternalPayment: true,
        items: [
            {
                type: TypeOrderItemTypeEnum.Payment,
                name: 'GoPay - Payment by Card',
                totalPrice: { priceWithVat: '10.00' },
            },
            { type: TypeOrderItemTypeEnum.Transport, name: 'PPL', totalPrice: { priceWithVat: '5.00' } },
        ],
        transport: {
            name: 'PPL',
            price: { priceWithVat: '5.00' },
        },
        promoCode: null,
        totalPrice: { priceWithVat: '100.00' },
    }) as any;

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (text: string) => text }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({
        query: mockRouterQuery,
        replace: (...args: unknown[]) => mockRouterReplace(...args),
    }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'http://127.0.0.1:8000', defaultLocale: 'cs' }),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ fetchCart: (...args: unknown[]) => mockFetchCart(...args) }),
}));

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    buildPaymentConfirmationUrlFromSession: (...args: unknown[]) => mockBuildPaymentConfirmationUrlFromSession(...args),
}));

vi.mock('graphql/requests/orders/queries/OrderSentPageContentQuery.generated', () => ({
    useOrderSentPageContentQuery: () => [
        { data: { orderSentPageContent: 'Order sent content' }, fetching: false, error: undefined },
    ],
    OrderSentPageContentQueryDocument: {},
}));

vi.mock('graphql/requests/orders/queries/OrderDetailByHashOrUuidQuery.generated', () => ({
    useOrderDetailByHashOrUuidQuery: () => [{ data: mockOrderData, fetching: false }, mockReexecuteOrderDetailQuery],
}));

vi.mock('gtm/factories/useGtmStaticPageViewEvent', () => ({
    useGtmStaticPageViewEvent: () => ({}),
}));

vi.mock('gtm/utils/pageViewEvents/useGtmPageViewEvent', () => ({
    useGtmPageViewEvent: vi.fn(),
}));

vi.mock('gtm/hooks/useEmitPendingPaymentEvent', () => ({
    useEmitPendingPaymentEvent: () => ({
        tryEmitPaymentEvent: (...args: unknown[]) => mockTryEmitPaymentEvent(...args),
    }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: unknown[]) =>
        urls.map((url) => (typeof url === 'string' ? url : '/order-detail/mock-hash')),
}));

vi.mock('components/Layout/CommonLayout', () => ({
    CommonLayout: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Blocks/ConfirmationPage/ConfirmationPageContent', () => ({
    ConfirmationPageContent: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway', () => ({
    GoPayGateway: () => <div>GoPayGateway</div>,
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: React.ReactNode; href?: string }) => (
        <a href={href}>{children}</a>
    ),
}));

vi.mock('components/Basic/Head/MetaRobots', () => ({ MetaRobots: () => null }));
vi.mock('components/Basic/Icon/InfoIcon', () => ({ InfoIcon: () => null }));
vi.mock('components/Blocks/OrderCustomerInfo/OrderCustomerInfo', () => ({ OrderCustomerInfo: () => null }));
vi.mock('components/Pages/OrderConfirmation/OrderConfirmationProducts', () => ({
    OrderConfirmationProducts: () => null,
}));
vi.mock('components/Pages/OrderConfirmation/OrderConfirmationStepper', () => ({
    OrderConfirmationStepper: () => null,
}));
vi.mock('components/Pages/OrderConfirmation/OrderConfirmationStepperFlows', () => ({
    FlowTypesEnum: { PaymentSuccess: 'PaymentSuccess', PaymentAwaiting: 'PaymentAwaiting' },
}));
vi.mock('components/Pages/OrderConfirmation/OrderConfirmationSummary', () => ({
    OrderConfirmationSummary: () => null,
}));
vi.mock('components/Pages/OrderConfirmation/RegistrationAfterOrder', () => ({ RegistrationAfterOrder: () => null }));
vi.mock('next-translate/Trans', () => ({ default: ({ children }: { children: React.ReactNode }) => <>{children}</> }));
vi.mock('utils/serverSide/getServerSidePropsWrapper', () => ({
    getServerSidePropsWrapper: () => () => async () => ({ props: {} }),
}));
vi.mock('utils/serverSide/initServerSideProps', () => ({ initServerSideProps: vi.fn() }));
vi.mock('utils/domain/domainUtils', () => ({ getBasePathWithLocale: vi.fn() }));

// createMockOrder captures mockOrderHasPaymentInProcess at call time, so mockOrderData must be
// re-created whenever the flag is flipped inside a test.
const setOrderHasPaymentInProcess = (value: boolean) => {
    mockOrderHasPaymentInProcess = value;
    mockOrderData = { order: createMockOrder() };
};

describe('order-confirmation page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        setOrderHasPaymentInProcess(false);
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(null);
        mockRouterReplace.mockResolvedValue(true);
        mockRouterQuery = {
            orderUuid: 'order-uuid',
            orderEmail: 'customer@example.com',
            orderUrlHash: 'order-hash',
        };
    });

    test('calls tryEmitPaymentEvent with order data as guarded fallback', async () => {
        renderOrderConfirmationPage();

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                isPaid: false,
                hasPaymentInProcess: false,
                paymentTransactionsCount: 3,
                paymentName: 'GoPay - Payment by Card',
                orderNumber: '1234567890',
            });
        });
    });

    test('calls tryEmitPaymentEvent with in-process order data (hook decides on emission)', async () => {
        setOrderHasPaymentInProcess(true);

        renderOrderConfirmationPage();

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).toHaveBeenCalledWith(
                expect.objectContaining({ hasPaymentInProcess: true, isPaid: false }),
            );
        });
    });

    test('does not call tryEmitPaymentEvent on stale GoPay order-confirmation landing with requiresAction', async () => {
        mockRouterQuery = {
            orderUuid: 'order-uuid',
            orderEmail: 'customer@example.com',
            orderUrlHash: 'order-hash',
            orderPaymentType: TypePaymentTypeEnum.GoPay,
            requiresAction: true,
        };

        renderOrderConfirmationPage();

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).not.toHaveBeenCalled();
        });
    });

    test('redirects only when stored GoPay session matches current order', async () => {
        mockBuildPaymentConfirmationUrlFromSession.mockReturnValue(
            '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
        );

        renderOrderConfirmationPage();

        await waitFor(() => {
            expect(mockBuildPaymentConfirmationUrlFromSession).toHaveBeenCalledWith(
                { url: 'http://127.0.0.1:8000', defaultLocale: 'cs' },
                'order-uuid',
            );
            expect(mockRouterReplace).toHaveBeenCalledWith(
                '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash',
            );
        });
    });

    test('retries missing GoPay order detail once on Safari with network-only request', async () => {
        mockOrderData = { order: null };
        mockRouterQuery = {
            orderUuid: 'order-uuid',
            orderEmail: 'customer@example.com',
            orderPaymentType: TypePaymentTypeEnum.GoPay,
        };

        const userAgentGetter = vi
            .spyOn(window.navigator, 'userAgent', 'get')
            .mockReturnValue(
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3 Safari/605.1.15',
            );

        renderOrderConfirmationPage();

        await waitFor(() => {
            expect(mockReexecuteOrderDetailQuery).toHaveBeenCalledWith({ requestPolicy: 'network-only' });
        });

        userAgentGetter.mockRestore();
    });

    test('renders GoPay gateway only for GoPay orders', () => {
        mockRouterQuery = {
            ...mockRouterQuery,
            orderPaymentType: TypePaymentTypeEnum.GoPay,
        };

        renderOrderConfirmationPage();

        expect(screen.getByText('GoPayGateway')).toBeInTheDocument();
    });

    test('does not render GoPay gateway for basic orders', () => {
        mockRouterQuery = {
            ...mockRouterQuery,
            orderPaymentType: TypePaymentTypeEnum.Basic,
        };

        renderOrderConfirmationPage();

        expect(screen.queryByText('GoPayGateway')).not.toBeInTheDocument();
    });
});
