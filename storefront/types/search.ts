import { SimpleArticleInterfaceType } from './articleInterface';
import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryConnectionType } from 'types/category';
import { ListedProductConnectionType, SimpleProductConnectionType } from 'types/product';

export type AutocompleteSearchType = {
    articlesSearch: SimpleArticleInterfaceType[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: SimpleCategoryConnectionType;
    productsSearch: SimpleProductConnectionType;
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
