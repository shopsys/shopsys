import { ProductOrderingModeEnum } from 'graphql/types';
import { getEmptyDefaultProductFiltersMap } from 'helpers/filterOptions/seoCategories';
import { StateCreator } from 'zustand';

export type DefaultProductFiltersMapType = {
    flags: Set<string>;
    brands: Set<string>;
    sort: ProductOrderingModeEnum;
    parameters: Map<string, Set<string>>;
};

export type SeoCategorySlice = {
    originalCategorySlug: string | undefined;
    setOriginalCategorySlug: (value: string | undefined) => void;
    defaultProductFiltersMap: DefaultProductFiltersMapType;
    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => void;
    wasRedirectedToSeoCategory: boolean;
    setWasRedirectedToSeoCategory: (value: boolean) => void;
    wasRedirectedFromSeoCategory: boolean;
    setWasRedirectedFromSeoCategory: (value: boolean) => void;
};

export const createSeoCategorySlice: StateCreator<SeoCategorySlice> = (set) => ({
    defaultProductFiltersMap: getEmptyDefaultProductFiltersMap(),
    originalCategorySlug: undefined,
    wasRedirectedToSeoCategory: false,
    wasRedirectedFromSeoCategory: false,

    setOriginalCategorySlug: (value: string | undefined) => {
        set({ originalCategorySlug: value });
    },
    setDefaultProductFiltersMap: (value: DefaultProductFiltersMapType) => {
        set({ defaultProductFiltersMap: value });
    },
    setWasRedirectedToSeoCategory: (value: boolean) => {
        set({ wasRedirectedToSeoCategory: value });
    },
    setWasRedirectedFromSeoCategory: (value: boolean) => {
        set({ wasRedirectedFromSeoCategory: value });
    },
});
