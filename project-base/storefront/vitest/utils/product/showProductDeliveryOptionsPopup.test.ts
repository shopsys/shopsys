import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeProductTypeEnum } from 'graphql/types';
import { showProductDeliveryOptionsPopup } from 'utils/product/showProductDeliveryOptionsPopup';
import { describe, expect, test } from 'vitest';

const sellableProduct = {
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
    isMainVariant: false,
    productType: TypeProductTypeEnum.Basic,
} as unknown as TypeProductDetailFragment;

describe('showProductDeliveryOptionsPopup', () => {
    test.each([
        { name: 'a sellable basic product', product: sellableProduct, expected: true },
        {
            name: 'a printed gift voucher',
            product: { ...sellableProduct, productType: TypeProductTypeEnum.PrintedGiftVoucher },
            expected: true,
        },
        {
            name: 'an electronic gift voucher, which is delivered by email',
            product: { ...sellableProduct, productType: TypeProductTypeEnum.ElectronicGiftVoucher },
            expected: false,
        },
        { name: 'a sold out product', product: { ...sellableProduct, isCurrentlyOutOfStock: true }, expected: false },
        { name: 'a main variant', product: { ...sellableProduct, isMainVariant: true }, expected: false },
    ])('returns $expected for $name', ({ product, expected }) => {
        expect(showProductDeliveryOptionsPopup(product)).toBe(expected);
    });
});
