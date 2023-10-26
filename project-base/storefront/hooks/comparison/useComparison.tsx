import {
    useAddProductToComparisonMutationApi,
    useCleanComparisonMutationApi,
    useComparisonQueryApi,
    useRemoveProductFromComparisonMutationApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useComparison = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [, addProductToComparison] = useAddProductToComparisonMutationApi();
    const [, removeProductFromComparison] = useRemoveProductFromComparisonMutationApi();
    const [, cleanComparison] = useCleanComparisonMutationApi();
    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);
    const updateComparisonUuid = usePersistStore((store) => store.updateComparisonUuid);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);
    const [isFetchingPaused, setIsFetchingPaused] = useState(true);

    const [{ data: comparisonData, fetching }] = useComparisonQueryApi({
        variables: { comparisonUuid },
        pause: isFetchingPaused,
    });

    useEffect(() => {
        setIsFetchingPaused(!comparisonUuid && !isUserLoggedIn);
    }, [comparisonUuid]);

    useEffect(() => {
        if (!isUserLoggedIn && comparisonData?.comparison && comparisonUuid !== comparisonData.comparison.uuid) {
            updateComparisonUuid(comparisonData.comparison.uuid);
        }
    }, [comparisonData?.comparison?.uuid]);

    const isProductInComparison = (productUuid: string) =>
        !!comparisonData?.comparison?.products.find((product) => product.uuid === productUuid);

    const handleAddToComparison = async (productUuid: string) => {
        const addProductToComparisonResult = await addProductToComparison({
            productUuid,
            comparisonUuid,
        });

        if (addProductToComparisonResult.error) {
            const { applicationError } = getUserFriendlyErrors(addProductToComparisonResult.error, t);

            showErrorMessage(applicationError?.message ?? t('Unable to add product to comparison.'));
        } else {
            setIsPopupCompareOpen(true);

            updateComparisonUuid(addProductToComparisonResult.data?.addProductToComparison.uuid ?? null);
        }
    };

    const handleRemoveFromComparison = async (productUuid: string) => {
        const removeProductFromComparisonResult = await removeProductFromComparison({
            productUuid,
            comparisonUuid,
        });

        if (removeProductFromComparisonResult.error) {
            const { applicationError } = getUserFriendlyErrors(removeProductFromComparisonResult.error, t);
            if (applicationError?.message) {
                showErrorMessage(applicationError.message);
            } else {
                showErrorMessage(t('Unable to remove product from comparison.'));
            }
        } else {
            if (!removeProductFromComparisonResult.data?.removeProductFromComparison) {
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
        const cleanComparisonResult = await cleanComparison({ comparisonUuid });

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
        comparison: comparisonData?.comparison,
        fetching,
        isPopupCompareOpen,
        isProductInComparison,
        toggleProductInComparison,
        handleCleanComparison,
        setIsPopupCompareOpen,
    };
};
