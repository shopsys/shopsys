import { usePersistStore } from 'store/usePersistStore';

export const useProductListUuids = (): {
    getAllProductListUuids: () => string[];
    removeAllProductListUuids: () => void;
} => {
    const wishlistUuid = usePersistStore((store) => store.wishlistUuid);
    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);
    const updateWishlistUuid = usePersistStore((store) => store.updateWishlistUuid);
    const updateComparisonUuid = usePersistStore((store) => store.updateComparisonUuid);

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

    const removeAllProductListUuids = () => {
        updateWishlistUuid(null);
        updateComparisonUuid(null);
    };

    return { getAllProductListUuids, removeAllProductListUuids };
};
