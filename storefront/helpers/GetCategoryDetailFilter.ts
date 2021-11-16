import { initialState } from 'redux/slices/user';
import { ParametersFilterStateType } from 'components/Blocks/Product/Filter/types';

export const getCategoryDetailFilter = (filterQuery: string | string[] | undefined): ParametersFilterStateType => {
    return typeof filterQuery !== 'undefined' ? JSON.parse(filterQuery as string) : initialState.parametersFilter;
};
