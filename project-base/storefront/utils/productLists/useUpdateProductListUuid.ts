import { ProductListTypeEnum } from 'graphql/types';
import { usePersistStore } from 'store/usePersistStore';

export const useUpdateProductListUuid = (productListType: ProductListTypeEnum) => {
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const updateProductListUuid = (productListUuid: string | null) => {
        const updatedProductListUuids = {
            ...productListUuids,
        };

        if (productListUuid) {
            updatedProductListUuids[productListType] = productListUuid;
        } else {
            delete updatedProductListUuids[productListType];
        }

        updateProductListUuids(updatedProductListUuids);
    };

    return updateProductListUuid;
};
