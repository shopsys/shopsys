import { PageInfoType, SliderProductItemType } from 'types/product';
import { BlogArticleCategoryType } from './blogCategory';
import { BlogCategoryFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageType } from 'types/image';

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
};

export type ListedBlogArticleType = {
    name: string;
    slug: string;
    image: ImageType | null;
};

type BlogArticleType = {
    uuid: string;
    name: string;
    publishDate: string;
    perex?: string;
    link: string;
    image: ImageType | null;
    blogCategories: BlogArticleCategoryType[];
};

export type BlogPreviewType = {
    name: string;
    link: string;
    perex: string;
    image: ImageType | null;
    blogCategories: BlogCategoryFragmentApi[];
};

export type BlogArticleDetailType = {
    __typename: string | undefined;
    breadcrumb: BreadcrumbItemType[];
    uuid: string;
    name: string;
    text: string | null;
    publishDate: string;
    slug: string;
    link: string;
    image: ImageType | null;
    blogArticleProducts: SliderProductItemType[];
};

export type BlogArticlesType = {
    totalCount: number;
    pageInfo: PageInfoType;
    edges: BlogArticleType[];
};
