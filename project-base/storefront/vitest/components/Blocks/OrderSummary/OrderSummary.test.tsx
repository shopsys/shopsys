import { render, screen, within } from '@testing-library/react';
import { OrderSummary } from 'components/Blocks/OrderSummary/OrderSummary';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));
vi.mock('components/Pages/Cart/CartPreview', () => ({ CartPreview: () => null }));
vi.mock('components/Blocks/Product/ProductAvailability', () => ({ ProductAvailability: () => <span>In stock</span> }));
vi.mock('utils/formatting/useFormatPrice', () => ({ useFormatPrice: () => (price: string | number) => `€${price}` }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { products: string }) => key.replace('{{ products }}', options?.products ?? ''),
    }),
}));
vi.mock('utils/cart/useCurrentCart', () => ({ useCurrentCart: () => ({ cart, isCartFetchingOrUnavailable: false }) }));

const price = { priceWithVat: '2', basicPrice: { priceWithVat: '2' }, percentageDiscount: null };
const item = (
    uuid: string,
    fullName: string,
    type = TypeCartItemTypeEnum.Product,
    itemUuid = uuid,
    relatedItems: { uuid: string }[] = [],
) => ({
    uuid: itemUuid,
    type,
    relatedItems,
    quantity: 2,
    freeQuantity: 0,
    additionalServices: [],
    product: {
        uuid,
        fullName,
        price,
        giftPrice: price,
        mainImage: null,
        unit: { name: 'pcs' },
        gifts: [],
        stockQuantity: 100,
        isCurrentlyOutOfStock: false,
    },
});
const cart = {
    items: [
        item('first', 'First product', TypeCartItemTypeEnum.Product, 'first', [{ uuid: 'first-gift' }]),
        item('second', 'Second product', TypeCartItemTypeEnum.Product, 'second', [{ uuid: 'second-gift' }]),
        item('gift', 'Shared gift', TypeCartItemTypeEnum.ProductGift, 'first-gift'),
        item('gift', 'Shared gift', TypeCartItemTypeEnum.ProductGift, 'second-gift'),
    ],
    removedProductGifts: [
        { giftProduct: { uuid: 'removed', fullName: 'Declined gift' }, mainProduct: { uuid: 'first' } },
    ],
};

describe('OrderSummary', () => {
    test('nests a gift shared by two products once under each of them, excluding declined gifts and controls', () => {
        render(<OrderSummary activeStep={2} />);

        const firstParent = screen.getByText('First product').closest('li')!;
        const secondParent = screen.getByText('Second product').closest('li')!;
        expect(within(firstParent).getByText('Shared gift')).toBeInTheDocument();
        expect(within(secondParent).getByText('Shared gift')).toBeInTheDocument();
        expect(screen.getAllByText('Shared gift')).toHaveLength(2);
        expect(screen.queryByText(/Gift for/)).not.toBeInTheDocument();
        expect(screen.queryByText('Declined gift')).not.toBeInTheDocument();
        expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
    });
});
