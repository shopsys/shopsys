import { SEO_SENSITIVE_FILTERS } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const getFilterWithoutSeoSensitiveFilters = (
    currentFilter: FilterOptionsUrlQueryType | undefined | null,
    currentSort: TypeProductOrderingModeEnum | null,
) => {
    const filteredSort = SEO_SENSITIVE_FILTERS.SORT || !currentSort ? undefined : currentSort;
    if (!currentFilter) {
        return { filteredFilter: undefined, filteredSort };
    }

    const filteredFilter: Partial<FilterOptionsUrlQueryType> = { ...currentFilter };
    if (SEO_SENSITIVE_FILTERS.AVAILABILITY) {
        delete filteredFilter.onlyInStock;
    }
    if (SEO_SENSITIVE_FILTERS.BRANDS) {
        delete filteredFilter.brands;
    }
    if (SEO_SENSITIVE_FILTERS.FLAGS) {
        delete filteredFilter.flags;
    }
    if (SEO_SENSITIVE_FILTERS.PARAMETERS.CHECKBOX) {
        filteredFilter.parameters = filteredFilter.parameters?.filter(
            (parameter) => typeof parameter.minimalValue === 'number' && typeof parameter.maximalValue === 'number',
        );
    }
    if (SEO_SENSITIVE_FILTERS.PARAMETERS.SLIDER) {
        filteredFilter.parameters = filteredFilter.parameters?.filter((parameter) => !!parameter.values?.length);
    }
    if (!filteredFilter.parameters?.length) {
        delete filteredFilter.parameters;
    }
    if (SEO_SENSITIVE_FILTERS.PRICE) {
        delete filteredFilter.minimalPrice;
    }
    if (SEO_SENSITIVE_FILTERS.PRICE) {
        delete filteredFilter.maximalPrice;
    }

    return { filteredFilter, filteredSort };
};
