import { FilterOptionsStateType } from 'types/productFilter';
import { initialState } from 'redux/slices/optionsFilter';

export const getFilterOptions = (filterQuery: string | undefined): FilterOptionsStateType => {
    return typeof filterQuery !== 'undefined' ? JSON.parse(filterQuery) : initialState;
};
