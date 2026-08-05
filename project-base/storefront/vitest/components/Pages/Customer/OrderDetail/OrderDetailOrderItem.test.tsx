import { render, screen } from '@testing-library/react';
import { OrderDetailOrderItem } from 'components/Pages/Customer/OrderDetail/OrderDetailOrderItem';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: ReactNode; href: string }) => <a href={href}>{children}</a>,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateComplaint: false }),
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    productReviewsEnabled: true,
                },
            },
        },
    ],
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: { updatePortalContent: () => void }) => unknown) =>
        selector({ updatePortalContent: vi.fn() }),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => true,
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string) => price,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const productUuid = 'product-uuid';
const orderItem = {
    __typename: 'OrderItem',
    uuid: 'order-item-uuid',
    name: 'Product',
    quantity: 1,
    unit: 'pcs',
    type: TypeOrderItemTypeEnum.Product,
    totalPrice: {
        __typename: 'Price',
        priceWithVat: '10',
        priceWithoutVat: '8',
        vatAmount: '2',
    },
    order: {
        withdrawalRequest: null,
    },
    product: {
        uuid: productUuid,
        catalogNumber: 'CAT-1',
        slug: '/product',
        isVisible: true,
        mainImage: null,
    },
} as TypeOrderDetailItemFragment;

describe('OrderDetailOrderItem', () => {
    test('shows an explanation instead of a review link for an already reviewed product', () => {
        render(
            <OrderDetailOrderItem
                isOrderFromRegisteredCustomer
                orderItem={orderItem}
                orderUrlHash="order-url-hash"
                orderUuid="order-uuid"
                productReviewsAllowed
                isReviewAvailabilityLoading={false}
                reviewedProductUuids={new Set([productUuid])}
            />,
        );

        const reviewedStatus = screen.getByText('Already reviewed.');

        expect(reviewedStatus.querySelector('svg')).toBeInTheDocument();
        expect(screen.queryByText('Write a review')).not.toBeInTheDocument();
    });

    test('does not offer a review while the previous reviews are loading', () => {
        render(
            <OrderDetailOrderItem
                isOrderFromRegisteredCustomer
                isReviewAvailabilityLoading
                orderItem={orderItem}
                orderUrlHash="order-url-hash"
                orderUuid="order-uuid"
                productReviewsAllowed
                reviewedProductUuids={new Set()}
            />,
        );

        expect(screen.queryByText('Write a review')).not.toBeInTheDocument();
        expect(screen.queryByText('Already reviewed.')).not.toBeInTheDocument();
    });
});
