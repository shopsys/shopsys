import { render } from '@testing-library/react';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

const mockPaymentFail = vi.fn<(props: unknown) => null>(() => null);
const mockPaymentInProcess = vi.fn<(props: unknown) => null>(() => null);
const mockPaymentSuccess = vi.fn<(props: unknown) => null>(() => null);

vi.mock('components/Pages/Order/PaymentConfirmation/PaymentFail', () => ({
    PaymentFail: (props: unknown) => mockPaymentFail(props),
}));

vi.mock('components/Pages/Order/PaymentConfirmation/PaymentInProcess', () => ({
    PaymentInProcess: (props: unknown) => mockPaymentInProcess(props),
}));

vi.mock('components/Pages/Order/PaymentConfirmation/PaymentSuccess', () => ({
    PaymentSuccess: (props: unknown) => mockPaymentSuccess(props),
}));

describe('PaymentStatus', () => {
    const orderData = {
        order: {
            uuid: 'order-uuid',
        },
    } as any;

    test('passes content to failed status component when override and backend status match', () => {
        render(
            <PaymentStatus
                orderData={orderData}
                statusOverride={TypePaymentContentPageStatusEnum.Failed}
                paymentStatusData={
                    {
                        UpdatePaymentStatus: {
                            paymentPageContent: {
                                status: TypePaymentContentPageStatusEnum.Failed,
                                content: 'failed content',
                            },
                        },
                    } as any
                }
            />,
        );

        expect(mockPaymentFail).toHaveBeenCalledWith({ orderPaymentFailedContent: 'failed content' });
    });

    test('uses atomic in-process payment content from update payment status mutation', () => {
        render(
            <PaymentStatus
                orderData={orderData}
                paymentStatusData={
                    {
                        UpdatePaymentStatus: {
                            paymentPageContent: {
                                status: TypePaymentContentPageStatusEnum.InProcess,
                                content: 'in process content',
                            },
                        },
                    } as any
                }
            />,
        );

        expect(mockPaymentInProcess).toHaveBeenCalledWith({ orderPaymentInProcessContent: 'in process content' });
    });

    test('uses explicit status override when mutation status request fails', () => {
        render(
            <PaymentStatus
                orderData={orderData}
                statusOverride={TypePaymentContentPageStatusEnum.Failed}
                paymentStatusData={
                    {
                        UpdatePaymentStatus: {
                            paymentPageContent: null,
                        },
                    } as any
                }
            />,
        );

        expect(mockPaymentFail).toHaveBeenCalledWith({ orderPaymentFailedContent: '' });
    });

    test('renders nothing when payment page content is not available and there is no override', () => {
        const { container } = render(
            <PaymentStatus
                orderData={orderData}
                paymentStatusData={
                    {
                        UpdatePaymentStatus: {
                            paymentPageContent: null,
                        },
                    } as any
                }
            />,
        );

        expect(container).toBeEmptyDOMElement();
    });
});
