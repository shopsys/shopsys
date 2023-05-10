import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getQueryWithoutAllParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { shallowReplaceIfDifferent } from 'helpers/filterOptions/shallowReplaceIfDifferent';
import { SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { useEffect } from 'react';

export const useRemoveSortFromUrlIfDefault = (
    orderingMode: ProductOrderingModeEnumApi | null,
    defaultOrderingMode: ProductOrderingModeEnumApi | null,
): void => {
    const router = useRouter();

    useEffect(() => {
        const routerQueryWithoutAllParameter = getQueryWithoutAllParameter(router.query);
        const pathname = router.asPath.split('?')[0];

        if (orderingMode === defaultOrderingMode) {
            delete routerQueryWithoutAllParameter[SORT_QUERY_PARAMETER_NAME];
        }

        shallowReplaceIfDifferent(router, { pathname, query: routerQueryWithoutAllParameter });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [orderingMode, defaultOrderingMode]);
};
