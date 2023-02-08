import { BlogArticleDetailType } from './blogArticle';
import { BlogCategoryDetailType } from './blogCategory';
import { BrandDetailType } from './brand';
import { CategoryDetailType } from './category';
import { FlagDetailType } from './flag';
import { MainVariantDetailType, ProductDetailType } from './product';
import { ArticleDetailFragmentApi, StoreDetailFragmentApi } from 'graphql/generated';

export type FriendlyUrlPageType =
    | ProductDetailType
    | MainVariantDetailType
    | CategoryDetailType
    | StoreDetailFragmentApi
    | ArticleDetailFragmentApi
    | BlogArticleDetailType
    | BlogCategoryDetailType
    | BrandDetailType
    | FlagDetailType;
