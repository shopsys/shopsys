import {
    ListedProductConnectionFragmentApi,
    ListedProductConnectionPreviewFragmentApi,
    SimpleArticleInterfaceFragmentApi,
} from 'graphql/generated';
import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryConnectionType } from 'types/category';

export type AutocompleteSearchType = {
    articlesSearch: SimpleArticleInterfaceFragmentApi[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: SimpleCategoryConnectionType;
    productsSearch: ListedProductConnectionFragmentApi;
};

export type SearchType = {
    articlesSearch: SimpleArticleInterfaceFragmentApi[];
    brandSearch: ListedBrandType[];
    productsSearch: ListedProductConnectionPreviewFragmentApi;
    categoriesSearch: {
        totalCount: number;
        categories: ListedCategoryType[];
    };
};
