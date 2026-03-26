import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';
import OrderPaymentConfirmationPage, { getServerSideProps } from 'pages/order-payment-confirmation';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockRouterReplace = vi.fn();
const mockGetGoPayPaymentSessionForOrder = vi.fn();
const mockUseUpdatePaymentStatus = vi.fn();
const mockUseOrderDetailByHashOrUuidQuery = vi.fn();
const mockRecheckPaymentStatus = vi.fn();
const mockGetBasePathWithLocale = vi.fn();
const mockGetLocalePrefix = vi.fn();
const mockInitServerSideProps = vi.fn();
const mockShowErrorMessage = vi.fn();
const mockShowInfoMessage = vi.fn();

let mockRouterQuery: Record<string, unknown> = {};
let mockDomainConfig = {
    url: 'http://127.0.0.1:8000',
    defaultLocale: 'cs',
};
let mockOrder: {
    uuid: string;
    email: string;
    urlHash: string;
    number: string;
    hasPaymentInProcess: boolean;
    hasExternalPayment: boolean;
    isPaid: boolean;
    paymentTransactionsCount: number;
    lastExternalPaymentUrl: string | null;
    payment: {
        name: string;
        price: { priceWithVat: string };
    };
    transport: {
        name: string;
        price: { priceWithVat: string };
    };
    items: never[];
    promoCode: null;
    totalPrice: { priceWithVat: string };
} = {
    uuid: 'order-uuid',
    email: 'customer@example.com',
    urlHash: 'order-hash',
    number: '1234567890',
    hasPaymentInProcess: false,
    hasExternalPayment: true,
    isPaid: false,
    paymentTransactionsCount: 2,
    lastExternalPaymentUrl: null,
    payment: {
        name: 'GoPay - Payment by Card',
        price: { priceWithVat: '10.00' },
    },
    transport: {
        name: 'PPL',
        price: { priceWithVat: '5.00' },
    },
    items: [],
    promoCode: null,
    totalPrice: { priceWithVat: '100.00' },
};

const renderOrderPaymentConfirmationPage = () => render(<OrderPaymentConfirmationPage {...({} as any)} />);

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (text: string) => text,
    }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({
        query: mockRouterQuery,
        isReady: true,
        replace: (...args: unknown[]) => mockRouterReplace(...args),
    }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => mockDomainConfig,
}));

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    getGoPayPaymentSessionForOrder: (...args: unknown[]) => mockGetGoPayPaymentSessionForOrder(...args),
}));

vi.mock('components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils', async (importOriginal) => {
    const actual =
        await importOriginal<typeof import('components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils')>();

    return {
        ...actual,
        useUpdatePaymentStatus: (...args: unknown[]) => mockUseUpdatePaymentStatus(...args),
    };
});

vi.mock('graphql/requests/orders/queries/OrderDetailByHashOrUuidQuery.generated', () => ({
    useOrderDetailByHashOrUuidQuery: (...args: unknown[]) => {
        mockUseOrderDetailByHashOrUuidQuery(...args);

        return [
            {
                data: {
                    order: mockOrder,
                },
                fetching: false,
                error: undefined,
            },
        ];
    },
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: unknown[]) =>
        urls.map((url) => {
            if (typeof url === 'string') {
                return url;
            }

            return '/order-detail/mock-hash';
        }),
}));

vi.mock('components/Layout/CommonLayout', () => ({
    CommonLayout: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Blocks/ConfirmationPage/ConfirmationPageContent', () => ({
    ConfirmationPageContent: ({
        heading,
        content,
        children,
    }: {
        heading: string;
        content?: string;
        children: React.ReactNode;
    }) => (
        <div>
            <div>{heading}</div>
            {content && <div>{content}</div>}
            {children}
        </div>
    ),
}));

vi.mock('components/Pages/Order/PaymentConfirmation/PaymentStatus', () => ({
    PaymentStatus: () => <div>PaymentStatus</div>,
}));

vi.mock('components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton', () => ({
    ShowPaymentInstructionButton: () => null,
}));

vi.mock('utils/toasts/showInfoMessage', () => ({
    showInfoMessage: (...args: unknown[]) => mockShowInfoMessage(...args),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: (...args: unknown[]) => mockShowErrorMessage(...args),
}));

vi.mock('components/Pages/OrderConfirmation/OrderConfirmationProducts', () => ({
    OrderConfirmationProducts: () => null,
}));

vi.mock('components/Pages/OrderConfirmation/OrderConfirmationStepper', () => ({
    OrderConfirmationStepper: () => null,
}));

vi.mock('components/Pages/OrderConfirmation/OrderConfirmationStepperFlows', () => ({
    FlowTypesEnum: {
        PaymentInProcess: 'PaymentInProcess',
        PaymentSuccess: 'PaymentSuccess',
        PaymentFailed: 'PaymentFailed',
    },
}));

vi.mock('components/Pages/OrderConfirmation/OrderConfirmationSummary', () => ({
    OrderConfirmationSummary: () => null,
}));

vi.mock('components/Pages/OrderConfirmation/RegistrationAfterOrder', () => ({
    RegistrationAfterOrder: () => null,
}));

vi.mock('components/Blocks/OrderCustomerInfo/OrderCustomerInfo', () => ({
    OrderCustomerInfo: () => null,
}));

vi.mock('components/PaymentsInOrderSelect/PaymentsInOrderSelect', () => ({
    PaymentsInOrderSelect: () => <div>PaymentsInOrderSelect</div>,
}));

vi.mock('components/Basic/Head/MetaRobots', () => ({
    MetaRobots: () => null,
}));

vi.mock('utils/serverSide/getServerSidePropsWrapper', () => ({
    getServerSidePropsWrapper:
        (callback: (props: Record<string, unknown>) => (context: Record<string, unknown>) => Promise<unknown>) =>
        async (context: Record<string, unknown>) => {
            const handler = callback({
                redisClient: {},
                domainConfig: mockDomainConfig,
                ssrExchange: {},
                t: (text: string) => text,
                cookiesStoreState: {},
            });

            return handler(context);
        },
}));

vi.mock('utils/serverSide/initServerSideProps', () => ({
    initServerSideProps: (...args: unknown[]) => mockInitServerSideProps(...args),
}));

vi.mock('utils/domain/domainUtils', () => ({
    getBasePathWithLocale: (...args: unknown[]) => mockGetBasePathWithLocale(...args),
    getLocalePrefix: (...args: unknown[]) => mockGetLocalePrefix(...args),
}));

describe('order-payment-confirmation page', () => {
    const createUpdatePaymentStatusResult = (overrides?: Record<string, unknown>) => ({
        UpdatePaymentStatus: {
            number: '1234567890',
            urlHash: 'order-hash',
            hasPaymentInProcess: false,
            isPaid: false,
            paymentTransactionsCount: 2,
            items: [],
            paymentPageContent: {
                status: 'FAILED',
                content: 'failed content',
            },
            ...overrides,
        },
    });

    beforeEach(() => {
        vi.clearAllMocks();
        mockDomainConfig = {
            url: 'http://127.0.0.1:8000',
            defaultLocale: 'cs',
        };
        mockOrder = {
            uuid: 'order-uuid',
            email: 'customer@example.com',
            urlHash: 'order-hash',
            number: '1234567890',
            hasPaymentInProcess: false,
            hasExternalPayment: true,
            isPaid: false,
            paymentTransactionsCount: 2,
            lastExternalPaymentUrl: null,
            payment: {
                name: 'GoPay - Payment by Card',
                price: { priceWithVat: '10.00' },
            },
            transport: {
                name: 'PPL',
                price: { priceWithVat: '5.00' },
            },
            items: [],
            promoCode: null,
            totalPrice: { priceWithVat: '100.00' },
        };
        mockGetLocalePrefix.mockReturnValue('');
        mockGetBasePathWithLocale.mockImplementation((path: string) => path);
        mockRouterReplace.mockResolvedValue(true);
        mockRecheckPaymentStatus.mockReset();
        mockRecheckPaymentStatus.mockResolvedValue(TypePaymentContentPageStatusEnum.Successful);
        mockInitServerSideProps.mockResolvedValue({ props: {} });
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult(),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });
        mockGetGoPayPaymentSessionForOrder.mockReturnValue(null);
        mockRouterQuery = {
            orderIdentifier: 'order-uuid',
            orderUrlHash: 'order-hash',
        };
    });

    test('recovers missing validity hash from GoPay session and replaces URL', async () => {
        mockGetGoPayPaymentSessionForOrder.mockReturnValue({
            orderUuid: 'order-uuid',
            orderUrlHash: 'order-hash',
            orderPaymentStatusPageValidityHash: 'validity-hash',
        });

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith({
                pathname: '/order-payment-confirmation',
                query: {
                    orderIdentifier: 'order-uuid',
                    orderUrlHash: 'order-hash',
                    orderPaymentStatusPageValidityHash: 'validity-hash',
                },
            });
        });
    });

    test('does not try session recovery when validity hash is already present in URL', async () => {
        mockRouterQuery = {
            ...mockRouterQuery,
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };
        mockGetGoPayPaymentSessionForOrder.mockReturnValue({
            orderUuid: 'order-uuid',
            orderUrlHash: 'order-hash',
            orderPaymentStatusPageValidityHash: 'session-hash',
        });

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).not.toHaveBeenCalled();
        });
    });

    test('sanitizes orderEmail from URL query when present', async () => {
        mockRouterQuery = {
            ...mockRouterQuery,
            orderEmail: 'customer@example.com',
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith(
                {
                    pathname: '/order-payment-confirmation',
                    query: {
                        orderIdentifier: 'order-uuid',
                        orderUrlHash: 'order-hash',
                        orderPaymentStatusPageValidityHash: 'hash-from-url',
                    },
                },
                undefined,
                { shallow: true },
            );
        });
    });

    test('sanitizes orderEmail and removes empty orderUrlHash from URL query', async () => {
        mockRouterQuery = {
            orderIdentifier: 'order-uuid',
            orderUrlHash: '',
            orderEmail: 'customer@example.com',
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith(
                {
                    pathname: '/order-payment-confirmation',
                    query: {
                        orderIdentifier: 'order-uuid',
                        orderPaymentStatusPageValidityHash: 'hash-from-url',
                    },
                },
                undefined,
                { shallow: true },
            );
        });
    });

    test('uses urlHash from payment status mutation when URL hash is missing', async () => {
        mockRouterQuery = {
            orderIdentifier: 'order-uuid',
            orderUrlHash: '',
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({ urlHash: 'hash-from-payment-status' }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockUseOrderDetailByHashOrUuidQuery).toHaveBeenCalledWith({
                variables: {
                    urlHash: 'hash-from-payment-status',
                    uuid: undefined,
                },
                requestPolicy: 'network-only',
                pause: false,
            });
        });
    });

    test('sanitizes empty orderUrlHash from URL independently of orderEmail', async () => {
        mockRouterQuery = {
            orderIdentifier: 'order-uuid',
            orderUrlHash: '',
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith(
                {
                    pathname: '/order-payment-confirmation',
                    query: {
                        orderIdentifier: 'order-uuid',
                        orderPaymentStatusPageValidityHash: 'hash-from-url',
                    },
                },
                undefined,
                { shallow: true },
            );
        });
    });

    test('prepends locale prefix to sanitized URL on SK domain', async () => {
        mockDomainConfig = {
            url: 'http://127.0.0.1:8000/sk',
            defaultLocale: 'sk',
        };
        mockGetLocalePrefix.mockReturnValue('/sk');
        mockRouterQuery = {
            orderIdentifier: 'order-uuid',
            orderEmail: 'customer@example.com',
            orderPaymentStatusPageValidityHash: 'hash-from-url',
        };

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith(
                {
                    pathname: '/sk/order-payment-confirmation',
                    query: {
                        orderIdentifier: 'order-uuid',
                        orderPaymentStatusPageValidityHash: 'hash-from-url',
                    },
                },
                undefined,
                { shallow: true },
            );
        });
    });

    test('prepends locale prefix to GoPay session recovery redirect on SK domain', async () => {
        mockDomainConfig = {
            url: 'http://127.0.0.1:8000/sk',
            defaultLocale: 'sk',
        };
        mockGetLocalePrefix.mockReturnValue('/sk');
        mockGetGoPayPaymentSessionForOrder.mockReturnValue({
            orderUuid: 'order-uuid',
            orderUrlHash: 'order-hash',
            orderPaymentStatusPageValidityHash: 'validity-hash',
        });

        renderOrderPaymentConfirmationPage();

        await waitFor(() => {
            expect(mockRouterReplace).toHaveBeenCalledWith({
                pathname: '/sk/order-payment-confirmation',
                query: {
                    orderIdentifier: 'order-uuid',
                    orderUrlHash: 'order-hash',
                    orderPaymentStatusPageValidityHash: 'validity-hash',
                },
            });
        });
    });

    test('uses locale-prefixed destination for server-side sanitization redirect on SK domain', async () => {
        mockDomainConfig = {
            url: 'http://127.0.0.1:8000/sk',
            defaultLocale: 'sk',
        };
        mockGetBasePathWithLocale.mockReturnValue('/sk/order-payment-confirmation');

        const result = await getServerSideProps({
            locale: 'sk',
            query: {
                orderIdentifier: 'order-uuid',
                orderEmail: 'customer@example.com',
                orderPaymentStatusPageValidityHash: 'hash-from-url',
            },
        } as any);

        expect(mockGetBasePathWithLocale).toHaveBeenCalledWith('/order-payment-confirmation', expect.any(Object));
        expect(result).toEqual({
            redirect: {
                destination:
                    '/sk/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=hash-from-url',
                statusCode: 302,
            },
        });
    });

    test('shows recoverable status error instead of failed payment flow', () => {
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: undefined,
            statusError: true,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });

        renderOrderPaymentConfirmationPage();

        expect(screen.getByText("We couldn't verify your payment status")).toBeInTheDocument();
        expect(
            screen.getByText(
                'Please try checking your payment status again. If the problem persists, check your email for order details.',
            ),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Check payment status' })).toBeInTheDocument();
        expect(screen.queryByText('PaymentStatus')).not.toBeInTheDocument();
        expect(screen.queryByText('PaymentsInOrderSelect')).not.toBeInTheDocument();
    });

    test('shows helper text for in-process payment only when show payment instruction button is visible', () => {
        mockOrder = {
            ...mockOrder,
            lastExternalPaymentUrl: 'https://gopay.example.com/payment-instruction',
        };
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });

        renderOrderPaymentConfirmationPage();

        expect(
            screen.getByText(
                'If you have already completed the payment outside the store, you can check the latest payment status.',
            ),
        ).toBeInTheDocument();
    });

    test('hides helper text when show payment instruction button is not visible', () => {
        mockOrder = {
            ...mockOrder,
            lastExternalPaymentUrl: null,
        };
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });

        renderOrderPaymentConfirmationPage();

        expect(
            screen.queryByText(
                'If you have already completed the payment outside the store, you can check the latest payment status.',
            ),
        ).not.toBeInTheDocument();
    });

    test('keeps helper text visible while payment status check is already running', () => {
        mockOrder = {
            ...mockOrder,
            lastExternalPaymentUrl: 'https://gopay.example.com/payment-instruction',
        };
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: true,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });

        renderOrderPaymentConfirmationPage();

        expect(
            screen.getByText(
                'If you have already completed the payment outside the store, you can check the latest payment status.',
            ),
        ).toBeInTheDocument();
    });

    test('shows info toast when manual status check confirms payment is still in process', async () => {
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });
        mockRecheckPaymentStatus.mockResolvedValue(TypePaymentContentPageStatusEnum.InProcess);

        renderOrderPaymentConfirmationPage();

        fireEvent.click(screen.getByRole('button', { name: 'Check payment status' }));

        await waitFor(() => {
            expect(mockShowInfoMessage).toHaveBeenCalledWith(
                'Payment status checked. The payment is still being processed.',
            );
        });
        expect(mockShowErrorMessage).not.toHaveBeenCalled();
        expect(mockRecheckPaymentStatus).toHaveBeenCalledTimes(1);
    });

    test('shows error toast when manual status check fails', async () => {
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });
        mockRecheckPaymentStatus.mockResolvedValue('error');

        renderOrderPaymentConfirmationPage();

        fireEvent.click(screen.getByRole('button', { name: 'Check payment status' }));

        await waitFor(() => {
            expect(mockShowErrorMessage).toHaveBeenCalledWith('Failed to check payment status. Please try again.');
        });
        expect(mockShowInfoMessage).not.toHaveBeenCalled();
    });

    test('does not show toast when manual status check resolves to terminal state', async () => {
        mockUseUpdatePaymentStatus.mockReturnValue({
            paymentStatusData: createUpdatePaymentStatusResult({
                hasPaymentInProcess: true,
                paymentPageContent: {
                    status: TypePaymentContentPageStatusEnum.InProcess,
                    content: 'in process',
                },
            }),
            statusError: false,
            isCheckingStatus: false,
            recheckPaymentStatus: mockRecheckPaymentStatus,
        });
        mockRecheckPaymentStatus.mockResolvedValue(TypePaymentContentPageStatusEnum.Successful);

        renderOrderPaymentConfirmationPage();

        fireEvent.click(screen.getByRole('button', { name: 'Check payment status' }));

        await waitFor(() => {
            expect(mockRecheckPaymentStatus).toHaveBeenCalledTimes(1);
        });
        expect(mockShowInfoMessage).not.toHaveBeenCalled();
        expect(mockShowErrorMessage).not.toHaveBeenCalled();
    });
});
