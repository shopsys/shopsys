import { render, screen } from '@testing-library/react';
import { OrderItemProducts } from 'components/Pages/Customer/Orders/OrderItemProducts';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children }: { children: ReactNode }) => <a href="/order-detail">{children}</a>,
}));

vi.mock('components/Pages/Customer/CustomerRecordElements', () => ({
    CustomerRecordProductImage: ({ imageAlt }: { imageAlt: string }) => <span>{imageAlt}</span>,
    CustomerRecordRowInfo: ({ children, title }: { children: ReactNode; title: string }) => (
        <section>
            <h2>{title}</h2>
            {children}
        </section>
    ),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

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

describe('OrderItemProducts', () => {
    test('wraps product previews in a row and links to remaining products', () => {
        render(<OrderItemProducts items={items} orderLink={orderLink} />);

        const productPreviews = screen.getByText('Product 1').parentElement;

        expect(productPreviews).toHaveClass('flex', 'flex-wrap', 'gap-3');
        expect(screen.getByText('Product 4')).toBeInTheDocument();
        expect(screen.queryByText('Product 5')).not.toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Next' })).toBeInTheDocument();
    });
});
