import { TypeProductListTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useProductList } from 'utils/productLists/useProductList';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const useWishlist = () => {
    const { t } = useTranslation();
    const updateWishlistUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Wishlist);

    const { productListData, removeList, isProductInList, toggleProductInList, isProductListFetching } = useProductList(
        TypeProductListTypeEnum.Wishlist,
        {
            addProductError: () => showErrorMessage(t('Unable to add product to wishlist.')),
            addProductSuccess: (result) => {
                showSuccessMessage(t('The item has been added to your wishlist.'));
                updateWishlistUuid(result?.uuid ?? null);
                dispatchBroadcastChannel('refetchWishedProducts');
            },
            removeError: () => showErrorMessage(t('Unable to clean wishlist.')),
            removeSuccess: () => {
                showSuccessMessage(t('Wishlist was cleaned.'));
                updateWishlistUuid(null);
                dispatchBroadcastChannel('reloadPage');
            },
            removeProductError: () => showErrorMessage(t('Unable to remove product from wishlist.')),
            removeProductSuccess: (result) => {
                if (!result) {
                    updateWishlistUuid(null);
                    dispatchBroadcastChannel('reloadPage');
                }
                showSuccessMessage(t('The item has been removed from your wishlist.'));
                dispatchBroadcastChannel('refetchWishedProducts');
            },
        },
    );

    return {
        wishlist: productListData?.productList,
        isProductListFetching,
        isProductInWishlist: isProductInList,
        removeWishlist: removeList,
        toggleProductInWishlist: toggleProductInList,
    };
};
