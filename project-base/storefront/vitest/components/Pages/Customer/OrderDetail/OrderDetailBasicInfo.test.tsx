import { fireEvent, render } from '@testing-library/react';
import { OrderDetailBasicInfo } from 'components/Pages/Customer/OrderDetail/OrderDetailBasicInfo';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

const { orderDetailOrderItemMock, addOrderItemsToEmptyCartMock, authorizationMock } = vi.hoisted(() => ({
    orderDetailOrderItemMock: vi.fn(),
    addOrderItemsToEmptyCartMock: vi.fn(),
    authorizationMock: { canCreateOrder: false },
}));

vi.mock('components/Blocks/ProductReviews/useCurrentCustomerUserReviewedProductUuids', () => ({
    useCurrentCustomerUserReviewedProductUuids: () => ({
        isLoading: false,
        reviewedProductUuids: new Set<string>(),
    }),
}));

vi.mock('components/Pages/Customer/CustomerRecordElements', () => ({
    CustomerRecordCard: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    CustomerRecordColumnInfo: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    CustomerRecordElementWithImage: () => null,
    CustomerRecordRowInfo: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Pages/Customer/OrderDetail/OrderDetailOrderItem', () => ({
    OrderDetailOrderItem: (props: unknown) => {
        orderDetailOrderItemMock(props);

        return null;
    },
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => authorizationMock,
}));

vi.mock('utils/cart/useAddOrderItemsToCart', () => ({
    useAddOrderItemsToCart: () => addOrderItemsToEmptyCartMock,
}));

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({ formatDate: (value: string) => value }),
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (value: string) => value,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/mappers/price', () => ({
    isPriceVisible: () => false,
}));

const productUuid = 'reviewed-product-uuid';
const order = {
    uuid: 'order-uuid',
    number: '1234567890',
    creationDate: '2026-09-01T08:00:00+02:00',
    status: 'Done',
    totalPrice: {
        priceWithVat: '100',
        priceWithoutVat: '80',
    },
    hasExternalPayment: false,
    hasPaymentInProcess: false,
    isPaid: true,
    urlHash: 'order-url-hash',
    productReviewsAllowed: true,
    reviewedProductUuids: [productUuid],
    customerUser: null,
    trackingNumber: null,
    trackingUrl: null,
    promoCode: null,
    note: null,
    giftVouchers: [] as TypeOrderDetailFragment['giftVouchers'],
    purchasedGiftVouchers: [] as TypeOrderDetailFragment['purchasedGiftVouchers'],
    remainingAmountToPay: '0',
    items: [
        {
            name: 'Reviewed product',
            type: TypeOrderItemTypeEnum.Product,
            product: {
                uuid: productUuid,
                isVisible: true,
                isSellingDenied: false,
                isInquiryType: false,
                isCurrentlyOutOfStock: false,
            },
        },
    ],
} as TypeOrderDetailFragment;

describe('OrderDetailBasicInfo', () => {
    test('passes products reviewed from a guest order to the review action', () => {
        render(<OrderDetailBasicInfo order={order} />);

        expect(orderDetailOrderItemMock).toHaveBeenCalledOnce();
        const orderItemProps = orderDetailOrderItemMock.mock.calls[0][0] as {
            isOrderFromRegisteredCustomer: boolean;
            reviewedProductUuids: Set<string>;
        };
        expect(orderItemProps.isOrderFromRegisteredCustomer).toBe(false);
        expect(orderItemProps.reviewedProductUuids).toEqual(new Set([productUuid]));
    });

    test('repeating the order passes its urlHash as the proof of access to the order', () => {
        authorizationMock.canCreateOrder = true;

        const { container } = render(<OrderDetailBasicInfo order={order} />);
        fireEvent.click(container.querySelector('[data-tid="order_detail_repeat_order_button"]')!);

        expect(addOrderItemsToEmptyCartMock).toHaveBeenCalledWith(order.uuid, order.urlHash);
    });
});
