import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ProductInterfaceType } from 'types/product';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useWishlist = () => {
    const { t } = useTranslation();
    const updateWishlistUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Wishlist);
    const {
        clearProductListGtmContext,
        pushAddProductListGtmEvent,
        pushRemoveProductListGtmEvent,
        toggleProductInListWithGtm,
    } = useProductListGtmEvent(GtmEventType.add_to_wishlist, GtmEventType.remove_from_wishlist);

    const { productListData, removeList, isProductInList, toggleProductInList, isProductListFetching } = useProductList(
        TypeProductListTypeEnum.Wishlist,
        {
            addProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to add product to wishlist.'));
            },
            addProductSuccess: (result, productUuid) => {
                showSuccessMessage(t('The item has been added to your wishlist.'));
                updateWishlistUuid(result?.uuid ?? null);
                pushAddProductListGtmEvent(productUuid);
            },
            removeError: () => showErrorMessage(t('Unable to clean wishlist.')),
            removeSuccess: () => {
                showSuccessMessage(t('Wishlist was cleaned.'));
                updateWishlistUuid(null);
            },
            removeProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to remove product from wishlist.'));
            },
            removeProductSuccess: (result, productUuid) => {
                if (!result) {
                    updateWishlistUuid(null);
                }
                showSuccessMessage(t('The item has been removed from your wishlist.'));
                pushRemoveProductListGtmEvent(productUuid);
            },
        },
    );

    const toggleProductInWishlist = (
        product: ProductInterfaceType,
        gtmProductListName: GtmProductListNameType,
        listIndex?: number,
    ) => {
        toggleProductInListWithGtm(product, gtmProductListName, listIndex, toggleProductInList);
    };

    return {
        wishlist: productListData?.productList,
        isProductListFetching,
        isProductInWishlist: isProductInList,
        removeWishlist: removeList,
        toggleProductInWishlist,
    };
};
