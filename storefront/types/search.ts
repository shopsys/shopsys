import { SimpleArticleInterfaceType } from './articleInterface';
import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryConnectionType } from 'types/category';
import { ListedProductConnectionPreviewType, ListedProductConnectionType } from 'types/product';

export type AutocompleteSearchType = {
    articlesSearch: SimpleArticleInterfaceType[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: SimpleCategoryConnectionType;
    productsSearch: ListedProductConnectionType;
};

export type SearchType = {
    articlesSearch: SimpleArticleInterfaceType[];
    brandSearch: ListedBrandType[];
    productsSearch: ListedProductConnectionPreviewType;
    categoriesSearch: {
        totalCount: number;
        categories: ListedCategoryType[];
    };
};
