import { ProductListTypeEnumApi } from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useProductList } from 'hooks/productLists/useProductList';
import { useUpdateProductListUuid } from 'hooks/productLists/useUpdateProductListUuid';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(ProductListTypeEnumApi.ComparisonApi);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);

    const { productListData, cleanList, isProductInList, toggleProductInList, fetching } = useProductList(
        ProductListTypeEnumApi.ComparisonApi,

        {
            addError: () => showErrorMessage(t('Unable to add product to comparison.')),
            addSuccess: (result) => {
                setIsPopupCompareOpen(true);
                updateComparisonUuid(result?.uuid ?? null);
            },
            cleanError: () => showErrorMessage(t('Unable to clean product comparison.')),
            cleanSuccess: () => {
                showSuccessMessage(t('Comparison products have been cleaned.'));
                updateComparisonUuid(null);
            },
            removeError: () => showErrorMessage(t('Unable to remove product from comparison.')),
            removeSuccess: (result) => {
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
        cleanComparison: cleanList,
        isPopupCompareOpen,
        setIsPopupCompareOpen,
    };
};
