import {
    ArticleDetailFragmentApi,
    BlogArticleDetailFragmentApi,
    BlogCategoryDetailFragmentApi,
    BrandDetailFragmentApi,
    CategoryDetailFragmentApi,
    FlagDetailFragmentApi,
    MainVariantDetailFragmentApi,
    ProductDetailFragmentApi,
    StoreDetailFragmentApi,
} from 'graphql/generated';

export type FriendlyUrlPageType =
    | ProductDetailFragmentApi
    | MainVariantDetailFragmentApi
    | CategoryDetailFragmentApi
    | StoreDetailFragmentApi
    | ArticleDetailFragmentApi
    | BlogArticleDetailFragmentApi
    | BlogCategoryDetailFragmentApi
    | BrandDetailFragmentApi
    | FlagDetailFragmentApi;
