import { ListedArticleType, SimpleArticleType } from 'types/article';
import { ListedBlogArticleType, SimpleBlogArticleType } from 'types/blogArticle';
import { ListedBrandType, SimpleBrandType } from 'connectors/brands/types';
import { ListedCategoryType, SimpleCategoryType } from 'connectors/categories/types';
import { ListedProductType, SimpleProductType } from 'connectors/products/types';

export type SearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type EnrichedSearchType = {
    articlesSearch: (ListedArticleType | ListedBlogArticleType)[];
    brandSearch: ListedBrandType[];
    productsSearch: { totalCount: number; products: ListedProductType[] };
    categoriesSearch: { totalCount: number; categories: ListedCategoryType[] };
};
