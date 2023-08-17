import { ProductFilterApi } from 'graphql/requests/types';
import { mapParametersFilter } from './mapParametersFilter';

export const getMappedProductFilter = (filterUrlQuery: string | string[] | undefined): ProductFilterApi | null => {
    if (Array.isArray(filterUrlQuery) || !filterUrlQuery) {
        return null;
    }

    return mapParametersFilter(JSON.parse(filterUrlQuery));
};
