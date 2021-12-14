import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';
import { ImageType } from 'components/Basic/Image/types';
import { PageInfoType } from 'components/Blocks/Product/types';

type BlogArticleCategoryType = {
    uuid: string;
    name: string;
    link: string;
    parent: {
        name: string;
    } | null;
};

export type BlogArticleType = {
    uuid: string;
    name: string;
    publishDate: string;
    perex?: string;
    link: string;
    image: ImageType | null;
    blogCategories: BlogArticleCategoryType[];
};

export type BlogArticlesType = {
    totalCount: number;
    pageInfo: PageInfoType;
    edges: BlogArticleType[];
};

export type BlogCategoryType = {
    __typename: 'BlogCategory';
    uuid: string;
    name: string;
    blogArticles: BlogArticlesType;
    breadcrumb: BreadcrumbItemType[];
};
