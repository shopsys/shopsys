import { BlogArticleDetailType } from './blogArticle';
import { BlogCategoryDetailType } from './blogCategory';
import { BrandDetailType } from './brand';
import { CategoryDetailType } from './category';
import { FlagDetailType } from './flag';
import { MainVariantDetailType, ProductDetailType } from './product';
import { StoreDetailType } from './store';
import { ArticleDetailFragmentApi } from 'graphql/generated';

export type FriendlyUrlPageType =
    | ProductDetailType
    | MainVariantDetailType
    | CategoryDetailType
    | StoreDetailType
    | ArticleDetailFragmentApi
    | BlogArticleDetailType
    | BlogCategoryDetailType
    | BrandDetailType
    | FlagDetailType;
