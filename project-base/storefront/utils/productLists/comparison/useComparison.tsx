import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { ProductInterfaceType } from 'types/product';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useProductList } from 'utils/productLists/useProductList';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

const ProductComparePopup = dynamic(() =>
    import('components/Blocks/Popup/ProductComparePopup').then((component) => component.ProductComparePopup),
);

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Comparison);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);
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
            },
            addProductSuccess: (result, productUuid) => {
                updatePortalContent(<ProductComparePopup />);
                updateComparisonUuid(result?.uuid ?? null);
                pushAddProductListGtmEvent(productUuid);
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
                showSuccessMessage(t('Product has been removed from your comparison.'));
                pushRemoveProductListGtmEvent(productUuid);
            },
        },
    );

    const toggleProductInComparison = (
        product: ProductInterfaceType,
        gtmProductListName: GtmProductListNameType,
        listIndex?: number,
    ) => {
        storeCurrentFocus();
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
