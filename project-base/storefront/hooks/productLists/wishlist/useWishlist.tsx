import { ProductListTypeEnumApi } from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useProductList } from 'hooks/productLists/useProductList';
import { useUpdateProductListUuid } from 'hooks/productLists/useUpdateProductListUuid';
import useTranslation from 'next-translate/useTranslation';

export const useWishlist = () => {
    const { t } = useTranslation();
    const updateWishlistUuid = useUpdateProductListUuid(ProductListTypeEnumApi.WishlistApi);

    const { productListData, cleanList, isProductInList, toggleProductInList, fetching } = useProductList(
        ProductListTypeEnumApi.WishlistApi,

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
        wishlist: productListData?.productList,
        fetching,
        isProductInWishlist: isProductInList,
        cleanWishlist: cleanList,
        toggleProductInWishlist: toggleProductInList,
    };
};
