import { render, waitFor } from '@testing-library/react';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetail/OrderDetailContent';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockTryEmitPaymentEvent = vi.fn();

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (text: string) => text }),
}));

vi.mock('gtm/hooks/useEmitPendingPaymentEvent', () => ({
    useEmitPendingPaymentEvent: () => ({
        tryEmitPaymentEvent: (...args: unknown[]) => mockTryEmitPaymentEvent(...args),
    }),
}));

vi.mock('components/Pages/Customer/OrderDetail/OrderDetailBasicInfo', () => ({
    OrderDetailBasicInfo: () => <div>OrderDetailBasicInfo</div>,
}));

vi.mock('components/Pages/Customer/OrderDetail/OrderDetailWithdrawalSection', () => ({
    OrderDetailWithdrawalSection: () => <div>OrderDetailWithdrawalSection</div>,
}));

vi.mock('components/Blocks/OrderCustomerInfo/OrderCustomerInfo', () => ({
    OrderCustomerInfo: () => <div>OrderCustomerInfo</div>,
}));

const createOrder = () =>
    ({
        uuid: 'order-uuid',
        number: '1234567890',
        isPaid: false,
        hasPaymentInProcess: false,
        paymentTransactionsCount: 2,
        items: [
            {
                type: TypeOrderItemTypeEnum.Payment,
                name: 'GoPay',
                payment: { name: 'GoPay' },
            },
        ],
    }) as any;

describe('OrderDetailContent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('emits pending payment event when no recovery redirect is needed', async () => {
        render(<OrderDetailContent order={createOrder()} />);

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                isPaid: false,
                hasPaymentInProcess: false,
                paymentTransactionsCount: 2,
                paymentName: 'GoPay',
                orderNumber: '1234567890',
            });
        });
    });
});
