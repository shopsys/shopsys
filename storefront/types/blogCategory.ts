import { BlogArticlesType } from './blogArticle';
import { BreadcrumbItemType } from 'types/breadcrumb';

export type BlogCategoryItem = {
    uuid: string;
    name: string;
    link: string;
    children: BlogCategoryItem[];
};

export type BlogArticleCategoryType = {
    uuid: string;
    name: string;
    link: string;
    parent: {
        name: string;
    } | null;
};

export type BlogCategoryType = {
    __typename: 'BlogCategory';
    uuid: string;
    name: string;
    blogArticles: BlogArticlesType;
    breadcrumb: BreadcrumbItemType[];
};
