import { TypeProductListTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useProductList } from 'utils/productLists/useProductList';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Comparison);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);

    const { productListData, removeList, isProductInList, toggleProductInList, fetching } = useProductList(
        TypeProductListTypeEnum.Comparison,
        {
            addProductError: () => showErrorMessage(t('Unable to add product to comparison.')),
            addProductSuccess: (result) => {
                setIsPopupCompareOpen(true);
                updateComparisonUuid(result?.uuid ?? null);
            },
            removeError: () => showErrorMessage(t('Unable to clean product comparison.')),
            removeSuccess: () => {
                showSuccessMessage(t('Comparison products have been cleaned.'));
                updateComparisonUuid(null);
            },
            removeProductError: () => showErrorMessage(t('Unable to remove product from comparison.')),
            removeProductSuccess: (result) => {
                if (!result) {
                    updateComparisonUuid(null);
                }

                showSuccessMessage(t('Product has been removed from your comparison.'));
            },
        },
    );

    return {
        comparison: productListData?.productList,
        fetching,
        isProductInComparison: isProductInList,
        toggleProductInComparison: toggleProductInList,
        removeComparison: removeList,
        isPopupCompareOpen,
        setIsPopupCompareOpen,
    };
};
