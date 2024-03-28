import { DEFAULT_SORT } from 'config/constants';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';

export const getEmptyDefaultProductFiltersMap = (): DefaultProductFiltersMapType => ({
    flags: new Set(),
    brands: new Set(),
    sort: DEFAULT_SORT,
    parameters: new Map(),
});
