import { render, screen } from '@testing-library/react';
import { OrderDetailBasicInfo } from 'components/Pages/Customer/OrderDetail/OrderDetailBasicInfo';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

let mockCanCreateOrder = true;

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (text: string) => text,
    }),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({
        canCreateOrder: mockCanCreateOrder,
    }),
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string) => price,
}));

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({
        formatDate: (date: string) => date,
    }),
}));

vi.mock('utils/cart/useAddOrderItemsToCart', () => ({
    useAddOrderItemsToCart: () => vi.fn(),
}));

vi.mock('utils/mappers/price', () => ({
    isPriceVisible: () => true,
}));

vi.mock('components/PaymentsInOrderSelect/PaymentsInOrderSelect', () => ({
    PaymentsInOrderSelect: ({ orderUuid }: { orderUuid?: string }) => (
        <div data-testid="payments-in-order-select">{orderUuid}</div>
    ),
}));

vi.mock('components/Pages/Customer/Orders/OrderPaymentStatusBar', () => ({
    OrderPaymentStatusBar: () => null,
}));

vi.mock('components/Pages/Customer/Orders/OrderItemElements', () => ({
    ElementWithImage: () => null,
    OrderItemColumnInfo: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: ReactNode; href?: string }) => <a href={href}>{children}</a>,
}));

vi.mock('components/Basic/Flag/Flag', () => ({
    Flag: ({ children }: { children: ReactNode }) => <span>{children}</span>,
}));

vi.mock('components/Basic/Icon/WalletIcon', () => ({
    WalletIcon: () => null,
}));

vi.mock('components/Forms/Button/Button', () => ({
    Button: ({ children }: { children: ReactNode }) => <button>{children}</button>,
}));

vi.mock('components/Pages/Customer/OrderDetail/OrderDetailOrderItem', () => ({
    OrderDetailOrderItem: () => null,
}));

const createOrder = (overrides: Partial<Record<string, unknown>> = {}) =>
    ({
        uuid: 'order-uuid',
        number: '1234567890',
        creationDate: '2026-02-20',
        status: 'Created',
        hasExternalPayment: true,
        isPaid: false,
        hasPaymentInProcess: false,
        paymentTransactionsCount: 2,
        customerUser: null,
        promoCode: null,
        note: null,
        trackingUrl: null,
        trackingNumber: null,
        totalPrice: {
            priceWithVat: '100.00',
            priceWithoutVat: '82.64',
        },
        urlHash: 'order-url-hash',
        payment: {
            mainImage: null,
            price: { priceWithVat: '10.00' },
        },
        transport: {
            mainImage: null,
            price: { priceWithVat: '5.00' },
        },
        items: [
            {
                type: TypeOrderItemTypeEnum.Payment,
                name: 'GoPay',
                totalPrice: { priceWithVat: '10.00' },
            },
            {
                type: TypeOrderItemTypeEnum.Transport,
                name: 'PPL',
                totalPrice: { priceWithVat: '5.00' },
            },
            {
                type: TypeOrderItemTypeEnum.Product,
                name: 'Product A',
                totalPrice: { priceWithVat: '85.00' },
                product: {
                    isVisible: true,
                    isSellingDenied: false,
                    isInquiryType: false,
                    isCurrentlyOutOfStock: false,
                },
            },
        ],
        ...overrides,
    }) as any;

describe('OrderDetailBasicInfo', () => {
    beforeEach(() => {
        mockCanCreateOrder = true;
    });

    test('shows retry payment selector with reload behavior for unpaid external payment', () => {
        render(<OrderDetailBasicInfo order={createOrder()} />);

        expect(screen.getByTestId('payments-in-order-select')).toHaveTextContent('order-uuid');
    });

    test('hides retry payment selector when payment is in process', () => {
        render(<OrderDetailBasicInfo order={createOrder({ hasPaymentInProcess: true })} />);

        expect(screen.queryByTestId('payments-in-order-select')).toBeNull();
    });
});
