import {
    ListedArticleType,
    ListedBlogArticleType,
    SimpleArticleType,
    SimpleBlogArticleType,
} from 'connectors/articles/types';
import { ListedProductType, SimpleProductType } from 'connectors/products/types';

export type SearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type EnrichedSearchType = {
    articlesSearch: (ListedArticleType | ListedBlogArticleType)[];
    productsSearch: { totalCount: number; products: ListedProductType[] };
};
