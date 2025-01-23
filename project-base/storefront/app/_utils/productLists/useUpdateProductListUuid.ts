'use client';

import { useProductList } from 'components/providers/ProductListProvider';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useCookiesStore } from 'store/useCookiesStore';

export const useUpdateProductListUuid = (productListType: TypeProductListTypeEnum) => {
    const productListUuids = useCookiesStore((s) => s.productListUuids);
    const setCookiesStoreState = useCookiesStore((state) => state.setCookiesStoreState);
    const { updateListUuid } = useProductList(productListType);

    const updateProductListUuid = (productListUuid: string | null) => {
        updateListUuid(productListUuid);
        const updatedProductListUuids = {
            ...productListUuids,
        };
        if (productListUuid) {
            updatedProductListUuids[productListType] = productListUuid;
        } else {
            delete updatedProductListUuids[productListType];
        }
        setCookiesStoreState({ productListUuids: updatedProductListUuids as { COMPARISON: string; WISHLIST: string } });
    };

    return updateProductListUuid;
};
