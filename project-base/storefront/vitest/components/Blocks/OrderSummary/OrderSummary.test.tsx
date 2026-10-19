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
const item = (uuid: string, fullName: string, type = TypeCartItemTypeEnum.Product) => ({
    uuid,
    type,
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
        {
            ...item('first', 'First product'),
            product: { ...item('first', 'First product').product, gifts: [{ uuid: 'gift' }, { uuid: 'removed' }] },
        },
        {
            ...item('second', 'Second product'),
            product: { ...item('second', 'Second product').product, gifts: [{ uuid: 'gift' }] },
        },
        item('gift', 'Shared gift', TypeCartItemTypeEnum.ProductGift),
    ],
    removedGifts: [{ product: { uuid: 'removed', fullName: 'Declined gift' }, quantity: 2 }],
};

describe('OrderSummary', () => {
    test('nests a shared gift once with both parents, excluding declined gifts and controls', () => {
        render(<OrderSummary activeStep={2} />);

        const parent = screen.getByText('First product').closest('li')!;
        expect(within(parent).getByText('Shared gift')).toBeInTheDocument();
        expect(screen.getAllByText('Shared gift')).toHaveLength(1);
        expect(within(parent).getByText('Gift for: First product, Second product')).toBeInTheDocument();
        expect(screen.queryByText('Declined gift')).not.toBeInTheDocument();
        expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
    });
});
