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

export const FriendlyPagesTypes = {
    article: 'front_article_detail',
    blogArticle: 'front_blogarticle_detail',
    blogCategory: 'front_blogcategory_detail',
    brand: 'front_brand_detail',
    category: 'front_product_list',
    product: 'front_product_detail',
    store: 'front_stores_detail',
    flag: 'front_flag_detail',
    seo_category: 'front_category_seo',
} as const;

export const FriendlyPagesDestinations: Record<FriendlyPagesTypesKey, string> = {
    article: '/articles/[articleSlug]',
    blogArticle: '/blogArticles/[blogArticleSlug]',
    blogCategory: '/blogCategories/[blogCategorySlug]',
    brand: '/brands/[brandSlug]',
    category: '/categories/[categorySlug]',
    product: '/products/[productSlug]',
    store: '/stores/[storeSlug]',
    flag: '/flags/[flagSlug]',
    seo_category: '/categories/[categorySlug]',
} as const;

export type FriendlyPagesTypesKey = keyof typeof FriendlyPagesTypes;

export type FriendlyPageTypesValue = (typeof FriendlyPagesTypes)[FriendlyPagesTypesKey];

export const FriendlyPagesTypesKeys = Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[];
