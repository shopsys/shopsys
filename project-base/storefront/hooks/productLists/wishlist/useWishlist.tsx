import {
    ProductListTypeEnumApi,
    useAddProductToWishlistMutationApi,
    useCleanProductListMutationApi,
    useRemoveProductFromWishlistMutationApi,
    useWishlistQueryApi,
} from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useProductList } from 'hooks/productLists/useProductList';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useWishlist = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();

    const updateWishlistUuid = usePersistStore((s) => s.updateWishlistUuid);
    const wishlistUuid = usePersistStore((s) => s.wishlistUuid);

    const [, addProductToListMutation] = useAddProductToWishlistMutationApi();
    const [, removeProductFromListMutation] = useRemoveProductFromWishlistMutationApi();
    const [, cleanListMutation] = useCleanProductListMutationApi();

    const [{ data: wishlistData, fetching }] = useWishlistQueryApi({
        variables: {
            input: {
                type: ProductListTypeEnumApi.WishlistApi,
                uuid: wishlistUuid,
            },
        },
        pause: !wishlistUuid && !isUserLoggedIn,
    });

    useEffect(() => {
        if (wishlistData?.productList?.uuid) {
            updateWishlistUuid(wishlistData.productList.uuid);
        }
    }, [wishlistData?.productList?.uuid]);

    const { cleanList, isProductInList, toggleProductInList } = useProductList(
        ProductListTypeEnumApi.WishlistApi,
        wishlistUuid,
        wishlistData,
        {
            cleanList: cleanListMutation,
            removeProductFromList: removeProductFromListMutation,
            addProductToList: addProductToListMutation,
        },
        {
            addError: () => showErrorMessage(t('Unable to add product to wishlist.')),
            addSuccess: (result) => {
                showSuccessMessage(t('The item has been added to your wishlist.'));
                updateWishlistUuid(result?.uuid ?? null);
            },
            cleanError: () => showErrorMessage(t('Unable to clean wishlist.')),
            cleanSuccess: () => {
                showSuccessMessage(t('Wishlist was cleaned.'));
                updateWishlistUuid(null);
            },
            removeError: () => showErrorMessage(t('Unable to remove product from wishlist.')),
            removeSuccess: (result) => {
                if (!result) {
                    updateWishlistUuid(null);
                }
                showSuccessMessage(t('The item has been removed from your wishlist.'));
            },
        },
    );

    return {
        wishlist: wishlistData?.productList,
        fetching,
        isProductInWishlist: isProductInList,
        cleanWishlist: cleanList,
        toggleProductInWishlist: toggleProductInList,
    };
};
