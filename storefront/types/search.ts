import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryType } from 'types/category';
import { ListedProductConnectionType, SimpleProductType } from 'types/product';
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
    productsSearch: ListedProductConnectionType;
    categoriesSearch: {
        totalCount: number;
        categories: ListedCategoryType[];
    };
};
