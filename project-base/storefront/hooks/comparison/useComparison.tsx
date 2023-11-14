import {
    ProductListTypeEnumApi,
    useAddProductToComparisonMutationApi,
    useCleanProductListMutationApi,
    useComparisonQueryApi,
    useRemoveProductFromComparisonMutationApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useComparison = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [, addProductToList] = useAddProductToComparisonMutationApi();
    const [, removeProductFromList] = useRemoveProductFromComparisonMutationApi();
    const [, cleanList] = useCleanProductListMutationApi();
    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);
    const updateComparisonUuid = usePersistStore((store) => store.updateComparisonUuid);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);

    const [{ data: comparisonData, fetching }] = useComparisonQueryApi({
        variables: { input: { uuid: comparisonUuid, type: ProductListTypeEnumApi.ComparisonApi } },
        pause: !comparisonUuid && !isUserLoggedIn,
    });

    const isProductInComparison = (productUuid: string) =>
        !!comparisonData?.productList?.products.find((product) => product.uuid === productUuid);

    const handleAddToComparison = async (productUuid: string) => {
        const addProductToComparisonResult = await addProductToList({
            input: {
                productUuid,
                productListInput: {
                    uuid: comparisonUuid,
                    type: ProductListTypeEnumApi.ComparisonApi,
                },
            },
        });

        if (addProductToComparisonResult.error) {
            const { applicationError } = getUserFriendlyErrors(addProductToComparisonResult.error, t);

            showErrorMessage(applicationError?.message ?? t('Unable to add product to comparison.'));
        } else {
            setIsPopupCompareOpen(true);
            updateComparisonUuid(addProductToComparisonResult.data?.AddProductToList.uuid ?? null);
        }
    };

    const handleRemoveFromComparison = async (productUuid: string) => {
        const removeProductFromComparisonResult = await removeProductFromList({
            input: {
                productUuid,
                productListInput: {
                    uuid: comparisonUuid,
                    type: ProductListTypeEnumApi.ComparisonApi,
                },
            },
        });

        if (removeProductFromComparisonResult.error) {
            const { applicationError } = getUserFriendlyErrors(removeProductFromComparisonResult.error, t);

            showErrorMessage(applicationError?.message || t('Unable to remove product from comparison.'));
        } else {
            if (!removeProductFromComparisonResult.data?.RemoveProductFromList) {
                updateComparisonUuid(null);
            }

            showSuccessMessage(t('Product has been removed from your comparison.'));
        }
    };

    const toggleProductInComparison = async (productUuid: string) => {
        if (isProductInComparison(productUuid)) {
            handleRemoveFromComparison(productUuid);
        } else {
            handleAddToComparison(productUuid);
        }
    };

    const handleCleanComparison = async () => {
        const cleanComparisonResult = await cleanList({
            input: {
                uuid: comparisonUuid,
                type: ProductListTypeEnumApi.ComparisonApi,
            },
        });

        if (cleanComparisonResult.error) {
            const { applicationError } = getUserFriendlyErrors(cleanComparisonResult.error, t);
            if (applicationError?.message) {
                showErrorMessage(applicationError.message);
            } else {
                showErrorMessage(t('Unable to clean product comparison.'));
            }
        } else {
            updateComparisonUuid(null);
            showSuccessMessage(t('Comparison products have been cleaned.'));
        }
    };

    return {
        comparison: comparisonData?.productList,
        fetching,
        isPopupCompareOpen,
        isProductInComparison,
        toggleProductInComparison,
        handleCleanComparison,
        setIsPopupCompareOpen,
    };
};
