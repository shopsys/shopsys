import { ProductListTypeEnum } from 'graphql/types';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useProductList } from 'hooks/productLists/useProductList';
import { useUpdateProductListUuid } from 'hooks/productLists/useUpdateProductListUuid';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(ProductListTypeEnum.Comparison);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);

    const { productListData, removeList, isProductInList, toggleProductInList, fetching } = useProductList(
        ProductListTypeEnum.Comparison,

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
