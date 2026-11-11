import { TypeProductListTypeEnum } from 'graphql/types';
import { usePersistStoreApi } from 'store/usePersistStore';

export const useUpdateProductListUuid = (productListType: TypeProductListTypeEnum) => {
    const store = usePersistStoreApi();

    const updateProductListUuid = (productListUuid: string | null) => {
        const { productListUuids, updateProductListUuids } = store.getState();
        if ((productListUuids[productListType] ?? null) === productListUuid) return;
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
