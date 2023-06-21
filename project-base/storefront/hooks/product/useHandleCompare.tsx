import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import {
    ComparedProductFragmentApi,
    useAddProductToComparisonMutationApi,
    useCleanComparisonMutationApi,
    useComparisonQueryApi,
    useRemoveProductFromComparisonMutationApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { Dispatch, SetStateAction, useEffect, useMemo, useState } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useHandleCompare = (
    productUuid: string,
): {
    isProductInComparison: boolean;
    handleProductInComparison: () => void;
    handleRemoveAllFromComparison: () => void;
    isPopupCompareOpen: boolean;
    setIsPopupCompareOpen: Dispatch<SetStateAction<boolean>>;
    getComparisonProducts: () => ComparedProductFragmentApi[];
} => {
    const t = useTypedTranslationFunction();
    const [isPopupCompareOpen, setIsPopupCompareOpen] = useState(false);
    const { isUserLoggedIn } = useCurrentUserData();
    const [isProductInComparison, setIsProductInComparison] = useState(false);
    const [, addProductToComparison] = useAddProductToComparisonMutationApi();
    const [, removeProductFromComparison] = useRemoveProductFromComparisonMutationApi();
    const [, cleanComparison] = useCleanComparisonMutationApi();
    const productsComparisonUuid = usePersistStore((s) => s.productsComparisonUuid);
    const updateUserState = usePersistStore((s) => s.updateUserState);
    const [result] = useComparisonQueryApi({
        variables: { comparisonUuid: productsComparisonUuid },
        requestPolicy: 'network-only',
    });

    const comparedProducts = useMemo(() => {
        return result.data?.comparison?.products ?? [];
    }, [result.data?.comparison?.products]);

    useEffect(() => {
        if (comparedProducts.find((product) => product.uuid === productUuid) !== undefined) {
            setIsProductInComparison(true);
        } else {
            setIsProductInComparison(false);
        }
    }, [productUuid, comparedProducts]);

    const handleAddToComparison = async () => {
        const addProductToComparisonResult = await addProductToComparison({
            productUuid,
            comparisonUuid: productsComparisonUuid,
        });

        if (addProductToComparisonResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(addProductToComparisonResult.error, t);
            if (applicationError?.message !== undefined) {
                showErrorMessage(applicationError.message);
            } else {
                showErrorMessage(t('Unable to add product to comparison.'));
            }
        } else {
            setIsPopupCompareOpen(true);

            updateUserState({
                productsComparisonUuid: isUserLoggedIn
                    ? null
                    : addProductToComparisonResult.data?.addProductToComparison.uuid ?? null,
            });
        }
    };

    const handleRemoveFromComparison = async () => {
        const removeProductFromComparisonResult = await removeProductFromComparison({
            productUuid,
            comparisonUuid: productsComparisonUuid,
        });

        if (removeProductFromComparisonResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(removeProductFromComparisonResult.error, t);
            if (applicationError?.message !== undefined) {
                showErrorMessage(applicationError.message);
            } else {
                showErrorMessage(t('Unable to remove product from comparison.'));
            }
        } else {
            if (!removeProductFromComparisonResult.data?.removeProductFromComparison) {
                updateUserState({ productsComparisonUuid: null });
            }
            showSuccessMessage(t('Product has been removed from your comparison.'));
        }
    };

    const handleProductInComparison = async () => {
        if (isProductInComparison) {
            handleRemoveFromComparison();
        } else {
            handleAddToComparison();
        }
    };

    const handleRemoveAllFromComparison = async () => {
        const cleanComparisonResult = await cleanComparison({ comparisonUuid: productsComparisonUuid });

        if (cleanComparisonResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(cleanComparisonResult.error, t);
            if (applicationError?.message !== undefined) {
                showErrorMessage(applicationError.message);
            } else {
                showErrorMessage(t('Unable to clean product comparison.'));
            }
        } else {
            updateUserState({ productsComparisonUuid: null });
            showSuccessMessage(t('Comparison products have been cleaned.'));
        }
    };

    const getComparisonProducts = (): ComparedProductFragmentApi[] => {
        return comparedProducts;
    };

    return {
        isProductInComparison,
        handleProductInComparison,
        handleRemoveAllFromComparison,
        isPopupCompareOpen,
        setIsPopupCompareOpen,
        getComparisonProducts,
    };
};
