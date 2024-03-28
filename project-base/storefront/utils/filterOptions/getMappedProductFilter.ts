import { mapParametersFilter } from './mapParametersFilter';
import { TypeProductFilter } from 'graphql/types';

export const getMappedProductFilter = (filterUrlQuery: string | string[] | undefined): TypeProductFilter | null => {
    if (Array.isArray(filterUrlQuery) || !filterUrlQuery) {
        return null;
    }

    return mapParametersFilter(JSON.parse(filterUrlQuery));
};
