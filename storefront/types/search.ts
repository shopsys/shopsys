import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryType } from 'types/category';
import { ListedProductType, SimpleProductType } from 'types/product';
import { FilterOptionsType } from './productFilter';
import { SimpleArticleInterfaceType } from './articleInterface';

export type AutocompleteSearchType = {
    articlesSearch: SimpleArticleInterfaceType[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type SearchType = {
    articlesSearch: SimpleArticleInterfaceType[];
    brandSearch: ListedBrandType[];
    productsSearch: {
        totalCount: number;
        productFilterOptions: FilterOptionsType | null;
        products: ListedProductType[];
    };
    categoriesSearch: {
        totalCount: number;
        categories: ListedCategoryType[];
    };
};
