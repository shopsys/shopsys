import { render, screen, within } from '@testing-library/react';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeAvailabilityStatusEnum, TypeOrderItemTypeEnum, TypeProductTypeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));
vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => (Number(price) === 0 ? 'Free' : `€${Number(price).toFixed(2)}`),
}));
vi.mock('utils/formatting/useFormatDate', () => ({ useFormatDate: () => ({ formatDate: (date: string) => date }) }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const currentProduct = {
    fullName: 'Current name',
    uuid: 'product',
    catalogNumber: 'CAT',
    slug: '/product',
    isVisible: true,
    isSellingDenied: false,
    isInquiryType: false,
    productType: TypeProductTypeEnum.Basic,
    isCurrentlyOutOfStock: false,
    promotionBuyQuantity: null,
    promotionFreeQuantity: null,
    mainCategory: null,
    categories: [],
    availability: { name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    mainImage: null,
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '99',
        priceWithoutVat: '99',
        vatAmount: '0',
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        basicPrice: { priceWithVat: '99', priceWithoutVat: '99', vatAmount: '0' },
    },
    giftPrice: {
        __typename: 'ProductPrice',
        priceWithVat: '99',
        priceWithoutVat: '99',
        vatAmount: '0',
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        basicPrice: { priceWithVat: '99', priceWithoutVat: '99', vatAmount: '0' },
    },
};

const createItem = (
    type: TypeOrderItemTypeEnum,
    unitPrice: string,
    totalPrice: string,
): TypeOrderDetailItemFragment => ({
    __typename: 'OrderItem',
    uuid: 'item',
    catnum: 'CAT',
    vatRate: '0',
    order: { uuid: 'order', number: '1', creationDate: '', customerUser: null, withdrawalRequest: null },
    transport: null,
    payment: null,
    name: 'Purchased item',
    type,
    quantity: 3,
    unit: 'pcs',
    relatedItems: [],
    unitPrice: { __typename: 'Price', priceWithVat: unitPrice, priceWithoutVat: unitPrice, vatAmount: '0' },
    totalPrice: { __typename: 'Price', priceWithVat: totalPrice, priceWithoutVat: totalPrice, vatAmount: '0' },
    product: currentProduct,
});

describe('OrderConfirmationProducts', () => {
    test('shows unavailable products and gifts together with the prices of the order', () => {
        render(
            <OrderConfirmationProducts
                expectedDeliveryDate={null}
                items={[
                    {
                        ...createItem(TypeOrderItemTypeEnum.Product, '100', '100'),
                        uuid: 'hidden-product',
                        name: 'Hidden product',
                        quantity: 1,
                        product: null,
                    },
                    {
                        ...createItem(TypeOrderItemTypeEnum.ProductGift, '1', '1'),
                        uuid: 'hidden-gift',
                        name: 'Hidden gift',
                        quantity: 1,
                        product: null,
                    },
                ]}
            />,
        );

        const [product, gift] = screen.getAllByRole('listitem');
        expect(product).toHaveTextContent('Hidden product');
        expect(product).toHaveTextContent('€100.00');
        expect(gift).toHaveTextContent('Hidden gift');
        expect(gift).toHaveTextContent('€1.00');
    });

    test.each([
        TypeOrderItemTypeEnum.Product,
        TypeOrderItemTypeEnum.ProductGift,
    ])('uses saved unit and total prices for %s after catalog prices change', (type) => {
        render(<OrderConfirmationProducts expectedDeliveryDate={null} items={[createItem(type, '2', '5.99')]} />);

        expect(screen.getByText('€2.00')).toBeInTheDocument();
        expect(screen.getByText('€5.99')).toBeInTheDocument();
        expect(screen.queryByText('€99.00')).not.toBeInTheDocument();
        expect(screen.queryByText('€6.00')).not.toBeInTheDocument();
    });

    test.each([
        TypeOrderItemTypeEnum.Product,
        TypeOrderItemTypeEnum.ProductGift,
    ])('keeps %s visible after its product is deleted', (type) => {
        const item = { ...createItem(type, '2', '6'), product: null };

        render(<OrderConfirmationProducts expectedDeliveryDate={null} items={[item]} />);

        expect(screen.getByText('Purchased item')).toBeInTheDocument();
        expect(screen.getByText('3 pcs')).toBeInTheDocument();
        expect(screen.getByText('€6.00')).toBeInTheDocument();
        expect(screen.getByRole('img')).toBeInTheDocument();
    });

    test('shows a historically free gift once as Free even when it is now paid', () => {
        render(
            <OrderConfirmationProducts
                expectedDeliveryDate={null}
                items={[createItem(TypeOrderItemTypeEnum.ProductGift, '0', '0')]}
            />,
        );

        const gift = screen.getByRole('listitem');
        expect(within(gift).getAllByText('Free')).toHaveLength(1);
        expect(within(gift).queryByText('/ pcs')).not.toBeInTheDocument();
        expect(within(gift).queryByRole('checkbox')).not.toBeInTheDocument();
        expect(within(gift).getByText('Gift with purchase')).toBeInTheDocument();
    });

    test('does not reveal hidden order prices', () => {
        render(
            <OrderConfirmationProducts
                expectedDeliveryDate={null}
                items={[createItem(TypeOrderItemTypeEnum.ProductGift, '***', '***')]}
            />,
        );

        expect(screen.getByText('Purchased item')).toBeInTheDocument();
        expect(screen.queryByText(/€|NaN|Free/)).not.toBeInTheDocument();
    });
});
