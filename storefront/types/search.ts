import { ListedArticleType, SimpleArticleType } from 'types/article';
import { ListedBlogArticleType, SimpleBlogArticleType } from 'types/blogArticle';
import { ListedBrandType, SimpleBrandType } from 'types/brand';
import { ListedCategoryType, SimpleCategoryType } from 'types/category';
import { ListedProductType, SimpleProductType } from 'types/product';
import { FilterOptionsType } from './productFilter';

export type AutocompleteSearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type SearchType = {
    articlesSearch: (ListedArticleType | ListedBlogArticleType)[];
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
