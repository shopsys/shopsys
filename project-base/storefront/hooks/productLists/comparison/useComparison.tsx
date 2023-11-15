import {
    ProductListTypeEnumApi,
    useAddProductToComparisonMutationApi,
    useCleanProductListMutationApi,
    useComparisonQueryApi,
    useRemoveProductFromComparisonMutationApi,
} from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useProductList } from 'hooks/productLists/useProductList';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useComparison = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();

    const [, addProductToListMutation] = useAddProductToComparisonMutationApi();
    const [, removeProductFromListMutation] = useRemoveProductFromComparisonMutationApi();
    const [, cleanListMutation] = useCleanProductListMutationApi();

    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);
    const updateComparisonUuid = usePersistStore((store) => store.updateComparisonUuid);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);

    const [{ data: comparisonData, fetching }] = useComparisonQueryApi({
        variables: { input: { uuid: comparisonUuid, type: ProductListTypeEnumApi.ComparisonApi } },
        pause: !comparisonUuid && !isUserLoggedIn,
    });

    useEffect(() => {
        if (comparisonData?.productList?.uuid) {
            updateComparisonUuid(comparisonData.productList.uuid);
        }
    }, [comparisonData?.productList?.uuid]);

    const { cleanList, isProductInList, toggleProductInList } = useProductList(
        ProductListTypeEnumApi.ComparisonApi,
        comparisonUuid,
        comparisonData,
        {
            cleanList: cleanListMutation,
            removeProductFromList: removeProductFromListMutation,
            addProductToList: addProductToListMutation,
        },
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
        comparison: comparisonData?.productList,
        fetching,
        isProductInComparison: isProductInList,
        toggleProductInComparison: toggleProductInList,
        cleanComparison: cleanList,
        isPopupCompareOpen,
        setIsPopupCompareOpen,
    };
};
