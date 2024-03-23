import { ListedProductConnectionPreviewFragment } from 'graphql/requests/products/fragments/ListedProductConnectionPreviewFragment.generated';
import { getDefaultFilterFromFilterOptions } from 'helpers/filterOptions/seoCategories';
import { useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const useHandleDefaultFiltersUpdate = (productsPreview: ListedProductConnectionPreviewFragment | undefined) => {
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
