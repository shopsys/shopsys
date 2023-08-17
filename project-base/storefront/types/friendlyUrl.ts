import { ArticleDetailFragmentApi } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { BlogArticleDetailFragmentApi } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { BlogCategoryDetailFragmentApi } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { BrandDetailFragmentApi } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { CategoryDetailFragmentApi } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { FlagDetailFragmentApi } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { MainVariantDetailFragmentApi } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragmentApi } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { StoreDetailFragmentApi } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';

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

export const FriendlyPagesDestinations: Record<FriendlyPagesTypesKeys, string> = {
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

export type FriendlyPagesTypesKeys = keyof typeof FriendlyPagesTypes;

export type FriendlyPageTypesValue = (typeof FriendlyPagesTypes)[FriendlyPagesTypesKeys];
