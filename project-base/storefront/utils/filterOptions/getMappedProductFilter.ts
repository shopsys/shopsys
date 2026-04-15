import { TypeProductFilter } from 'graphql/types';
import { mapParametersFilter } from './mapParametersFilter';

export const getMappedProductFilter = (filterUrlQuery: string | string[] | undefined): TypeProductFilter | null => {
    if (Array.isArray(filterUrlQuery) || !filterUrlQuery) {
        return null;
    }

    return mapParametersFilter(JSON.parse(filterUrlQuery));
};
