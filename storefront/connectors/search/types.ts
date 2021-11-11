import { SimpleArticleType, SimpleBlogArticleType } from 'connectors/articles/types';
import { SimpleBrandType } from 'connectors/brands/types';
import { SimpleCategoryType } from 'connectors/categories/types';
import { SimpleProductType } from 'connectors/products/types';

export type SearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};
