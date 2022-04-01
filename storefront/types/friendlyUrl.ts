import { ArticleDetailType } from './article';
import { BlogArticleDetailType } from './blogArticle';
import { BlogCategoryDetailType } from './blogCategory';
import { BrandDetailType } from './brand';
import { CategoryDetailType } from './category';
import { FlagDetailType } from './flag';
import { MainVariantDetailType, ProductDetailType } from './product';
import { StoreDetailType } from './store';

export type FriendlyUrlPageType =
    | ProductDetailType
    | MainVariantDetailType
    | CategoryDetailType
    | StoreDetailType
    | ArticleDetailType
    | BlogArticleDetailType
    | BlogCategoryDetailType
    | BrandDetailType
    | FlagDetailType;
