import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryType } from 'types/category';
import { ListedProductType, SimpleProductType } from 'types/product';
import { FilterOptionsType } from './productFilter';
import { SimpleArticleType } from 'types/article';
import { SimpleBlogArticleType } from 'types/blogArticle';

export type AutocompleteSearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type SearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
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
