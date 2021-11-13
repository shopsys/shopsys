import { ListedProductType, SimpleProductType } from 'connectors/products/types';

export type SearchType = {
    articlesSearch: (SimpleArticleType | SimpleBlogArticleType)[];
    brandSearch: SimpleBrandType[];
    categoriesSearch: { totalCount: number; categories: SimpleCategoryType[] };
    productsSearch: { totalCount: number; products: SimpleProductType[] };
};

export type EnrichedSearchType = {
    productsSearch: { totalCount: number; products: ListedProductType[] };
};
