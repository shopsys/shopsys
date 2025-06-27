import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeStoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';

// moved because of huge import size of graphql types (middleware)

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
