import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TypePaymentTypeEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockChangePaymentInOrderHandler = vi.fn();
const mockReexecuteOrderAvailablePaymentsQuery = vi.fn();

let mockOrderAvailablePaymentsData: any;

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (text: string) => text,
    }),
}));

vi.mock('components/PaymentsInOrderSelect/paymentInOrderSelectUtils', () => ({
    useChangePaymentInOrder: () => ({
        isChangePaymentInOrderFetching: false,
        changePaymentInOrderHandler: (...args: unknown[]) => mockChangePaymentInOrderHandler(...args),
    }),
}));

vi.mock('graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated', () => ({
    useOrderAvailablePaymentsQuery: () => [
        { data: mockOrderAvailablePaymentsData, fetching: false },
        (...args: unknown[]) => mockReexecuteOrderAvailablePaymentsQuery(...args),
    ],
}));

vi.mock('components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway', () => ({
    GoPayGateway: ({
        initialButtonText,
        onMaxTransactionCountReached,
    }: {
        initialButtonText?: string;
        onMaxTransactionCountReached?: () => void;
    }) => <button onClick={onMaxTransactionCountReached}>{initialButtonText ?? 'Pay with GoPay'}</button>,
}));

vi.mock('components/PaymentsInOrderSelect/PaymentsInOrderSelectItem', () => ({
    PaymentsInOrderSelectItem: ({
        payment,
        setSelectedPaymentForChange,
    }: {
        payment: { name: string };
        setSelectedPaymentForChange?: (payment: unknown) => void;
    }) => <button onClick={() => setSelectedPaymentForChange?.(payment)}>{payment.name}</button>,
}));

describe('PaymentsInOrderSelect', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        mockOrderAvailablePaymentsData = {
            orderPayments: {
                currentPayment: {
                    uuid: 'current-gopay',
                    name: 'GoPay current payment',
                    type: TypePaymentTypeEnum.GoPay,
                },
                availablePayments: [
                    {
                        uuid: 'available-gopay',
                        name: 'GoPay bank transfer',
                        type: TypePaymentTypeEnum.GoPay,
                    },
                    {
                        uuid: 'cash-on-delivery',
                        name: 'Cash on delivery',
                        type: TypePaymentTypeEnum.Basic,
                    },
                ],
            },
        };
    });

    test('hides GoPay options and refetches available payments when max transaction count is reached', async () => {
        render(<PaymentsInOrderSelect orderUuid="order-uuid" />);

        expect(screen.getByText('GoPay current payment')).toBeInTheDocument();
        expect(screen.getByText('GoPay bank transfer')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Repeat payment' })).toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: 'Repeat payment' }));

        await waitFor(() => {
            expect(mockReexecuteOrderAvailablePaymentsQuery).toHaveBeenCalledWith({ requestPolicy: 'network-only' });
        });

        expect(screen.queryByText('GoPay current payment')).not.toBeInTheDocument();
        expect(screen.queryByText('GoPay bank transfer')).not.toBeInTheDocument();
        expect(screen.getByText('Cash on delivery')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Repeat payment' })).not.toBeInTheDocument();
    });

    test('hides GoPay options and refetches after max transaction error from auto-triggered GoPay gateway', async () => {
        mockChangePaymentInOrderHandler.mockResolvedValue({
            ChangePaymentInOrder: {
                uuid: 'edited-order',
            },
        });

        render(<PaymentsInOrderSelect orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('GoPay bank transfer'));
        fireEvent.click(screen.getByRole('button', { name: 'Pay with the selected method' }));

        await waitFor(() => {
            expect(screen.getByRole('button', { name: 'Pay with GoPay' })).toBeInTheDocument();
        });

        fireEvent.click(screen.getByRole('button', { name: 'Pay with GoPay' }));

        await waitFor(() => {
            expect(mockReexecuteOrderAvailablePaymentsQuery).toHaveBeenCalledWith({ requestPolicy: 'network-only' });
        });

        expect(screen.queryByText('GoPay current payment')).not.toBeInTheDocument();
        expect(screen.queryByText('GoPay bank transfer')).not.toBeInTheDocument();
        expect(screen.getByText('Cash on delivery')).toBeInTheDocument();
    });

    test('redirects after changing to non-GoPay payment by default', async () => {
        mockChangePaymentInOrderHandler.mockResolvedValue({
            ChangePaymentInOrder: {
                uuid: 'edited-order',
            },
        });

        render(<PaymentsInOrderSelect orderUuid="order-uuid" />);

        fireEvent.click(screen.getByText('Cash on delivery'));
        fireEvent.click(screen.getByRole('button', { name: 'Pay with the selected method' }));

        await waitFor(() => {
            expect(mockChangePaymentInOrderHandler).toHaveBeenCalledWith(
                'order-uuid',
                'cash-on-delivery',
                'Cash on delivery',
                TypePaymentTypeEnum.Basic,
                undefined,
                true,
            );
        });
    });
});
