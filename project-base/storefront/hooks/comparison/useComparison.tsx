import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import {
    useAddProductToComparisonMutationApi,
    useCleanComparisonMutationApi,
    useComparisonQueryApi,
    useRemoveProductFromComparisonMutationApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect, useRef, useState } from 'react';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useComparison = () => {
    const t = useTypedTranslationFunction();
    const isUserLoggedIn = !!useCurrentCustomerData();
    const [, addProductToComparison] = useAddProductToComparisonMutationApi();
    const [, removeProductFromComparison] = useRemoveProductFromComparisonMutationApi();
    const [, cleanComparison] = useCleanComparisonMutationApi();
    const comparisonUuid = usePersistStore((store) => store.comparisonUuid);
    const updateUserState = usePersistStore((store) => store.updateUserState);
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);
    const initialLoadRef = useRef(false);
    const [isLoadingVisible, setIsLoadingVisible] = useState(true);

    const [{ data: comparisonData, fetching }] = useComparisonQueryApi({
        variables: { comparisonUuid },
        pause: !comparisonUuid && !isUserLoggedIn,
    });

    useEffect(() => {
        if (initialLoadRef.current) {
            initialLoadRef.current = true;
        } else {
            setIsLoadingVisible(fetching);
        }
    }, [fetching]);

    useEffect(() => {
        updateUserState({
            comparisonUuid: comparisonData?.comparison?.uuid ?? null,
        });
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

            updateUserState({
                comparisonUuid: addProductToComparisonResult.data?.addProductToComparison.uuid ?? null,
            });
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
                updateUserState({ comparisonUuid: null });
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
            updateUserState({ comparisonUuid: null });
            showSuccessMessage(t('Comparison products have been cleaned.'));
        }
    };

    return {
        comparison: comparisonData?.comparison,
        fetching: isLoadingVisible,
        isPopupCompareOpen,
        isProductInComparison,
        toggleProductInComparison,
        handleCleanComparison,
        setIsPopupCompareOpen,
    };
};
