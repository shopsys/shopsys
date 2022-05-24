import { BlogArticleConnectionType } from './blogArticle';
import { BreadcrumbItemType } from 'types/breadcrumb';

export type BlogCategoryDetailType = {
    __typename: 'BlogCategory';
    uuid: string;
    name: string;
    blogArticles: BlogArticleConnectionType | null;
    breadcrumb: BreadcrumbItemType[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type ListedBlogCategoryType = SimpleBlogCategoryType & {
    children: ListedBlogCategoryType[];
};

export type SimpleBlogCategoryType = {
    uuid: string;
    name: string;
    link: string;
    parent: {
        name: string;
    } | null;
};
