import { render, screen } from '@testing-library/react';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateSummary', () => ({
    ExpectedDeliveryDateSummary: () => null,
}));

vi.mock('components/Blocks/OrderItemProductCard/OrderItemProductCard', () => ({
    OrderItemProductCard: ({ fullName, price }: { fullName: string; price: TypeProductPriceFragment }) => (
        <span data-price={price.priceWithVat} data-testid="product-card">
            {fullName}
        </span>
    ),
}));

vi.mock('components/Blocks/OrderItemGiftCard/OrderItemGiftCard', () => ({
    OrderItemGiftCard: ({ fullName, price }: { fullName: string; price: TypeProductPriceFragment }) => (
        <span data-price={price.priceWithVat} data-testid="gift-card">
            {fullName}
        </span>
    ),
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string) => price,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const createItem = (type: TypeOrderItemTypeEnum, name: string, unitPriceWithVat: string) =>
    ({
        uuid: name,
        name,
        type,
        quantity: 1,
        unit: 'pcs',
        unitPrice: { __typename: 'Price', priceWithVat: unitPriceWithVat, priceWithoutVat: '0', vatAmount: '0' },
        relatedItems: [],
        product: null,
    }) as unknown as TypeOrderDetailItemFragment;

describe('OrderConfirmationProducts', () => {
    test('shows items whose product is no longer available with the prices of the order', () => {
        render(
            <OrderConfirmationProducts
                expectedDeliveryDate={null}
                items={[
                    createItem(TypeOrderItemTypeEnum.Product, 'Hidden product', '100'),
                    createItem(TypeOrderItemTypeEnum.ProductGift, 'Hidden gift', '1'),
                ]}
            />,
        );

        expect(screen.getByTestId('product-card')).toHaveTextContent('Hidden product');
        expect(screen.getByTestId('product-card')).toHaveAttribute('data-price', '100');
        expect(screen.getByTestId('gift-card')).toHaveTextContent('Hidden gift');
        expect(screen.getByTestId('gift-card')).toHaveAttribute('data-price', '1');
    });
});
