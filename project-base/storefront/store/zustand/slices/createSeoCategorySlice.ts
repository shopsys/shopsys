import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getEmptyDefaultProductFiltersMap } from 'helpers/filterOptions/seoCategories';
import { StateCreator } from 'zustand';

export type DefaultProductFiltersMapType = {
    flags: Set<string>;
    sort: ProductOrderingModeEnumApi;
    parameters: Map<string, Set<string>>;
};

export type SeoCategorySlice = {
    defaultProductFiltersMap: DefaultProductFiltersMapType;
    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => void;
};

export const createSeoCategorySlice: StateCreator<SeoCategorySlice> = (set) => ({
    defaultProductFiltersMap: getEmptyDefaultProductFiltersMap(),

    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => {
        set({ defaultProductFiltersMap: value });
    },
});
