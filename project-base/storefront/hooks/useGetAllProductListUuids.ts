import { usePersistStore } from 'store/usePersistStore';

export const useGetAllProductListUuids = (): (() => string[]) => {
    const wishlistUuid = usePersistStore((store) => store.wishlistUuid);
    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);

    const getAllProductListUuids = () => {
        const productListsUuids = [];

        if (wishlistUuid) {
            productListsUuids.push(wishlistUuid);
        }
        if (comparisonUuid) {
            productListsUuids.push(comparisonUuid);
        }

        return productListsUuids;
    };

    return getAllProductListUuids;
};
