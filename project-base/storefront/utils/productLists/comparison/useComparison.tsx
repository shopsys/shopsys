import { TypeProductListTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useProductList } from 'utils/productLists/useProductList';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

const ProductComparePopup = dynamic(() =>
    import('components/Blocks/Popup/ProductComparePopup').then((component) => component.ProductComparePopup),
);

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Comparison);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const { productListData, removeList, isProductInList, toggleProductInList, isProductListFetching } = useProductList(
        TypeProductListTypeEnum.Comparison,
        {
            addProductError: () => showErrorMessage(t('Unable to add product to comparison.')),
            addProductSuccess: (result) => {
                dispatchBroadcastChannel('refetchComparison');
                updatePortalContent(<ProductComparePopup />);
                updateComparisonUuid(result?.uuid ?? null);
            },
            removeError: () => showErrorMessage(t('Unable to clean product comparison.')),
            removeSuccess: () => {
                dispatchBroadcastChannel('refetchComparison');
                showSuccessMessage(t('Comparison products have been cleaned.'));
                updateComparisonUuid(null);
            },
            removeProductError: () => showErrorMessage(t('Unable to remove product from comparison.')),
            removeProductSuccess: (result) => {
                dispatchBroadcastChannel('refetchComparison');
                if (!result) {
                    updateComparisonUuid(null);
                }

                showSuccessMessage(t('Product has been removed from your comparison.'));
            },
        },
    );

    return {
        comparison: productListData?.productList,
        isProductListFetching,
        isProductInComparison: isProductInList,
        toggleProductInComparison: toggleProductInList,
        removeComparison: removeList,
    };
};
