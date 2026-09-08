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

type ComparisonCallbacks = {
    onProductRemoved?: (productUuid: string) => void;
    onProductAdded?: (productUuid: string) => void;
    onAddProductError?: (productUuid: string) => void;
};

export const useComparison = ({ onProductRemoved, onProductAdded, onAddProductError }: ComparisonCallbacks = {}) => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Comparison);
    const {
        clearProductListGtmContext,
        pushAddProductListGtmEvent,
        pushRemoveProductListGtmEvent,
        toggleProductInListWithGtm,
    } = useProductListGtmEvent(GtmEventType.add_to_comparison, GtmEventType.remove_from_comparison);

    const { productListData, removeList, isProductInList, toggleProductInList, isProductListFetching } = useProductList(
        TypeProductListTypeEnum.Comparison,
        {
            addProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to add product to comparison.'));
                onAddProductError?.(productUuid);
            },
            addProductSuccess: (result, productUuid) => {
                if (!onProductAdded) {
                    showSuccessMessage(t('Product added to comparison.'));
                }
                updateComparisonUuid(result?.uuid ?? null);
                pushAddProductListGtmEvent(productUuid);
                onProductAdded?.(productUuid);
            },
            removeError: () => showErrorMessage(t('Unable to clean product comparison.')),
            removeSuccess: () => {
                showSuccessMessage(t('Comparison products have been cleaned.'));
                updateComparisonUuid(null);
            },
            removeProductError: (productUuid) => {
                clearProductListGtmContext(productUuid);
                showErrorMessage(t('Unable to remove product from comparison.'));
            },
            removeProductSuccess: (result, productUuid) => {
                if (!result) {
                    updateComparisonUuid(null);
                }
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
        toggleProductInListWithGtm(product, gtmProductListName, listIndex, toggleProductInList);
    };

    return {
        comparison: productListData?.productList,
        isProductListFetching,
        isProductInComparison: isProductInList,
        toggleProductInComparison,
        removeComparison: removeList,
    };
};
