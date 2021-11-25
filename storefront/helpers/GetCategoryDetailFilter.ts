import { initialState } from 'redux/slices/optionsFilter';
import { OptionsFilterStateType } from 'components/Blocks/Product/Filter/types';

export const getCategoryDetailFilter = (filterQuery: string | string[] | undefined): OptionsFilterStateType => {
    return typeof filterQuery !== 'undefined' ? JSON.parse(getFilterQueryStandardizedValue(filterQuery)) : initialState;
};

const getFilterQueryStandardizedValue = (filterQuery: string | string[]) => {
    return Array.isArray(filterQuery) ? filterQuery.join() : filterQuery;
};
