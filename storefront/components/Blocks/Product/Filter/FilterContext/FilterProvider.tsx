import { getDefaultFormValues } from '../formMeta';
import { FilterContext } from './context';
import { filterReducer } from './reducer';
import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { FILTER_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { useEffect, useMemo, useReducer } from 'react';

type FilterProviderProps = {
    originalSlug: string | null;
    productFilterOptions: ProductFilterOptionsFragmentApi;
};

export const FilterProvider: FC<FilterProviderProps> = ({ children, originalSlug, productFilterOptions }) => {
    const { query } = useRouter();

    const value = useReducer(filterReducer, {
        options: productFilterOptions,
        selected: getDefaultFormValues(
            mapParametersFilter(getFilterOptions(parseFilterOptionsFromQuery(query[FILTER_QUERY_PARAMETER_NAME]))),
            productFilterOptions,
            originalSlug,
        ),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
    const deepComparedFilterOptions = useMemo(() => productFilterOptions, [JSON.stringify([productFilterOptions])]);
    const [, dispatch] = value;
    useEffect(() => {
        dispatch({
            type: 'setFilterOptions',
            payload: deepComparedFilterOptions,
        });
    }, [deepComparedFilterOptions, dispatch]);

    return <FilterContext.Provider value={value}>{children}</FilterContext.Provider>;
};
