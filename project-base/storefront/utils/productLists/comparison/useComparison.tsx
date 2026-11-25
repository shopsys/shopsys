import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { useProductLists } from 'components/providers/ProductListsProvider';
import { COMPARISON_PAGE_SIZE } from 'config/constants';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useRef, useState } from 'react';
import { ProductInterfaceType } from 'types/product';
import { useClient } from 'urql';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { useProductListQueryResult } from 'utils/productLists/useProductListQueryResult';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ComparisonOptions = {
    onProductRemoved?: (productUuid: string) => void;
    onProductRemoveError?: (productUuid: string) => void;
    showAddSuccessMessage?: boolean;
};

export const useComparison = ({
    onProductRemoved,
    onProductRemoveError,
    showAddSuccessMessage = true,
}: ComparisonOptions = {}) => {
    const { t } = useTranslation();
    const {
        clearProductListGtmContext,
        pushAddProductListGtmEvent,
        pushRemoveProductListGtmEvent,
        toggleProductInListWithGtm,
    } = useProductListGtmEvent(GtmEventType.add_to_comparison, GtmEventType.remove_from_comparison);

    const { productListSummary, removeList, isProductInList, toggleProductInList, moveProduct } = useProductList(
        TypeProductListTypeEnum.Comparison,
        {
            addProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to add product to comparison.'));
            },
            addProductSuccess: (productUuid) => {
                if (showAddSuccessMessage) {
                    showSuccessMessage(t('Product added to comparison.'));
                }
                pushAddProductListGtmEvent(productUuid);
            },
            removeError: () => showErrorMessage(t('Unable to clean product comparison.')),
            removeSuccess: () => {
                showSuccessMessage(t('Comparison products have been cleaned.'));
            },
            removeProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to remove product from comparison.'));
                onProductRemoveError?.(productUuid);
            },
            removeProductSuccess: (productUuid) => {
                if (!onProductRemoved) {
                    showSuccessMessage(t('Product has been removed from your comparison.'));
                }
                pushRemoveProductListGtmEvent(productUuid);
                onProductRemoved?.(productUuid);
            },
        },
    );

    const toggleProductInComparison = (
        product: ProductInterfaceType,
        gtmProductListName: GtmProductListNameType,
        listIndex?: number,
    ) => {
        return toggleProductInListWithGtm(product, gtmProductListName, listIndex, toggleProductInList);
    };

    return {
        comparisonSummary: productListSummary,
        isProductInComparison: isProductInList,
        toggleProductInComparison,
        removeComparison: removeList,
        moveProductInComparison: moveProduct,
    };
};

export const useComparisonDetails = () => {
    const { lists, visible } = useProductLists();
    const client = useClient();
    const metadata = lists.COMPARISON;
    const hasProducts = !!metadata.summary && metadata.count > 0;
    const contentKey = metadata.summary?.productUuids.join(',') ?? '';
    const pause = !visible || !metadata.readyForDetails || !hasProducts;
    const result = useProductListQueryResult<TypeProductListQuery, TypeProductListQueryVariables>({
        query: ProductListQueryDocument,
        variables: { input: metadata.input, endCursor: '', pageSize: COMPARISON_PAGE_SIZE },
        pause,
        selectionKey: metadata.selectionKey,
        contentKey,
    });
    const [additionalPages, setAdditionalPages] = useState<{
        key: string;
        products: TypeProductInProductListFragment[];
        error: boolean;
    }>();
    const additionalPageCount = Math.max(0, Math.ceil(metadata.count / COMPARISON_PAGE_SIZE) - 1);
    const pagesKey = `${metadata.selectionKey}:${contentKey}`;
    const loaded = useRef<{
        selectionKey: string;
        list?: NonNullable<TypeProductListQuery['productList']>;
        products: Map<string, TypeProductInProductListFragment>;
    }>(undefined);
    const listUuid = metadata.input.uuid;
    const selectionKey = metadata.selectionKey;

    // The whole comparison is displayed at once, so all remaining pages are loaded in parallel
    useEffect(() => {
        if (pause || additionalPageCount === 0) return;
        let active = true;
        const fetchComparisonPage = (page: number) =>
            client
                .query<TypeProductListQuery, TypeProductListQueryVariables>(
                    ProductListQueryDocument,
                    {
                        input: { type: TypeProductListTypeEnum.Comparison, uuid: listUuid },
                        endCursor: getEndCursor(page, 0, COMPARISON_PAGE_SIZE),
                        pageSize: COMPARISON_PAGE_SIZE,
                    },
                    {
                        requestPolicy: 'cache-and-network',
                        productListSelection: selectionKey,
                        productListContent: contentKey,
                    },
                )
                .toPromise();
        // The first page is loaded by the query above
        const pages = Array.from({ length: additionalPageCount }, (_, index) => fetchComparisonPage(index + 2));
        Promise.all(pages).then((responses) => {
            if (!active) return;
            setAdditionalPages({
                key: pagesKey,
                products: responses.flatMap(
                    (response) => mapConnectionEdges(response.data?.productList?.products.edges) ?? [],
                ),
                error: responses.some((response) => response.error || !response.data?.productList),
            });
        });
        return () => {
            active = false;
        };
    }, [pause, additionalPageCount, pagesKey, client, listUuid, selectionKey, contentKey]);

    const currentAdditionalPages = additionalPages?.key === pagesKey ? additionalPages : undefined;
    const list = result.data?.productList;
    // Removing or moving a product changes the pages, so keep every loaded card of the selected list until fresh
    // ones arrive, otherwise the comparison would briefly lose products or flash as empty
    if (loaded.current?.selectionKey !== selectionKey) {
        loaded.current = { selectionKey, products: new Map() };
    }
    const loadedComparison = loaded.current;
    const loadedProducts = loadedComparison.products;
    for (const product of [...(additionalPages?.products ?? []), ...(mapConnectionEdges(list?.products.edges) ?? [])]) {
        loadedProducts.set(product.uuid, product);
    }
    // Display the comparison only after all of its pages were loaded for the first time
    if (list && (additionalPageCount === 0 || additionalPages)) loadedComparison.list = list;
    const loadedList = loadedComparison.list;
    const products =
        metadata.summary?.productUuids.flatMap((uuid) => {
            const product = loadedProducts.get(uuid);
            return product ? [product] : [];
        }) ?? [];
    const isAdditionalPagesFetching = additionalPageCount > 0 && !pause && !currentAdditionalPages;

    return {
        comparison: metadata.summary === null ? null : loadedList ? { ...loadedList, products } : undefined,
        isProductListFetching: metadata.fetching || (hasProducts && (result.fetching || isAdditionalPagesFetching)),
        hasProductListError:
            metadata.error || !!result.error || (additionalPageCount > 0 && !!currentAdditionalPages?.error),
    };
};
