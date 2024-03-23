import { ArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { BlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { BlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { BrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { CategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { FlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { StoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';

export type FriendlyUrlPageType =
    | ProductDetailFragment
    | MainVariantDetailFragment
    | CategoryDetailFragment
    | StoreDetailFragment
    | ArticleDetailFragment
    | BlogArticleDetailFragment
    | BlogCategoryDetailFragment
    | BrandDetailFragment
    | FlagDetailFragment;

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
