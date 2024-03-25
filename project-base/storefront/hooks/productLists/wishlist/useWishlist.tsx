import { ProductListTypeEnum } from 'graphql/types';
import { showErrorMessage } from 'helpers/toasts/showErrorMessage';
import { showSuccessMessage } from 'helpers/toasts/showSuccessMessage';
import { useProductList } from 'hooks/productLists/useProductList';
import { useUpdateProductListUuid } from 'hooks/productLists/useUpdateProductListUuid';
import useTranslation from 'next-translate/useTranslation';

export const useWishlist = () => {
    const { t } = useTranslation();
    const updateWishlistUuid = useUpdateProductListUuid(ProductListTypeEnum.Wishlist);

    const { productListData, removeList, isProductInList, toggleProductInList, fetching } = useProductList(
        ProductListTypeEnum.Wishlist,

        {
            addProductError: () => showErrorMessage(t('Unable to add product to wishlist.')),
            addProductSuccess: (result) => {
                showSuccessMessage(t('The item has been added to your wishlist.'));
                updateWishlistUuid(result?.uuid ?? null);
            },
            removeError: () => showErrorMessage(t('Unable to clean wishlist.')),
            removeSuccess: () => {
                showSuccessMessage(t('Wishlist was cleaned.'));
                updateWishlistUuid(null);
            },
            removeProductError: () => showErrorMessage(t('Unable to remove product from wishlist.')),
            removeProductSuccess: (result) => {
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
        removeWishlist: removeList,
        toggleProductInWishlist: toggleProductInList,
    };
};
