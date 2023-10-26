import { useIsUserLoggedIn } from './auth/useIsUserLoggedIn';
import {
    ListedProductFragmentApi,
    useAddProductToWishlistMutationApi,
    useCleanWishlistMutationApi,
    useRemoveProductFromWishlistMutationApi,
    useSharedWishlistQueryApi,
    useWishlistQueryApi,
} from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useWishlist = () => {
    const { t } = useTranslation();

    const isUserLoggedIn = useIsUserLoggedIn();
    const [isFetchingPaused, setIsFetchingPaused] = useState(true);

    const updateWishlistUuid = usePersistStore((s) => s.updateWishlistUuid);
    const wishlistUuid = usePersistStore((s) => s.wishlistUuid);

    const [, addProductToWishlist] = useAddProductToWishlistMutationApi();
    const [, removeProductFromWishlist] = useRemoveProductFromWishlistMutationApi();
    const [, cleanWishlist] = useCleanWishlistMutationApi();
    const [{ data: wishlistData, fetching }] = useWishlistQueryApi({
        variables: { wishlistUuid },
        pause: isFetchingPaused,
    });

    useEffect(() => {
        setIsFetchingPaused(!wishlistUuid && !isUserLoggedIn);
    }, [wishlistUuid]);

    useEffect(() => {
        if (!isUserLoggedIn && wishlistData?.wishlist && wishlistUuid !== wishlistData.wishlist.uuid) {
            updateWishlistUuid(wishlistData.wishlist.uuid);
        }
    }, [wishlistData?.wishlist?.uuid]);

    const handleCleanWishlist = async () => {
        const cleanWishlistResult = await cleanWishlist({ wishlistUuid });

        if (cleanWishlistResult.error) {
            showErrorMessage(t('Unable to clean wishlist.'));
        } else {
            showSuccessMessage(t('Wishlist was cleaned.'));
            updateWishlistUuid(null);
        }
    };

    const handleAddToWishlist = async (productUuid: string) => {
        const addProductToWishlistResult = await addProductToWishlist({
            productUuid,
            wishlistUuid: isUserLoggedIn ? null : wishlistUuid,
        });

        if (addProductToWishlistResult.error) {
            showErrorMessage(t('Unable to add product to wishlist.'));
        } else {
            showSuccessMessage(t('The item has been added to your wishlist.'));
            updateWishlistUuid(addProductToWishlistResult.data?.addProductToWishlist.uuid ?? null);
        }
    };

    const handleRemoveFromWishlist = async (productUuid: string) => {
        const removeProductFromWishlistResult = await removeProductFromWishlist({ productUuid, wishlistUuid });

        if (removeProductFromWishlistResult.error) {
            showErrorMessage(t('Unable to remove product from wishlist.'));
        } else {
            if (!removeProductFromWishlistResult.data?.removeProductFromWishlist) {
                updateWishlistUuid(null);
            }
            showSuccessMessage(t('The item has been removed from your wishlist.'));
        }
    };

    const isProductInWishlist = (productUuid: string) =>
        !!wishlistData?.wishlist?.products.find((product) => product.uuid === productUuid);

    const toggleProductInWishlist = (productUuid: string) => {
        if (isProductInWishlist(productUuid)) {
            handleRemoveFromWishlist(productUuid);
        } else {
            handleAddToWishlist(productUuid);
        }
    };

    return {
        wishlist: wishlistData?.wishlist,
        fetching,
        isProductInWishlist,
        handleCleanWishlist,
        toggleProductInWishlist,
    };
};

export const useSharedWishlist = (catnums: string[]): { products: ListedProductFragmentApi[]; fetching: boolean } => {
    const [{ data, fetching }] = useSharedWishlistQueryApi({
        variables: { catnums },
    });

    if (!data?.productsByCatnums) {
        return { products: [], fetching };
    }

    return {
        products: data.productsByCatnums,
        fetching,
    };
};
