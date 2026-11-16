import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { useProductLists } from 'components/providers/ProductListsProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    TypeWishlistPageQuery,
    TypeWishlistPageQueryVariables,
    WishlistPageQueryDocument,
} from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useState } from 'react';
import { ProductInterfaceType } from 'types/product';
import { useClient } from 'urql';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { useProductListQueryResult } from 'utils/productLists/useProductListQueryResult';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useLatest } from 'utils/ui/useLatest';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';
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
    const client = useClient();
    const [refreshVersion, setRefreshVersion] = useState(0);
    const [additionalPages, setAdditionalPages] = useState<{
        key: string;
        pages: TypeListedProductFragment[][];
        refreshVersion: number;
        fetching: boolean;
        error: boolean;
    }>();
    const latestAdditionalPages = useLatest(additionalPages);
    const metadata = lists.WISHLIST;
    const range = getWishlistPageRange(
        requestedPage,
        requestedLoadMore,
        metadata.hasMetadata ? metadata.count : undefined,
    );
    const hasProducts = !!metadata.summary && metadata.count > 0;
    const contentKey = metadata.summary?.productUuids.join(',') ?? '';
    const result = useProductListQueryResult<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>({
        query: WishlistPageQueryDocument,
        variables: { input: metadata.input, endCursor: getEndCursor(range.page), pageSize: DEFAULT_PAGE_SIZE },
        pause: !visible || !metadata.readyForDetails || !hasProducts,
        selectionKey: metadata.selectionKey,
        contentKey,
    });
    const extraPageCount = Math.min(
        range.loadMore,
        Math.max(0, Math.ceil(metadata.count / DEFAULT_PAGE_SIZE) - range.page),
    );
    const pagesKey = `${metadata.selectionKey}:${contentKey}:${range.page}`;
    const canLoadMore =
        visible && metadata.readyForDetails && !result.fetching && !result.stale && !result.error && hasProducts;
    const listUuid = metadata.input.uuid;
    const selectionKey = metadata.selectionKey;
    const page = range.page;
    useRefetchOnTabFocus(() => {
        if (extraPageCount > 0) setRefreshVersion((version) => version + 1);
    }, true);

    useEffect(() => {
        if (!canLoadMore || extraPageCount === 0) return;
        let active = true;
        const previous = latestAdditionalPages.current?.key === pagesKey ? latestAdditionalPages.current : undefined;
        const cachedPages = previous?.refreshVersion === refreshVersion ? previous.pages : [];
        const missingPageCount = Math.max(0, extraPageCount - cachedPages.length);
        if (missingPageCount === 0) {
            setAdditionalPages({ key: pagesKey, pages: cachedPages, refreshVersion, fetching: false, error: false });
            return;
        }
        setAdditionalPages({
            key: pagesKey,
            pages: previous?.pages ?? [],
            refreshVersion: previous?.refreshVersion ?? refreshVersion,
            fetching: true,
            error: false,
        });

        // Reuse confirmed pages on load-more; a content change or focus refresh validates the whole range.
        Promise.all(
            Array.from({ length: missingPageCount }, (_, index) =>
                client
                    .query<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>(
                        WishlistPageQueryDocument,
                        {
                            input: { type: TypeProductListTypeEnum.Wishlist, uuid: listUuid },
                            endCursor: getEndCursor(page + cachedPages.length + index + 1),
                            pageSize: DEFAULT_PAGE_SIZE,
                        },
                        {
                            requestPolicy: 'cache-and-network',
                            productListSelection: selectionKey,
                            productListContent: contentKey,
                        },
                    )
                    .toPromise(),
            ),
        ).then((responses) => {
            if (!active) return;
            if (responses.some((response) => response.error || !response.data?.productList)) {
                setAdditionalPages({
                    key: pagesKey,
                    pages: previous?.pages ?? [],
                    refreshVersion: previous?.refreshVersion ?? refreshVersion,
                    fetching: false,
                    error: true,
                });
                return;
            }
            setAdditionalPages({
                key: pagesKey,
                pages: [
                    ...cachedPages,
                    ...responses.map(
                        (response) => mapConnectionEdges(response.data?.productList?.productsConnection.edges) ?? [],
                    ),
                ],
                refreshVersion,
                fetching: false,
                error: false,
            });
        });
        return () => {
            active = false;
        };
    }, [
        canLoadMore,
        extraPageCount,
        pagesKey,
        client,
        listUuid,
        selectionKey,
        contentKey,
        page,
        refreshVersion,
        latestAdditionalPages,
    ]);

    const currentAdditionalPages = additionalPages?.key === pagesKey ? additionalPages : undefined;
    const firstPage = result.data?.productList;
    const wishlist = firstPage
        ? {
              __typename: firstPage.__typename,
              uuid: firstPage.uuid,
              products: [
                  ...(mapConnectionEdges(firstPage.productsConnection.edges) ?? []),
                  ...(currentAdditionalPages?.pages.slice(0, extraPageCount).flat() ?? []),
              ],
          }
        : firstPage;
    return {
        wishlist: hasProducts ? wishlist : metadata.summary === null ? null : undefined,
        totalCount: metadata.count,
        page: range.page,
        loadMore: range.loadMore,
        isProductListFetching:
            metadata.fetching ||
            (hasProducts &&
                (result.fetching ||
                    (extraPageCount > 0 &&
                        !result.error &&
                        !metadata.error &&
                        (!currentAdditionalPages || currentAdditionalPages.fetching)))),
        hasProductListError:
            metadata.error || !!result.error || (extraPageCount > 0 && !!currentAdditionalPages?.error),
    };
};
