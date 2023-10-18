import { mapParametersFilter } from './mapParametersFilter';
import { ProductFilterApi } from 'graphql/generated';

export const getMappedProductFilter = (filterUrlQuery: string | string[] | undefined): ProductFilterApi | null => {
    if (Array.isArray(filterUrlQuery) || !filterUrlQuery) {
        return null;
    }

    return mapParametersFilter(JSON.parse(filterUrlQuery));
};
