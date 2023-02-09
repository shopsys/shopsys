import { SimpleBlogCategoryType } from './blogCategory';
import { ImageSizesFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { PageInfoType } from 'types/pageInfo';
import { SliderProductItemType } from 'types/product';

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
    blogArticleProducts: SliderProductItemType[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type BlogArticleConnectionType = {
    totalCount: number;
    pageInfo: PageInfoType;
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
