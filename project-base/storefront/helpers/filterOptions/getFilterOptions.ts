import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const getFilterOptions = (filterQuery: string | undefined): FilterOptionsUrlQueryType | null => {
    return typeof filterQuery !== 'undefined' ? JSON.parse(filterQuery) : null;
};
