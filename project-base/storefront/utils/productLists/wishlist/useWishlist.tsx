import { useProductLists } from 'components/providers/ProductListsProvider';
import {
    TypeWishlistPageQuery,
    TypeWishlistPageQueryVariables,
    WishlistPageQueryDocument,
} from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ProductInterfaceType } from 'types/product';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { useProductListQueryResult } from 'utils/productLists/useProductListQueryResult';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { getWishlistPageRange } from './getWishlistPageRange';

export const useWishlist = () => {
    const { t } = useTranslation();
    const {
        clearProductListGtmContext,
        pushAddProductListGtmEvent,
        pushRemoveProductListGtmEvent,
        toggleProductInListWithGtm,
    } = useProductListGtmEvent(GtmEventType.add_to_wishlist, GtmEventType.remove_from_wishlist);

    const { removeList, isProductInList, toggleProductInList } = useProductList(TypeProductListTypeEnum.Wishlist, {
        addProductError: (productUuid) => {
            clearProductListGtmContext(productUuid);
            showErrorMessage(t('Unable to add product to wishlist.'));
        },
        addProductSuccess: (productUuid) => {
            showSuccessMessage(t('The item has been added to your wishlist.'));
            pushAddProductListGtmEvent(productUuid);
        },
        removeError: () => showErrorMessage(t('Unable to clean wishlist.')),
        removeSuccess: () => {
            showSuccessMessage(t('Wishlist was cleaned.'));
        },
        removeProductError: (productUuid) => {
            clearProductListGtmContext(productUuid);
            showErrorMessage(t('Unable to remove product from wishlist.'));
        },
        removeProductSuccess: (productUuid) => {
            showSuccessMessage(t('The item has been removed from your wishlist.'));
            pushRemoveProductListGtmEvent(productUuid);
        },
    });

    const toggleProductInWishlist = (
        product: ProductInterfaceType,
        gtmProductListName: GtmProductListNameType,
        listIndex?: number,
    ) => {
        toggleProductInListWithGtm(product, gtmProductListName, listIndex, toggleProductInList);
    };

    return {
        isProductInWishlist: isProductInList,
        removeWishlist: removeList,
        toggleProductInWishlist,
    };
};

export const useWishlistPage = (requestedPage: number, requestedLoadMore: number) => {
    const { lists, visible } = useProductLists();
    const metadata = lists.WISHLIST;
    const range = getWishlistPageRange(
        requestedPage,
        requestedLoadMore,
        metadata.hasMetadata ? metadata.count : undefined,
    );
    const hasProducts = !!metadata.summary && metadata.count > 0;
    const result = useProductListQueryResult<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>({
        query: WishlistPageQueryDocument,
        variables: { input: metadata.input, offset: range.offset, limit: range.limit },
        pause: !visible || !metadata.readyForDetails || !hasProducts,
        selectionKey: metadata.selectionKey,
        revision: metadata.revision,
    });
    return {
        wishlist: hasProducts ? result.data?.productList : metadata.summary === null ? null : undefined,
        totalCount: metadata.count,
        page: range.page,
        loadMore: range.loadMore,
        isProductListFetching: metadata.fetching || (hasProducts && result.fetching),
        hasProductListError: metadata.error || !!result.error,
    };
};
