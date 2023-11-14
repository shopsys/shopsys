import { useIsUserLoggedIn } from './auth/useIsUserLoggedIn';
import {
    ProductListTypeEnumApi,
    useAddProductToWishlistMutationApi,
    useCleanProductListMutationApi,
    useRemoveProductFromWishlistMutationApi,
    useWishlistQueryApi,
} from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';

export const useWishlist = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();

    const updateWishlistUuid = usePersistStore((s) => s.updateWishlistUuid);
    const wishlistUuid = usePersistStore((s) => s.wishlistUuid);

    const [, addProductToList] = useAddProductToWishlistMutationApi();
    const [, removeProductFromList] = useRemoveProductFromWishlistMutationApi();
    const [, cleanList] = useCleanProductListMutationApi();

    const [{ data: wishlistData, fetching }] = useWishlistQueryApi({
        variables: {
            input: {
                type: ProductListTypeEnumApi.WishlistApi,
                uuid: wishlistUuid,
            },
        },
        pause: !wishlistUuid && !isUserLoggedIn,
    });

    const handleCleanWishlist = async () => {
        const cleanWishlistResult = await cleanList({
            input: {
                type: ProductListTypeEnumApi.WishlistApi,
                uuid: wishlistUuid,
            },
        });

        if (cleanWishlistResult.error) {
            showErrorMessage(t('Unable to clean wishlist.'));
        } else {
            showSuccessMessage(t('Wishlist was cleaned.'));
            updateWishlistUuid(null);
        }
    };

    const handleAddToWishlist = async (productUuid: string) => {
        const addProductToWishlistResult = await addProductToList({
            input: {
                productUuid,
                productListInput: {
                    uuid: wishlistUuid,
                    type: ProductListTypeEnumApi.WishlistApi,
                },
            },
        });

        if (addProductToWishlistResult.error) {
            showErrorMessage(t('Unable to add product to wishlist.'));
        } else {
            showSuccessMessage(t('The item has been added to your wishlist.'));
            updateWishlistUuid(addProductToWishlistResult.data?.AddProductToList.uuid ?? null);
        }
    };

    const handleRemoveFromWishlist = async (productUuid: string) => {
        const removeProductFromWishlistResult = await removeProductFromList({
            input: {
                productUuid,
                productListInput: {
                    uuid: wishlistUuid,
                    type: ProductListTypeEnumApi.WishlistApi,
                },
            },
        });

        if (removeProductFromWishlistResult.error) {
            showErrorMessage(t('Unable to remove product from wishlist.'));
        } else {
            if (!removeProductFromWishlistResult.data?.RemoveProductFromList) {
                updateWishlistUuid(null);
            }
            showSuccessMessage(t('The item has been removed from your wishlist.'));
        }
    };

    const isProductInWishlist = (productUuid: string) =>
        !!wishlistData?.productList?.products.find((product) => product.uuid === productUuid);

    const toggleProductInWishlist = (productUuid: string) => {
        if (isProductInWishlist(productUuid)) {
            handleRemoveFromWishlist(productUuid);
        } else {
            handleAddToWishlist(productUuid);
        }
    };

    return {
        wishlist: wishlistData?.productList,
        fetching,
        isProductInWishlist,
        handleCleanWishlist,
        toggleProductInWishlist,
    };
};
