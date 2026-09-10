import { renderHook } from '@testing-library/react';
import { useProductListItemLinkDescription } from 'components/Blocks/Product/ProductsList/useProductListItemLinkDescription';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { describe, expect, test } from 'vitest';

type ProductOverrides = {
    isInquiryType?: boolean;
    isMainVariant?: boolean;
    isSellingDenied?: boolean;
    priceWithVat?: string;
};

const createProduct = ({
    isInquiryType = false,
    isMainVariant = false,
    isSellingDenied = false,
    priceWithVat = '100',
}: ProductOverrides = {}) =>
    ({
        isInquiryType,
        isMainVariant,
        isSellingDenied,
        price: { priceWithVat },
    }) as TypeListedProductFragment;

describe('useProductListItemLinkDescription', () => {
    test('describes the product link with every rendered product detail', () => {
        const { result } = renderHook(() =>
            useProductListItemLinkDescription(createProduct(), { price: true, storeAvailability: true }),
        );

        expect(result.current.productLinkAriaDescribedBy).toBe(
            `${result.current.priceDescriptionId} ${result.current.availabilityDescriptionId}`,
        );
    });

    test('describes the product link only with details enabled in the visible items configuration', () => {
        const { result: priceResult } = renderHook(() =>
            useProductListItemLinkDescription(createProduct(), { price: true }),
        );
        const { result: availabilityResult } = renderHook(() =>
            useProductListItemLinkDescription(createProduct(), { storeAvailability: true }),
        );

        expect(priceResult.current.productLinkAriaDescribedBy).toBe(priceResult.current.priceDescriptionId);
        expect(availabilityResult.current.productLinkAriaDescribedBy).toBe(
            availabilityResult.current.availabilityDescriptionId,
        );
    });

    test('omits descriptions which have no rendered content', () => {
        const { result } = renderHook(() =>
            useProductListItemLinkDescription(createProduct({ isInquiryType: true, priceWithVat: '***' }), {
                price: true,
                storeAvailability: true,
            }),
        );

        expect(result.current.productLinkAriaDescribedBy).toBeUndefined();
    });
});
