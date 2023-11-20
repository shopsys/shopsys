import { ListedProductConnectionPreviewFragmentApi } from 'graphql/generated';
import { getDefaultFilterFromFilterOptions } from 'helpers/filterOptions/seoCategories';
import { useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const useHandleDefaultFiltersUpdate = (
    productsPreview: ListedProductConnectionPreviewFragmentApi | undefined,
) => {
    const setDefaultProductFiltersMap = useSessionStore((s) => s.setDefaultProductFiltersMap);

    useEffect(() => {
        setDefaultProductFiltersMap(
            getDefaultFilterFromFilterOptions(
                productsPreview?.productFilterOptions,
                productsPreview?.defaultOrderingMode,
            ),
        );
    }, [productsPreview?.productFilterOptions, productsPreview?.defaultOrderingMode]);
};
