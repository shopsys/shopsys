import { mapParametersFilter } from './mapParametersFilter';
import { ProductFilter } from 'graphql/types';

export const getMappedProductFilter = (filterUrlQuery: string | string[] | undefined): ProductFilter | null => {
    if (Array.isArray(filterUrlQuery) || !filterUrlQuery) {
        return null;
    }

    return mapParametersFilter(JSON.parse(filterUrlQuery));
};
