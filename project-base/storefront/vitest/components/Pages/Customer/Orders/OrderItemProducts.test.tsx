import { render, screen } from '@testing-library/react';
import { OrderItemProducts } from 'components/Pages/Customer/Orders/OrderItemProducts';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children }: { children: ReactNode }) => <a href="/order-detail">{children}</a>,
}));

vi.mock('components/Pages/Customer/CustomerRecordElements', () => ({
    CustomerRecordProductImage: ({ image, imageAlt }: { image?: string; imageAlt: string }) => (
        <span data-image={image ?? 'fallback'} data-testid="product-image">
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

    test('wraps product previews in a row and links to remaining products', () => {
        const orderLink = {
            pathname: '/customer/order/[orderNumber]',
            query: { orderNumber: '123456' },
        };

        const items = Array.from({ length: 5 }, (_, index) => {
            const productNumber = index + 1;

            return {
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
