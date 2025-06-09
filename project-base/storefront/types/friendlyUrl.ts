import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeStoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';
import { TypeFriendlyUrlRouteEnum } from 'graphql/types';

export type FriendlyUrlPageType =
    | TypeProductDetailFragment
    | TypeMainVariantDetailFragment
    | TypeCategoryDetailFragment
    | TypeStoreDetailFragment
    | TypeArticleDetailFragment
    | TypeBlogArticleDetailFragment
    | TypeBlogCategoryDetailFragment
    | TypeBrandDetailFragment
    | TypeFlagDetailFragment;

export const FriendlyPagesTypes = {
    article: TypeFriendlyUrlRouteEnum.FrontArticleDetail,
    blogArticle: TypeFriendlyUrlRouteEnum.FrontBlogarticleDetail,
    blogCategory: TypeFriendlyUrlRouteEnum.FrontBlogcategoryDetail,
    brand: TypeFriendlyUrlRouteEnum.FrontBrandDetail,
    brandsOverview: 'front_brands_detail',
    category: TypeFriendlyUrlRouteEnum.FrontProductList,
    product: TypeFriendlyUrlRouteEnum.FrontProductDetail,
    stores: 'front_stores',
    store: TypeFriendlyUrlRouteEnum.FrontStoresDetail,
    flag: TypeFriendlyUrlRouteEnum.FrontFlagDetail,
    seo_category: TypeFriendlyUrlRouteEnum.FrontCategorySeo,
    complaintNew: 'front_customer_complaint_new',
    complaintList: 'front_customer_complaint_list',
    orderList: 'front_customer_order_list',
    account: 'front_customer_account',
    login: 'front_login',
} as const;

export const FriendlyPagesDestinations: Record<FriendlyPagesTypesKey, string> = {
    article: '/articles/[articleSlug]',
    blogArticle: '/blogArticles/[blogArticleSlug]',
    blogCategory: '/blogCategories/[blogCategorySlug]',
    brand: '/brands/[brandSlug]',
    brandsOverview: '/brands-overview',
    category: '/categories/[categorySlug]',
    product: '/products/[productSlug]',
    stores: '/stores',
    store: '/stores/[storeSlug]',
    flag: '/flags/[flagSlug]',
    seo_category: '/categories/[categorySlug]',
    complaintNew: '/customer/new-complaints',
    complaintList: '/customer/complaints',
    orderList: '/customer/orders',
    account: '/customer/account',
    login: '/login',
} as const;

export type FriendlyPagesTypesKey = keyof typeof FriendlyPagesTypes;

export type FriendlyPageTypesValue = (typeof FriendlyPagesTypes)[FriendlyPagesTypesKey];

export const FriendlyPagesTypesKeys = Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[];
