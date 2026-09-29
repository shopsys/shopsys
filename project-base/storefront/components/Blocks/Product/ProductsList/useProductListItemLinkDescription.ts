import type { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useId } from 'react';
import { isPriceVisible } from 'utils/mappers/price';
import type { ProductVisibleItemsConfigType } from './ProductListItem';

export const useProductListItemLinkDescription = (
    product: TypeCompactProductFragment | TypeListedProductFragment,
    visibleItemsConfig: ProductVisibleItemsConfigType,
) => {
    const priceDescriptionId = useId();
    const availabilityDescriptionId = useId();
    const isPriceDescriptionVisible =
        visibleItemsConfig.price &&
        !(product.isMainVariant && product.isSellingDenied) &&
        isPriceVisible(product.price.priceWithVat);
    const isAvailabilityDescriptionVisible =
        visibleItemsConfig.storeAvailability &&
        !product.isSellingDenied &&
        'isInquiryType' in product &&
        !product.isInquiryType;
    const productLinkAriaDescribedBy = [
        isPriceDescriptionVisible ? priceDescriptionId : undefined,
        isAvailabilityDescriptionVisible ? availabilityDescriptionId : undefined,
    ]
        .filter(Boolean)
        .join(' ');

    return {
        availabilityDescriptionId,
        priceDescriptionId,
        productLinkAriaDescribedBy: productLinkAriaDescribedBy || undefined,
    };
};
