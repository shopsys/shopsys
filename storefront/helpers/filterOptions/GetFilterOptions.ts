import { initialState } from 'redux/slices/optionsFilter';
import { FilterOptionsStateType } from 'types/productFilter';

export const getFilterOptions = (filterQuery: string | undefined): FilterOptionsStateType => {
    return typeof filterQuery !== 'undefined' ? JSON.parse(filterQuery) : initialState;
};
