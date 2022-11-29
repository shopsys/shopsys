import { BreadcrumbItemType } from 'types/breadcrumb';

export type ArticleDetailType = {
    __typename: 'ArticleSite';
    slug: string;
    uuid: string;
    placement: string;
    articleName: string;
    text: string | null;
    breadcrumb: BreadcrumbItemType[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
    createdAt: string;
};

export type SimpleArticleSiteType = {
    __typename: 'ArticleSite';
    name: string;
    slug: string;
    external: boolean;
};

export type SimpleArticleLinkType = {
    __typename: 'ArticleLink';
    name: string;
    url: string;
    external: boolean;
};
