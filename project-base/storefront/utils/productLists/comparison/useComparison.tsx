import { useProductLists } from 'components/providers/ProductListsProvider';
import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
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
    const metadata = lists.COMPARISON;
    const hasProducts = !!metadata.summary && metadata.count > 0;
    const result = useProductListQueryResult<TypeProductListQuery, TypeProductListQueryVariables>({
        query: ProductListQueryDocument,
        variables: { input: metadata.input },
        pause: !visible || !metadata.readyForDetails || !hasProducts,
        selectionKey: metadata.selectionKey,
        contentKey: metadata.summary?.productUuids.join(','),
    });
    const list = result.data?.productList;
    const productsByUuid = new Map(list?.products.map((product) => [product.uuid, product]));
    const products =
        metadata.summary?.productUuids.flatMap((uuid) => {
            const product = productsByUuid.get(uuid);
            return product ? [product] : [];
        }) ?? [];

    return {
        comparison: metadata.summary === null ? null : list ? { ...list, products } : undefined,
        isProductListFetching: metadata.fetching || (hasProducts && result.fetching),
        hasProductListError: metadata.error || !!result.error,
    };
};
