import { BreadcrumbItemType } from 'types/breadcrumb';

export type ArticleDetailType = {
    __typename: 'Article';
    slug: string;
    uuid: string;
    placement: string;
    articleName: string;
    text: string | null;
    breadcrumb: BreadcrumbItemType[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type SimpleArticleType = {
    __typename?: 'Article';
    name: string;
    slug: string;
};
