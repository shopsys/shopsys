import { render, screen } from '@testing-library/react';
import { OrderItemProducts } from 'components/Pages/Customer/Orders/OrderItemProducts';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children }: { children: ReactNode }) => <a href="/order-detail">{children}</a>,
}));

vi.mock('components/Pages/Customer/CustomerRecordElements', () => ({
    CustomerRecordProductImage: ({
        image,
        imageAlt,
        link,
        tooltipLabel,
    }: {
        image?: string;
        imageAlt: string;
        link?: string;
        tooltipLabel?: string;
    }) => (
        <span
            data-image={image ?? 'fallback'}
            data-link={link ?? 'none'}
            data-testid="product-image"
            data-tooltip={tooltipLabel}
        >
            {imageAlt}
        </span>
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
            uuid: 'order-item-uuid',
            name: 'Product without image',
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
        expect(screen.getByTestId('product-image')).toHaveTextContent('Product without image');
    });

    test('shows an item whose product is no longer available by the name of the item, without a link', () => {
        const item = {
            __typename: 'OrderItem',
            uuid: 'order-item-uuid',
            name: 'Hidden product',
            quantity: 1,
            product: null,
        } as TypeOrderItemFragment;

        render(
            <OrderItemProducts
                items={[item]}
                orderLink={{ pathname: '/customer/orders', query: { orderNumber: '123' } }}
            />,
        );

        const productImage = screen.getByTestId('product-image');

        expect(productImage).toHaveAttribute('data-image', 'fallback');
        expect(productImage).toHaveAttribute('data-link', 'none');
        expect(productImage).toHaveAttribute('data-tooltip', 'Hidden product');
        expect(productImage).toHaveTextContent('Hidden product');
    });

    test('wraps product previews in a row and links to remaining products', () => {
        const orderLink = {
            pathname: '/customer/order/[orderNumber]',
            query: { orderNumber: '123456' },
        };

        const items = Array.from({ length: 5 }, (_, index) => {
            const productNumber = index + 1;

            return {
                uuid: `order-item-${productNumber}`,
                name: `Product ${productNumber}`,
                product: {
                    isVisible: true,
                    link: `/product-${productNumber}`,
                    mainImage: {
                        name: `Product ${productNumber}`,
                        url: `/product-${productNumber}.jpg`,
                    },
                    name: `Product ${productNumber}`,
                },
                quantity: 1,
            } as TypeOrderItemFragment;
        });

        render(<OrderItemProducts items={items} orderLink={orderLink} />);

        const productPreviews = screen.getByText('Product 1').parentElement;

        expect(productPreviews).toHaveClass('flex', 'flex-wrap', 'gap-3');
        expect(screen.getByText('Product 4')).toBeInTheDocument();
        expect(screen.queryByText('Product 5')).not.toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Next' })).toBeInTheDocument();
    });
});
