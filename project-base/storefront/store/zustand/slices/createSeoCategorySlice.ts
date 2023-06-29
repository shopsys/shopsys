import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getEmptyDefaultProductFiltersMap } from 'helpers/filterOptions/seoCategories';
import { StateCreator } from 'zustand';

export type DefaultProductFiltersMapType = {
    flags: Set<string>;
    sort: ProductOrderingModeEnumApi;
    parameters: Map<string, Set<string>>;
};

export type SeoCategorySlice = {
    originalCategorySlug: string | undefined;
    setOriginalCategorySlug: (value: string | undefined) => void;
    defaultProductFiltersMap: DefaultProductFiltersMapType;
    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => void;
};

export const createSeoCategorySlice: StateCreator<SeoCategorySlice> = (set) => ({
    defaultProductFiltersMap: getEmptyDefaultProductFiltersMap(),
    originalCategorySlug: undefined,

    setOriginalCategorySlug: (value: string | undefined) => {
        set({ originalCategorySlug: value });
    },
    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => {
        set({ defaultProductFiltersMap: value });
    },
});
