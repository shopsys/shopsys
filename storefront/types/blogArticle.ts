import { SimpleBlogCategoryType } from './blogCategory';
import { ImageSizesFragmentApi, ListedProductFragmentApi, PageInfoFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';

export type BlogArticleDetailType = {
    __typename?: 'BlogArticle';
    uuid: string;
    name: string;
    slug: string;
    link: string;
    images: ImageSizesFragmentApi[];
    breadcrumb: BreadcrumbItemType[];
    text: string | null;
    publishDate: string;
    blogArticleProducts: ListedProductFragmentApi[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type BlogArticleConnectionType = {
    totalCount: number;
    pageInfo: PageInfoFragmentApi;
    edges: ListedBlogArticleType[];
};

export type ListedBlogArticleType = {
    uuid: string;
    name: string;
    link: string;
    slug: string;
    images: ImageSizesFragmentApi[];
    publishDate: string;
    perex: string | null;
    blogCategories: SimpleBlogCategoryType[];
};

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
    images: ImageSizesFragmentApi[];
};
