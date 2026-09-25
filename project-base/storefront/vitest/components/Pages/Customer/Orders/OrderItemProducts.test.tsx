import { render, screen } from '@testing-library/react';
import { OrderItemProducts } from 'components/Pages/Customer/Orders/OrderItemProducts';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: ReactNode; href: string }) => <a href={href}>{children}</a>,
}));

vi.mock('components/Pages/Customer/CustomerRecordElements', () => ({
    CustomerRecordProductImage: ({ image }: { image?: string }) => (
        <span data-image={image ?? 'fallback'} data-testid="product-image" />
    ),
    CustomerRecordRowInfo: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('OrderItemProducts', () => {
    test('shows the fallback image for a product without a main image', () => {
        const item = {
            __typename: 'OrderItem',
            quantity: 1,
            product: {
                __typename: 'MainVariant',
                link: '/product',
                name: 'Product without image',
                isVisible: true,
                isSellingDenied: false,
                isInquiryType: false,
                isCurrentlyOutOfStock: false,
                mainImage: null,
            },
        } as TypeOrderItemFragment;

        render(
            <OrderItemProducts
                items={[item]}
                orderLink={{ pathname: '/customer/orders', query: { orderNumber: '123' } }}
            />,
        );

        expect(screen.getByTestId('product-image')).toHaveAttribute('data-image', 'fallback');
    });
});
